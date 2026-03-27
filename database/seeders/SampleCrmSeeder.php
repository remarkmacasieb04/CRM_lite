<?php

namespace Database\Seeders;

use App\Enums\ClientStatus;
use App\Enums\SocialProvider;
use App\Models\Client;
use App\Models\ClientNote;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleCrmSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Morgan Ellis',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        SocialAccount::query()->firstOrCreate(
            [
                'user_id' => $owner->id,
                'provider' => SocialProvider::Google->value,
            ],
            [
                'provider_user_id' => 'google-owner-001',
                'provider_email' => $owner->email,
                'provider_email_verified_at' => now(),
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=160&q=80',
            ],
        );

        Client::factory()
            ->count(4)
            ->for($owner)
            ->withStatus(ClientStatus::Lead)
            ->create();

        Client::factory()
            ->count(5)
            ->for($owner)
            ->withStatus(ClientStatus::Active)
            ->create();

        Client::factory()
            ->count(3)
            ->for($owner)
            ->withStatus(ClientStatus::Completed)
            ->create();

        Client::factory()
            ->count(2)
            ->for($owner)
            ->archived()
            ->create();

        $clients = $owner->clients()->get();

        foreach ($clients as $client) {
            ClientNote::factory()
                ->count(fake()->numberBetween(1, 3))
                ->for($client)
                ->for($owner, 'user')
                ->create();
        }
    }
}
