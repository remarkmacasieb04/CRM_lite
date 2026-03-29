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
                'name' => 'Osama Bin Laden',
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
                'avatar' => 'https://images.unsplash.com/photo-1493106819501-66d381c466f1?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
            ],
        );

        Client::factory()
            ->count(15)
            ->for($owner)
            ->withStatus(ClientStatus::Lead)
            ->create();

        Client::factory()
            ->count(10)
            ->for($owner)
            ->withStatus(ClientStatus::Active)
            ->create();

        Client::factory()
            ->count(9)
            ->for($owner)
            ->withStatus(ClientStatus::Completed)
            ->create();

        Client::factory()
            ->count(11)
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
