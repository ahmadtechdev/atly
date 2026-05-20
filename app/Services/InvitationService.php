<?php

namespace App\Services;

use App\Enums\InvitationStatus;
use App\Enums\MembershipRole;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\InvitationReceivedNotification;
use App\Notifications\InvitationToJoinAtlyNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    /** @var array<class-string<Model>, array{table:string,foreign:string}> */
    private const TARGETS = [
        Task::class => ['table' => 'task_collaborators', 'foreign' => 'task_id'],
        Project::class => ['table' => 'project_members', 'foreign' => 'project_id'],
        Workspace::class => ['table' => 'workspace_members', 'foreign' => 'workspace_id'],
    ];

    /**
     * Create a fresh invitation. Throws ValidationException on duplicate or self-invite.
     */
    /**
     * Holds the recipient status of the most recent `send()` call so callers
     * (e.g. controllers) can surface contextual UI messages without an extra
     * lookup. Either 'registered' or 'pending_registration'.
     */
    public ?string $lastRecipientStatus = null;

    public function send(
        User $inviter,
        Model $invitable,
        string $email,
        ?string $message = null,
        ?string $role = null,
    ): Invitation {
        $email = strtolower(trim($email));
        $type = $invitable::class;

        if (! array_key_exists($type, self::TARGETS)) {
            throw ValidationException::withMessages(['email' => 'This entity cannot be invited to.']);
        }

        if (strtolower($inviter->email) === $email) {
            throw ValidationException::withMessages(['email' => 'You cannot invite yourself.']);
        }

        $invitee = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($invitee !== null && $this->alreadyMember($invitable, $invitee)) {
            throw ValidationException::withMessages(['email' => 'This user already has access.']);
        }

        $existing = Invitation::query()
            ->where('invitable_type', $type)
            ->where('invitable_id', $invitable->getKey())
            ->whereRaw('LOWER(invitee_email) = ?', [$email])
            ->where('status', InvitationStatus::Pending->value)
            ->first();

        if ($existing !== null) {
            throw ValidationException::withMessages(['email' => 'An invitation is already pending for this email.']);
        }

        $resolvedRole = MembershipRole::tryParse($role) ?? MembershipRole::Guest;

        $invitation = Invitation::create([
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee?->id,
            'invitee_email' => $email,
            'invitable_type' => $type,
            'invitable_id' => $invitable->getKey(),
            'role' => $resolvedRole->value,
            'status' => InvitationStatus::Pending,
            'message' => $message,
            'expires_at' => now()->addDays(14),
        ]);

        $this->lastRecipientStatus = $invitee === null ? 'pending_registration' : 'registered';

        $this->notify($invitation, $invitee, $email);

        return $invitation->fresh(['inviter', 'invitable']);
    }

    public function accept(Invitation $invitation, User $user): Invitation
    {
        $this->assertRecipient($invitation, $user);
        $this->assertActionable($invitation);

        DB::transaction(function () use ($invitation, $user): void {
            $target = self::TARGETS[$invitation->invitable_type] ?? null;

            if ($target === null) {
                throw ValidationException::withMessages(['invitation' => 'This invitation target is invalid.']);
            }

            DB::table($target['table'])->updateOrInsert(
                [$target['foreign'] => $invitation->invitable_id, 'user_id' => $user->id],
                [
                    'role' => $invitation->role,
                    'invited_by' => $invitation->inviter_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            $invitation->update([
                'invitee_id' => $user->id,
                'status' => InvitationStatus::Accepted,
                'responded_at' => now(),
            ]);
        });

        return $invitation->fresh(['inviter', 'invitable']);
    }

    public function decline(Invitation $invitation, User $user): Invitation
    {
        $this->assertRecipient($invitation, $user);
        $this->assertActionable($invitation);

        $invitation->update([
            'invitee_id' => $user->id,
            'status' => InvitationStatus::Declined,
            'responded_at' => now(),
        ]);

        return $invitation->fresh();
    }

    public function cancel(Invitation $invitation, User $inviter): Invitation
    {
        if ($invitation->inviter_id !== $inviter->id) {
            throw ValidationException::withMessages(['invitation' => 'You did not send this invitation.']);
        }

        $this->assertActionable($invitation);

        $invitation->update([
            'status' => InvitationStatus::Cancelled,
            'responded_at' => now(),
        ]);

        return $invitation->fresh();
    }

    /**
     * Backfill invitee_id for pending invitations matching a user's email.
     */
    public function linkPendingInvitationsForUser(User $user): int
    {
        return Invitation::query()
            ->whereNull('invitee_id')
            ->whereRaw('LOWER(invitee_email) = ?', [strtolower($user->email)])
            ->update(['invitee_id' => $user->id]);
    }

    private function alreadyMember(Model $invitable, User $user): bool
    {
        if ($invitable instanceof Task) {
            return $invitable->user_id === $user->id
                || $invitable->collaborators()->whereKey($user->id)->exists();
        }

        if ($invitable instanceof Project) {
            return $invitable->user_id === $user->id
                || $invitable->members()->whereKey($user->id)->exists();
        }

        if ($invitable instanceof Workspace) {
            return $invitable->user_id === $user->id
                || $invitable->members()->whereKey($user->id)->exists();
        }

        return false;
    }

    private function assertRecipient(Invitation $invitation, User $user): void
    {
        $matchesId = $invitation->invitee_id !== null && $invitation->invitee_id === $user->id;
        $matchesEmail = strtolower($invitation->invitee_email) === strtolower($user->email);

        if (! $matchesId && ! $matchesEmail) {
            throw ValidationException::withMessages(['invitation' => 'This invitation is not addressed to you.']);
        }
    }

    private function assertActionable(Invitation $invitation): void
    {
        if (! $invitation->isPending()) {
            throw ValidationException::withMessages(['invitation' => 'This invitation can no longer be modified.']);
        }
    }

    private function notify(Invitation $invitation, ?User $invitee, string $email): void
    {
        if ($invitee !== null) {
            $invitee->notify(new InvitationReceivedNotification($invitation));

            return;
        }

        Notification::route('mail', $email)
            ->notify(new InvitationToJoinAtlyNotification($invitation));
    }
}
