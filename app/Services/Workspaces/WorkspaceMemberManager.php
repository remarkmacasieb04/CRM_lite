<?php

namespace App\Services\Workspaces;

use App\Enums\WorkspaceMemberRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WorkspaceMemberManager
{
    public function createWorkspace(User $user, string $name): Workspace
    {
        return DB::transaction(function () use ($user, $name): Workspace {
            $baseSlug = Str::slug($name);
            $slug = $baseSlug;
            $suffix = 1;

            while (Workspace::query()->where('slug', $slug)->exists()) {
                $suffix++;
                $slug = "{$baseSlug}-{$suffix}";
            }

            $workspace = Workspace::query()->create([
                'owner_user_id' => $user->id,
                'name' => $name,
                'slug' => $slug,
                'is_personal' => false,
            ]);

            $workspace->members()->attach($user->id, [
                'role' => WorkspaceMemberRole::Owner->value,
            ]);

            $user->forceFill([
                'current_workspace_id' => $workspace->id,
            ])->save();

            return $workspace;
        });
    }

    public function addOrUpdateMember(Workspace $workspace, string $email, WorkspaceMemberRole $role): User
    {
        $user = User::query()->where('email', $email)->firstOrFail();
        $wasMember = $user->belongsToWorkspace($workspace);

        if ($workspace->owner_user_id === $user->id && $role !== WorkspaceMemberRole::Owner) {
            throw ValidationException::withMessages([
                'email' => ['The workspace owner always keeps the owner role.'],
            ]);
        }

        DB::transaction(function () use ($workspace, $user, $role, $wasMember): void {
            $workspace->members()->syncWithoutDetaching([
                $user->id => ['role' => $role->value],
            ]);

            if (! $wasMember) {
                $user->forceFill([
                    'current_workspace_id' => $workspace->id,
                ])->save();
            }
        });

        return $user;
    }

    public function switchWorkspace(User $user, Workspace $workspace): void
    {
        if (! $user->belongsToWorkspace($workspace)) {
            throw ValidationException::withMessages([
                'workspace' => ['You do not belong to that workspace.'],
            ]);
        }

        $user->forceFill([
            'current_workspace_id' => $workspace->id,
        ])->save();
    }
}
