<?php

namespace App\Policies;

use App\Models\ClientDocument;
use App\Models\User;

class ClientDocumentPolicy
{
    public function view(User $user, ClientDocument $document): bool
    {
        return $document->workspace !== null && $user->belongsToWorkspace($document->workspace);
    }

    public function create(User $user): bool
    {
        return $user->resolveCurrentWorkspace() !== null;
    }

    public function update(User $user, ClientDocument $document): bool
    {
        return $this->view($user, $document);
    }

    public function delete(User $user, ClientDocument $document): bool
    {
        return $this->view($user, $document);
    }
}
