<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientNotePolicy
{
    public function create(User $user, Client $client): bool
    {
        return $client->workspace !== null && $user->belongsToWorkspace($client->workspace);
    }
}
