<?php

namespace Tests\Feature\Clients;

use App\Enums\ClientActivityType;
use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\ClientNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ClientSearchAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_can_be_searched_by_name_company_email_and_phone(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Client::factory()->for($user)->create([
            'name' => 'Needle Client',
            'company' => 'Haystack Agency',
            'email' => 'owner@haystack.test',
            'phone' => '111-1111',
        ]);

        Client::factory()->for($user)->create([
            'name' => 'Jordan Miles',
            'company' => 'Needle Company',
            'email' => 'owner@other.test',
            'phone' => '222-2222',
        ]);

        Client::factory()->for($user)->create([
            'name' => 'Another Person',
            'company' => 'Another Company',
            'email' => 'needle@example.test',
            'phone' => '333-3333',
        ]);

        Client::factory()->for($user)->create([
            'name' => 'Fourth Client',
            'company' => 'Fourth Company',
            'email' => 'owner@fourth.test',
            'phone' => '555-needle',
        ]);

        $response = $this->get(route('clients.index', ['search' => 'needle']));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('clients/Index')
            ->has('clients.data', 4));
    }

    public function test_clients_can_be_filtered_by_status_and_archived_state(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Client::factory()->for($user)->create([
            'name' => 'Lead Client',
            'status' => ClientStatus::Lead,
        ]);

        Client::factory()->for($user)->create([
            'name' => 'Active Client',
            'status' => ClientStatus::Active,
        ]);

        Client::factory()->for($user)->archived()->create([
            'name' => 'Archived Client',
            'status' => ClientStatus::Completed,
        ]);

        $statusResponse = $this->get(route('clients.index', [
            'status' => ClientStatus::Lead->value,
        ]));

        $statusResponse->assertInertia(fn (AssertableInertia $page) => $page
            ->component('clients/Index')
            ->has('clients.data', 1)
            ->where('clients.data.0.name', 'Lead Client'));

        $archivedResponse = $this->get(route('clients.index', [
            'archived' => 'only',
        ]));

        $archivedResponse->assertInertia(fn (AssertableInertia $page) => $page
            ->component('clients/Index')
            ->has('clients.data', 1)
            ->where('clients.data.0.name', 'Archived Client'));
    }

    public function test_clients_export_respects_filters_and_ownership(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Client::factory()->for($user)->create([
            'name' => 'Exportable Lead',
            'company' => 'Northwind Studio',
            'email' => 'lead@northwind.test',
            'phone' => '555-1000',
            'status' => ClientStatus::Lead,
        ]);

        Client::factory()->for($user)->create([
            'name' => 'Different Status',
            'company' => 'Northwind Active',
            'email' => 'active@northwind.test',
            'phone' => '555-1001',
            'status' => ClientStatus::Active,
        ]);

        Client::factory()->for($otherUser)->create([
            'name' => 'Other User Lead',
            'company' => 'Northwind Studio',
            'email' => 'other@northwind.test',
            'phone' => '555-1002',
            'status' => ClientStatus::Lead,
        ]);

        $response = $this->actingAs($user)->get(route('clients.export', [
            'search' => 'northwind',
            'status' => ClientStatus::Lead->value,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertHeader('content-disposition');

        $csv = $response->streamedContent();
        $rows = collect(preg_split("/\r\n|\n|\r/", trim($csv)))
            ->filter()
            ->map(fn (string $line): array => str_getcsv($line))
            ->values();

        $this->assertSame([
            'Name',
            'Company',
            'Email',
            'Phone',
            'Status',
            'Budget',
            'Source',
            'Last Contacted',
            'Follow Up',
            'Archived At',
            'Created At',
            'Updated At',
        ], $rows->first());

        $this->assertCount(2, $rows);
        $this->assertSame('Exportable Lead', $rows[1][0]);
        $this->assertSame('Northwind Studio', $rows[1][1]);
        $this->assertSame('lead@northwind.test', $rows[1][2]);
        $this->assertSame('555-1000', $rows[1][3]);
        $this->assertSame('Lead', $rows[1][4]);
    }

    public function test_clients_can_be_imported_from_csv_and_update_matching_emails(): void
    {
        $user = User::factory()->create();
        $existingClient = Client::factory()->for($user)->create([
            'name' => 'Existing Client',
            'email' => 'existing@example.com',
            'status' => ClientStatus::Active,
        ]);

        $csv = <<<'CSV'
name,email,status,company,phone,follow_up_at
Imported Client,imported@example.com,,Northwind Studio,555-3000,2026-04-02
Updated Client,existing@example.com,completed,Northwind Retainer,555-3001,2026-04-08
CSV;

        $response = $this->actingAs($user)->post(route('clients.import'), [
            'file' => UploadedFile::fake()->createWithContent('clients.csv', $csv),
        ]);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clients', [
            'user_id' => $user->id,
            'name' => 'Imported Client',
            'email' => 'imported@example.com',
            'status' => ClientStatus::Lead->value,
            'company' => 'Northwind Studio',
        ]);

        $this->assertDatabaseHas('clients', [
            'id' => $existingClient->id,
            'name' => 'Updated Client',
            'email' => 'existing@example.com',
            'status' => ClientStatus::Completed->value,
            'company' => 'Northwind Retainer',
        ]);

        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'type' => ClientActivityType::Imported->value,
        ]);
    }

    public function test_client_import_reports_validation_errors_for_invalid_rows(): void
    {
        $user = User::factory()->create();

        $csv = <<<'CSV'
company,email
Broken Row,broken@example.com
CSV;

        $response = $this->actingAs($user)
            ->from(route('clients.index'))
            ->post(route('clients.import'), [
                'file' => UploadedFile::fake()->createWithContent('clients.csv', $csv),
            ]);

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHasErrors('file');

        $this->assertDatabaseCount('clients', 0);
    }

    public function test_dashboard_stats_only_include_owned_non_archived_clients(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Client::factory()->for($user)->create([
            'status' => ClientStatus::Lead,
            'follow_up_at' => now()->addDays(3),
        ]);

        Client::factory()->for($user)->create([
            'status' => ClientStatus::Active,
            'follow_up_at' => now()->addDays(2),
        ]);

        Client::factory()->for($user)->create([
            'status' => ClientStatus::Active,
            'follow_up_at' => now()->addDays(10),
        ]);

        $overdueClient = Client::factory()->for($user)->create([
            'name' => 'Overdue Client',
            'status' => ClientStatus::Waiting,
            'follow_up_at' => now()->subDay(),
        ]);

        $recentClient = Client::factory()->for($user)->create([
            'name' => 'Recent Client',
            'status' => ClientStatus::Completed,
            'follow_up_at' => null,
        ]);

        Client::factory()->for($user)->archived()->create([
            'status' => ClientStatus::Lead,
            'follow_up_at' => now()->addDay(),
        ]);

        Client::factory()->for($otherUser)->count(2)->create([
            'status' => ClientStatus::Active,
            'follow_up_at' => now()->addDay(),
        ]);

        ClientNote::factory()->for($recentClient)->for($user, 'user')->create([
            'content' => 'Wrapped up final handoff.',
        ]);

        ClientNote::factory()->for(
            Client::factory()->for($otherUser)->create(),
        )->for($otherUser, 'user')->create([
            'content' => 'Should not appear.',
        ]);

        ClientActivity::query()->create([
            'user_id' => $user->id,
            'client_id' => $overdueClient->id,
            'type' => ClientActivityType::Contacted->value,
            'description' => 'Marked this client as contacted today.',
            'properties' => [
                'client_name' => $overdueClient->name,
            ],
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Dashboard')
            ->where('stats.totalClients', 5)
            ->where('stats.activeClients', 2)
            ->where('stats.leads', 1)
            ->where('stats.overdueFollowUps', 1)
            ->where('stats.followUpsDueSoon', 2)
            ->where('followUpReminders.overdue.0.name', 'Overdue Client')
            ->where('recentlyUpdatedClients.0.name', 'Recent Client')
            ->where('recentNotes.0.content', 'Wrapped up final handoff.')
            ->where('recentActivity.0.description', 'Marked this client as contacted today.'));
    }
}
