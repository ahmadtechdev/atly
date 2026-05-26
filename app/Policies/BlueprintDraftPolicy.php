<?php

namespace App\Policies;

use App\Models\BlueprintDraft;
use App\Models\User;

class BlueprintDraftPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BlueprintDraft $draft): bool
    {
        return $draft->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, BlueprintDraft $draft): bool
    {
        return $draft->user_id === $user->id && ! $draft->isFinalized();
    }

    public function delete(User $user, BlueprintDraft $draft): bool
    {
        return $draft->user_id === $user->id;
    }

    public function finalize(User $user, BlueprintDraft $draft): bool
    {
        return $draft->user_id === $user->id && ! $draft->isFinalized();
    }

    public function invite(User $user, BlueprintDraft $draft): bool
    {
        return $draft->user_id === $user->id && ! $draft->isFinalized();
    }
}
