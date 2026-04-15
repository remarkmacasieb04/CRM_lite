<?php

namespace App\Policies;

use App\Models\ClientCommunication;
use App\Models\User;

class ClientCommunicationPolicy
{
    public function view(User $user, ClientCommunication $communication): bool
    {
        return $communication->workspace !== null && $user->belongsToWorkspace($communication->workspace);
    }

    public function create(User $user): bool
    {
        return $user->resolveCurrentWorkspace() !== null;
    }
}
