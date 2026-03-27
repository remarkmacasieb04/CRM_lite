<?php

namespace Tests\Feature\Clients;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_manage_the_full_client_lifecycle(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $createResponse = $this->post(route('clients.store'), [
            'name' => 'Northshore Studio',
            'company' => 'Northshore Studio',
            'email' => 'hello@northshore.test',
            'phone' => '555-0100',
            'status' => ClientStatus::Lead->value,
            'budget' => '2400.00',
            'source' => 'Referral',
            'last_contacted_at' => '2026-03-20',
            'follow_up_at' => '2026-03-29',
        ]);

        $client = Client::query()->firstOrFail();

        $createResponse->assertRedirect(route('clients.show', $client));
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'user_id' => $user->id,
            'name' => 'Northshore Studio',
            'status' => ClientStatus::Lead->value,
        ]);
        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => 'created',
            'description' => 'Created this client record.',
        ]);

        $this->get(route('clients.show', $client))
            ->assertOk()
            ->assertSee('Northshore Studio');

        $updateResponse = $this->patch(route('clients.update', $client), [
            'name' => 'Northshore Studio',
            'company' => 'Northshore Design Co.',
            'email' => 'hello@northshore.test',
            'phone' => '555-0100',
            'status' => ClientStatus::Active->value,
            'budget' => '3200.00',
            'source' => 'Website',
            'last_contacted_at' => '2026-03-21',
            'follow_up_at' => '2026-03-30',
        ]);

        $updateResponse->assertRedirect(route('clients.show', $client));
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'company' => 'Northshore Design Co.',
            'status' => ClientStatus::Active->value,
        ]);
        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => 'updated',
            'description' => 'Updated client details.',
        ]);

        $this->patch(route('clients.archive', $client))
            ->assertRedirect(route('clients.show', $client));

        $this->assertNotNull($client->refresh()->archived_at);
        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => 'archived',
        ]);

        $this->patch(route('clients.restore', $client))
            ->assertRedirect(route('clients.show', $client));

        $this->assertNull($client->refresh()->archived_at);
        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => 'restored',
        ]);

        $this->patch(route('clients.archive', $client));

        $this->delete(route('clients.destroy', $client))
            ->assertRedirect(route('clients.index', ['archived' => 'only']));

        $this->assertDatabaseMissing('clients', [
            'id' => $client->id,
        ]);
        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'type' => 'deleted',
            'description' => 'Permanently deleted this client.',
        ]);
    }

    public function test_user_can_add_a_note_to_their_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('clients.notes.store', $client), [
                'content' => 'Called to confirm scope and timeline.',
            ])
            ->assertRedirect(route('clients.show', $client));

        $this->assertDatabaseHas('client_notes', [
            'client_id' => $client->id,
            'user_id' => $user->id,
            'content' => 'Called to confirm scope and timeline.',
        ]);
        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => 'note_added',
            'description' => 'Added a client note.',
        ]);
    }

    public function test_note_creation_requires_content(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();

        $this->actingAs($user)
            ->from(route('clients.show', $client))
            ->post(route('clients.notes.store', $client), [
                'content' => '',
            ])
            ->assertSessionHasErrors('content');
    }

    public function test_user_can_mark_a_client_as_contacted_from_the_quick_action(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create([
            'last_contacted_at' => null,
        ]);

        $this->actingAs($user)
            ->patch(route('clients.contacted', $client))
            ->assertRedirect();

        $this->assertNotNull($client->fresh()->last_contacted_at);
        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => 'contacted',
            'description' => 'Marked this client as contacted today.',
        ]);
    }

    public function test_users_cannot_access_or_modify_other_users_clients(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $client = Client::factory()->for($owner)->create();
        $archivedClient = Client::factory()->for($owner)->archived()->create();

        $this->actingAs($intruder)
            ->get(route('clients.show', $client))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('clients.update', $client), [
                'name' => 'Tampered',
                'status' => ClientStatus::Lead->value,
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->post(route('clients.notes.store', $client), [
                'content' => 'Unauthorized note.',
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('clients.archive', $client))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->patch(route('clients.restore', $archivedClient))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('clients.destroy', $archivedClient))
            ->assertForbidden();
    }
}
