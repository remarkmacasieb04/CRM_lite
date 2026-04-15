<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->resolveCurrentWorkspace() !== null;
    }

    public function view(User $user, Task $task): bool
    {
        return $task->workspace !== null && $user->belongsToWorkspace($task->workspace);
    }

    public function create(User $user): bool
    {
        return $user->resolveCurrentWorkspace() !== null;
    }

    public function update(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->view($user, $task);
    }
}
