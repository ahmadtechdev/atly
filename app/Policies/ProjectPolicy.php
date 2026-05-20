<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $project->hasAccessFor($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $project->canManage($user);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    public function invite(User $user, Project $project): bool
    {
        return $project->canManage($user);
    }
}
