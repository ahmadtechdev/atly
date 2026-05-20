<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;

class TaskCommentPolicy
{
    public function viewAny(User $user, Task $task): bool
    {
        return $task->hasAccessFor($user);
    }

    public function view(User $user, TaskComment $comment): bool
    {
        return $comment->task && $comment->task->hasAccessFor($user);
    }

    public function create(User $user, Task $task): bool
    {
        return $task->canComment($user);
    }

    public function delete(User $user, TaskComment $comment): bool
    {
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $comment->task?->canManage($user) === true;
    }
}
