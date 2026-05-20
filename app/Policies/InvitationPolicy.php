<?php

namespace App\Policies;

use App\Models\Invitation;
use App\Models\User;

class InvitationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Invitation $invitation): bool
    {
        return $this->isParticipant($user, $invitation);
    }

    public function respond(User $user, Invitation $invitation): bool
    {
        return $invitation->invitee_id === $user->id
            || strtolower($invitation->invitee_email) === strtolower($user->email);
    }

    public function cancel(User $user, Invitation $invitation): bool
    {
        return $invitation->inviter_id === $user->id;
    }

    private function isParticipant(User $user, Invitation $invitation): bool
    {
        return $invitation->inviter_id === $user->id
            || $invitation->invitee_id === $user->id
            || strtolower($invitation->invitee_email) === strtolower($user->email);
    }
}
