<?php

namespace Tests\Feature\Clients;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\ClientAttachment;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            'tags' => 'Referral, Design',
        ]);

        $client = Client::query()->firstOrFail();
        $referralTag = Tag::query()->where('slug', 'referral')->firstOrFail();
        $designTag = Tag::query()->where('slug', 'design')->firstOrFail();

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
        $this->assertDatabaseHas('client_tag', [
            'client_id' => $client->id,
            'tag_id' => $referralTag->id,
        ]);
        $this->assertDatabaseHas('client_tag', [
            'client_id' => $client->id,
            'tag_id' => $designTag->id,
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
            'tags' => 'Retainer',
        ]);

        $retainerTag = Tag::query()->where('slug', 'retainer')->firstOrFail();

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
        $this->assertDatabaseHas('client_tag', [
            'client_id' => $client->id,
            'tag_id' => $retainerTag->id,
        ]);
        $this->assertDatabaseMissing('client_tag', [
            'client_id' => $client->id,
            'tag_id' => $referralTag->id,
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

    public function test_user_can_upload_download_and_remove_client_attachments(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $client = Client::factory()->for($user)->create();

        $uploadResponse = $this->actingAs($user)
            ->from(route('clients.show', $client))
            ->post(route('clients.attachments.store', $client), [
                'file' => UploadedFile::fake()->create('proposal.pdf', 120, 'application/pdf'),
            ]);

        $attachment = ClientAttachment::query()->firstOrFail();

        $uploadResponse->assertRedirect(route('clients.show', $client));
        Storage::disk('local')->assertExists($attachment->path);
        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => 'attachment_added',
        ]);

        $this->actingAs($user)
            ->get(route('clients.attachments.download', $attachment))
            ->assertOk()
            ->assertDownload('proposal.pdf');

        $path = $attachment->path;

        $this->actingAs($user)
            ->delete(route('clients.attachments.destroy', $attachment))
            ->assertRedirect()
            ->assertSessionHas('success');

        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('client_attachments', [
            'id' => $attachment->id,
        ]);
        $this->assertDatabaseHas('client_activities', [
            'user_id' => $user->id,
            'client_id' => $client->id,
            'type' => 'attachment_deleted',
        ]);
    }

    public function test_deleting_an_archived_client_purges_stored_attachments(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $client = Client::factory()->for($user)->archived()->create();

        $this->actingAs($user)->post(route('clients.attachments.store', $client), [
            'file' => UploadedFile::fake()->create('brief.txt', 10, 'text/plain'),
        ]);

        $attachment = ClientAttachment::query()->firstOrFail();
        $path = $attachment->path;

        $this->actingAs($user)
            ->delete(route('clients.destroy', $client))
            ->assertRedirect(route('clients.index', ['archived' => 'only']));

        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('client_attachments', [
            'id' => $attachment->id,
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

    public function test_users_cannot_download_or_delete_other_users_attachments(): void
    {
        Storage::fake('local');

        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $client = Client::factory()->for($owner)->create();

        $this->actingAs($owner)->post(route('clients.attachments.store', $client), [
            'file' => UploadedFile::fake()->create('scope.pdf', 40, 'application/pdf'),
        ]);

        $attachment = ClientAttachment::query()->firstOrFail();

        $this->actingAs($intruder)
            ->get(route('clients.attachments.download', $attachment))
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('clients.attachments.destroy', $attachment))
            ->assertForbidden();
    }
}
