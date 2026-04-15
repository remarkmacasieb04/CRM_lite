<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Client $client): bool
    {
        return $client->workspace !== null && $user->belongsToWorkspace($client->workspace);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Client $client): bool
    {
        return $client->workspace !== null && $user->belongsToWorkspace($client->workspace);
    }

    public function archive(User $user, Client $client): bool
    {
        return $this->update($user, $client) && ! $client->isArchived();
    }

    public function restore(User $user, Client $client): bool
    {
        return $this->update($user, $client) && $client->isArchived();
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->update($user, $client) && $client->isArchived();
    }
}
