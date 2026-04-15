<?php

namespace App\Policies;

use App\Models\ClientPortalShare;
use App\Models\User;

class ClientPortalSharePolicy
{
    public function view(User $user, ClientPortalShare $share): bool
    {
        return $share->workspace !== null && $user->belongsToWorkspace($share->workspace);
    }

    public function create(User $user): bool
    {
        return $user->resolveCurrentWorkspace() !== null;
    }

    public function update(User $user, ClientPortalShare $share): bool
    {
        return $this->view($user, $share);
    }
}
