<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        return $task->hasAccessFor($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $task->canManage($user) || $task->canComplete($user);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    public function invite(User $user, Task $task): bool
    {
        return $task->canManage($user);
    }
}
