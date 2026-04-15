<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    public function view(User $user, Workspace $workspace): bool
    {
        return $user->belongsToWorkspace($workspace);
    }

    public function manageMembers(User $user, Workspace $workspace): bool
    {
        return $user->workspaceRole($workspace)?->canManageWorkspace() ?? false;
    }

    public function switch(User $user, Workspace $workspace): bool
    {
        return $user->belongsToWorkspace($workspace);
    }
}
