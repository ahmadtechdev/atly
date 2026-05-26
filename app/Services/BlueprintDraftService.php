<?php

namespace App\Services;

use App\Enums\BlueprintDraftStatus;
use App\Enums\InvitationStatus;
use App\Enums\MembershipRole;
use App\Enums\TaskStatus;
use App\Models\BlueprintDraft;
use App\Models\BlueprintDraftMember;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class BlueprintDraftService
{
    public function __construct(private readonly InvitationService $invitations) {}

    /**
     * Persist a draft from a generated blueprint payload.
     *
     * @param  array<string, mixed>  $payload  shape: ['project' => [...], 'tasks' => [...], 'members' => [...], 'workspace_id' => int|null, 'assignment_type' => string, 'start_date' => string, 'end_date' => string]
     */
    public function save(User $user, array $payload): BlueprintDraft
    {
        return DB::transaction(function () use ($user, $payload): BlueprintDraft {
            $draft = BlueprintDraft::query()->create([
                'user_id' => $user->id,
                'workspace_id' => $this->resolveWorkspaceId($user, $payload['workspace_id'] ?? null),
                'name' => (string) ($payload['project']['name'] ?? 'Untitled blueprint'),
                'description' => $payload['project']['description'] ?? null,
                'color' => (string) ($payload['project']['color'] ?? 'sky'),
                'assignment_type' => $payload['assignment_type'] ?? 'individual',
                'status' => BlueprintDraftStatus::Draft->value,
                'start_date' => $payload['start_date'],
                'end_date' => $payload['end_date'],
                'tasks' => $payload['tasks'] ?? [],
            ]);

            foreach ($payload['members'] ?? [] as $member) {
                if (! is_array($member)) {
                    continue;
                }

                $name = trim((string) ($member['name'] ?? ''));

                if ($name === '') {
                    continue;
                }

                $draft->members()->create([
                    'name' => $name,
                    'email' => $this->cleanEmail($member['email'] ?? null),
                    'skills' => trim((string) ($member['skills'] ?? $member['skill'] ?? '')) ?: null,
                    'split' => isset($member['split']) && $member['split'] !== '' ? (int) $member['split'] : null,
                ]);
            }

            return $draft->fresh(['members']);
        });
    }

    /**
     * Update a draft's contents (tasks, members, project meta).
     *
     * @param  array<string, mixed>  $payload
     */
    public function update(BlueprintDraft $draft, array $payload): BlueprintDraft
    {
        if ($draft->isFinalized()) {
            throw ValidationException::withMessages(['draft' => 'This blueprint has already been finalized.']);
        }

        return DB::transaction(function () use ($draft, $payload): BlueprintDraft {
            $updates = array_filter([
                'name' => $payload['project']['name'] ?? null,
                'description' => $payload['project']['description'] ?? null,
                'color' => $payload['project']['color'] ?? null,
                'workspace_id' => array_key_exists('workspace_id', $payload)
                    ? $this->resolveWorkspaceId($draft->user, $payload['workspace_id'])
                    : null,
                'tasks' => $payload['tasks'] ?? null,
            ], fn ($v) => $v !== null);

            if ($updates !== []) {
                $draft->fill($updates)->save();
            }

            if (array_key_exists('members', $payload) && is_array($payload['members'])) {
                $this->syncMembers($draft, $payload['members']);
            }

            return $draft->fresh(['members', 'workspace']);
        });
    }

    /**
     * Send invitations for every team member that has an email and no pending invite yet.
     */
    public function sendInvitations(BlueprintDraft $draft, ?string $message = null): BlueprintDraft
    {
        if (! $draft->isTeam()) {
            throw ValidationException::withMessages(['draft' => 'Invitations only apply to team blueprints.']);
        }

        $draft->loadMissing('members.invitation');

        $errors = [];

        foreach ($draft->members as $member) {
            if ($member->email === null || $member->email === '') {
                continue;
            }

            if ($member->invitation_id !== null && $member->invitation?->isPending()) {
                continue;
            }

            if ($member->accepted_at !== null) {
                continue;
            }

            try {
                $invitation = $this->invitations->send(
                    inviter: $draft->user,
                    invitable: $draft,
                    email: $member->email,
                    message: $message ?: 'You have been invited to collaborate on a project plan.',
                    role: MembershipRole::Assignee->value,
                );

                $member->update([
                    'invitation_id' => $invitation->id,
                    'declined_at' => null,
                ]);
            } catch (ValidationException $e) {
                $errors[$member->email] = $e->errors()['email'][0] ?? 'Could not invite.';
            }
        }

        $draft->forceFill([
            'status' => BlueprintDraftStatus::AwaitingInvites->value,
            'invited_at' => $draft->invited_at ?? now(),
        ])->save();

        if ($errors !== []) {
            throw ValidationException::withMessages(['email' => array_values($errors)]);
        }

        return $draft->fresh(['members.invitation']);
    }

    /**
     * Mark a member as accepted (called from InvitationService on accept).
     * Auto-finalizes when every emailed member has accepted.
     */
    public function handleAcceptance(BlueprintDraft $draft, User $user): void
    {
        $member = $draft->members()
            ->whereRaw('LOWER(email) = ?', [strtolower($user->email)])
            ->first();

        if ($member === null) {
            return;
        }

        $member->update([
            'user_id' => $user->id,
            'accepted_at' => now(),
            'declined_at' => null,
        ]);

        $draft->loadMissing('members');

        if ($draft->allTeamMembersAccepted() && ! $draft->isFinalized()) {
            $this->finalize($draft);
        }
    }

    /**
     * Mark a member as declined (called from InvitationService on decline).
     */
    public function handleDecline(BlueprintDraft $draft, User $user): void
    {
        $member = $draft->members()
            ->whereRaw('LOWER(email) = ?', [strtolower($user->email)])
            ->first();

        $member?->update([
            'declined_at' => now(),
            'accepted_at' => null,
        ]);
    }

    /**
     * Convert the draft into a real Project + Tasks. Tasks whose `assigned_to`
     * matches an accepted team member get a TaskCollaborator linking that user.
     */
    public function finalize(BlueprintDraft $draft): Project
    {
        if ($draft->isFinalized() && $draft->finalized_project_id) {
            return $draft->project ?? throw ValidationException::withMessages(['draft' => 'Finalized project missing.']);
        }

        return DB::transaction(function () use ($draft): Project {
            $draft->loadMissing('members.user');

            /** @var Project $project */
            $project = $draft->user->projects()->create([
                'name' => $draft->name,
                'description' => $draft->description,
                'color' => $draft->color,
                'workspace_id' => $draft->workspace_id,
            ]);

            $acceptedMembers = $draft->members
                ->filter(fn ($m) => $m->user_id !== null && $m->accepted_at !== null)
                ->keyBy(fn ($m) => strtolower($m->name));

            foreach ($acceptedMembers as $member) {
                DB::table('project_members')->updateOrInsert(
                    ['project_id' => $project->id, 'user_id' => $member->user_id],
                    [
                        'role' => MembershipRole::Assignee->value,
                        'invited_by' => $draft->user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }

            $now = now();
            $taskRows = [];

            foreach ($draft->tasks ?? [] as $task) {
                if (! is_array($task) || trim((string) ($task['title'] ?? '')) === '') {
                    continue;
                }

                $taskRows[] = [
                    'user_id' => $draft->user_id,
                    'project_id' => $project->id,
                    'title' => (string) $task['title'],
                    'description' => $task['description'] ?? null,
                    'status' => TaskStatus::Pending->value,
                    'priority' => $task['priority'] ?? 'medium',
                    'start_date' => $task['start_date'] ?? $draft->start_date->toDateString(),
                    'due_date' => $task['due_date'] ?? $draft->end_date->toDateString(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($taskRows !== []) {
                Task::insert($taskRows);
            }

            $assignmentMap = $draft->tasks ?? [];
            if ($assignmentMap !== [] && $acceptedMembers->isNotEmpty()) {
                $newTasks = Task::query()
                    ->where('project_id', $project->id)
                    ->orderBy('id')
                    ->get();

                foreach ($newTasks as $index => $task) {
                    $assignedName = $assignmentMap[$index]['assigned_to'] ?? null;

                    if (! is_string($assignedName)) {
                        continue;
                    }

                    $member = $acceptedMembers[strtolower($assignedName)] ?? null;

                    if ($member === null) {
                        continue;
                    }

                    DB::table('task_collaborators')->updateOrInsert(
                        ['task_id' => $task->id, 'user_id' => $member->user_id],
                        [
                            'role' => MembershipRole::Assignee->value,
                            'invited_by' => $draft->user_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                }
            }

            $draft->forceFill([
                'status' => BlueprintDraftStatus::Finalized->value,
                'finalized_project_id' => $project->id,
                'finalized_at' => now(),
            ])->save();

            return $project;
        });
    }

    public function delete(BlueprintDraft $draft): void
    {
        DB::transaction(function () use ($draft): void {
            $draft->invitations()->delete();
            $draft->delete();
        });
    }

    /**
     * @param  array<int, mixed>  $members
     */
    private function syncMembers(BlueprintDraft $draft, array $members): void
    {
        $draft->loadMissing('members');

        $existingByKey = $draft->members->keyBy('id');
        $seenIds = [];

        foreach ($members as $member) {
            if (! is_array($member)) {
                continue;
            }

            $name = trim((string) ($member['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $payload = [
                'name' => $name,
                'email' => $this->cleanEmail($member['email'] ?? null),
                'skills' => trim((string) ($member['skills'] ?? $member['skill'] ?? '')) ?: null,
                'split' => isset($member['split']) && $member['split'] !== '' ? (int) $member['split'] : null,
            ];

            $id = $member['id'] ?? null;

            if ($id !== null && isset($existingByKey[$id])) {
                $record = $existingByKey[$id];
                $emailChanged = $record->email !== $payload['email'];

                $record->fill($payload);

                if ($emailChanged) {
                    $record->invitation_id = null;
                    $record->accepted_at = null;
                    $record->declined_at = null;
                }

                $record->save();
                $seenIds[] = $record->id;

                continue;
            }

            $created = $draft->members()->create($payload);
            $seenIds[] = $created->id;
        }

        $draft->members()
            ->whereNotIn('id', $seenIds)
            ->each(function (BlueprintDraftMember $stale): void {
                if ($stale->invitation_id !== null) {
                    try {
                        $stale->invitation?->update([
                            'status' => InvitationStatus::Cancelled,
                            'responded_at' => now(),
                        ]);
                    } catch (Throwable) {
                        // Best-effort cancel; ignore.
                    }
                }
                $stale->delete();
            });
    }

    private function resolveWorkspaceId(User $user, mixed $workspaceId): ?int
    {
        if ($workspaceId === null || $workspaceId === '' || $workspaceId === 0) {
            return null;
        }

        $exists = Workspace::query()->forUser($user)->whereKey($workspaceId)->exists();

        return $exists ? (int) $workspaceId : null;
    }

    private function cleanEmail(mixed $email): ?string
    {
        if (! is_string($email)) {
            return null;
        }

        $email = strtolower(trim($email));

        return $email === '' ? null : $email;
    }
}
