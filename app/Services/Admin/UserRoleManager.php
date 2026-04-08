<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class UserRoleManager
{
    public function updateRole(User $target, UserRole $role): void
    {
        if ($target->role === $role) {
            return;
        }

        if ($target->isAdmin() && $role !== UserRole::Admin && $this->adminCount() <= 1) {
            throw ValidationException::withMessages([
                'role' => ['At least one admin account must remain in the system.'],
            ]);
        }

        $target->forceFill([
            'role' => $role,
        ])->save();
    }

    private function adminCount(): int
    {
        return User::query()
            ->where('role', UserRole::Admin->value)
            ->count();
    }
}
