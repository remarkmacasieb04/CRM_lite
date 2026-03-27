<?php

namespace Database\Factories;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->name(),
            'company' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(ClientStatus::cases()),
            'budget' => fake()->randomFloat(2, 500, 12000),
            'source' => fake()->randomElement(['Referral', 'Website', 'LinkedIn', 'Repeat client']),
            'last_contacted_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'follow_up_at' => fake()->optional()->dateTimeBetween('now', '+14 days'),
            'archived_at' => null,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'archived_at' => now(),
        ]);
    }

    public function withStatus(ClientStatus $status): static
    {
        return $this->state(fn (): array => [
            'status' => $status,
        ]);
    }
}
