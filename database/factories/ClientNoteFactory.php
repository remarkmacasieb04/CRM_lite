<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientNote>
 */
class ClientNoteFactory extends Factory
{
    protected $model = ClientNote::class;

    public function definition(): array
    {
        $client = Client::factory();

        return [
            'client_id' => $client,
            'user_id' => fn (array $attributes): ?int => Client::query()->find($attributes['client_id'])?->user_id,
            'workspace_id' => fn (array $attributes): ?int => Client::query()->find($attributes['client_id'])?->workspace_id,
            'content' => fake()->paragraph(),
        ];
    }
}
