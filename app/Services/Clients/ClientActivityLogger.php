<?php

namespace App\Services\Clients;

use App\Enums\ClientActivityType;
use App\Models\Client;
use App\Models\ClientActivity;
use App\Models\User;

class ClientActivityLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(
        User $user,
        ?Client $client,
        ClientActivityType $type,
        string $description,
        array $properties = [],
    ): ClientActivity {
        return ClientActivity::query()->create([
            'user_id' => $user->id,
            'workspace_id' => $client?->workspace_id ?? $user->current_workspace_id,
            'client_id' => $client?->id,
            'type' => $type->value,
            'description' => $description,
            'properties' => [
                'client_name' => $client?->name,
                ...$properties,
            ],
        ]);
    }
}
