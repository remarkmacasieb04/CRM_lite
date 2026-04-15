<?php

namespace Database\Seeders;

use App\Enums\ClientStatus;
use App\Enums\CommunicationChannel;
use App\Enums\CommunicationDirection;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\SocialProvider;
use App\Enums\UserRole;
use App\Enums\WorkspaceMemberRole;
use App\Models\Client;
use App\Models\ClientCommunication;
use App\Models\ClientDocument;
use App\Models\ClientNote;
use App\Models\SocialAccount;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleCrmSeeder extends Seeder
{
    public function run(): void
    {
        $owner = $this->createDemoUser(
            name: 'Abdul Jack Ol',
            email: 'owner@example.com',
            role: UserRole::Admin,
        );

        $admin = $this->createDemoUser(
            name: 'Casey Harper',
            email: 'admin@example.com',
            role: UserRole::Admin,
        );

        $freelancer = $this->createDemoUser(
            name: 'Jordan Lee',
            email: 'freelancer@example.com',
            role: UserRole::User,
        );

        $staffUser = $this->createDemoUser(
            name: 'Avery Chen',
            email: 'user@example.com',
            role: UserRole::User,
        );

        $this->seedOwnerSocialAccount($owner);

        $this->seedClientPortfolio($owner, leadCount: 15, activeCount: 10, completedCount: 9, archivedCount: 11);
        $this->seedClientPortfolio($admin, leadCount: 4, activeCount: 4, completedCount: 2, archivedCount: 1);
        $this->seedClientPortfolio($freelancer, leadCount: 8, activeCount: 6, completedCount: 3, archivedCount: 2);
        $this->seedClientPortfolio($staffUser, leadCount: 5, activeCount: 3, completedCount: 2, archivedCount: 1);

        $this->seedSharedWorkspace($owner, $admin, $freelancer, $staffUser);
    }

    private function createDemoUser(string $name, string $email, UserRole $role): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => $role,
            ],
        );
    }

    private function seedOwnerSocialAccount(User $owner): void
    {
        SocialAccount::query()->firstOrCreate(
            [
                'user_id' => $owner->id,
                'provider' => SocialProvider::Google->value,
            ],
            [
                'provider_user_id' => 'google-owner-001',
                'provider_email' => $owner->email,
                'provider_email_verified_at' => now(),
                'avatar' => 'https://images.unsplash.com/photo-1493106819501-66d381c466f1?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            ],
        );
    }

    private function seedClientPortfolio(
        User $user,
        int $leadCount,
        int $activeCount,
        int $completedCount,
        int $archivedCount,
    ): void {
        if ($user->clients()->exists()) {
            return;
        }

        Client::factory()
            ->count($leadCount)
            ->for($user)
            ->withStatus(ClientStatus::Lead)
            ->create();

        Client::factory()
            ->count($activeCount)
            ->for($user)
            ->withStatus(ClientStatus::Active)
            ->create();

        Client::factory()
            ->count($completedCount)
            ->for($user)
            ->withStatus(ClientStatus::Completed)
            ->create();

        Client::factory()
            ->count($archivedCount)
            ->for($user)
            ->archived()
            ->create();

        foreach ($user->clients()->get() as $client) {
            ClientNote::factory()
                ->count(fake()->numberBetween(1, 3))
                ->for($client)
                ->for($user, 'user')
                ->create();
        }
    }

    private function seedSharedWorkspace(User $owner, User $admin, User $freelancer, User $staffUser): void
    {
        $workspace = Workspace::query()->firstOrCreate(
            ['slug' => 'acme-collaborative'],
            [
                'owner_user_id' => $owner->id,
                'name' => 'Acme Collaborative',
                'is_personal' => false,
            ],
        );

        $workspace->members()->syncWithoutDetaching([
            $owner->id => ['role' => WorkspaceMemberRole::Owner->value],
            $admin->id => ['role' => WorkspaceMemberRole::Admin->value],
            $freelancer->id => ['role' => WorkspaceMemberRole::Member->value],
            $staffUser->id => ['role' => WorkspaceMemberRole::Member->value],
        ]);

        if (! Client::query()->whereBelongsTo($workspace)->exists()) {
            $clients = Client::factory()
                ->count(5)
                ->for($owner)
                ->state([
                    'workspace_id' => $workspace->id,
                    'status' => ClientStatus::Active,
                ])
                ->create();

            foreach ($clients as $client) {
                ClientNote::factory()
                    ->count(2)
                    ->for($client)
                    ->for($admin, 'user')
                    ->state([
                        'workspace_id' => $workspace->id,
                    ])
                    ->create();

                ClientCommunication::query()->create([
                    'workspace_id' => $workspace->id,
                    'client_id' => $client->id,
                    'user_id' => $freelancer->id,
                    'channel' => CommunicationChannel::Email->value,
                    'direction' => CommunicationDirection::Outbound->value,
                    'subject' => 'Weekly update',
                    'summary' => 'Shared current progress, next steps, and requested feedback on open items.',
                    'happened_at' => now()->subDays(fake()->numberBetween(1, 5)),
                ]);

                ClientDocument::query()->create([
                    'workspace_id' => $workspace->id,
                    'client_id' => $client->id,
                    'user_id' => $admin->id,
                    'type' => DocumentType::Proposal->value,
                    'title' => "{$client->name} scope proposal",
                    'document_number' => 'PRO-SHARED-'.$client->id,
                    'status' => fake()->randomElement([
                        DocumentStatus::Draft->value,
                        DocumentStatus::Sent->value,
                        DocumentStatus::Accepted->value,
                    ]),
                    'amount' => fake()->randomFloat(2, 900, 4500),
                    'currency' => 'USD',
                    'issued_at' => now()->subDays(fake()->numberBetween(2, 8)),
                    'due_at' => now()->addDays(fake()->numberBetween(3, 12)),
                    'notes' => 'Shared with the client through the portal once the team is ready.',
                    'is_portal_visible' => true,
                ]);

                Task::query()->create([
                    'workspace_id' => $workspace->id,
                    'client_id' => $client->id,
                    'created_by_user_id' => $owner->id,
                    'assigned_to_user_id' => $staffUser->id,
                    'title' => 'Prepare next review',
                    'description' => 'Pull together the next review package and confirm open questions.',
                    'status' => fake()->randomElement(['todo', 'in_progress', 'waiting']),
                    'priority' => fake()->randomElement(['medium', 'high']),
                    'due_at' => now()->addDays(fake()->numberBetween(1, 6)),
                ]);
            }
        }
    }
}
