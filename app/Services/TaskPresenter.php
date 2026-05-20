<?php

namespace App\Services;

use App\Enums\MembershipRole;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskComment;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TaskPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function payload(Task $task): array
    {
        $task->loadMissing([
            'attachments',
            'user',
            'project.workspace',
            'timeEntries',
            'collaborators',
            'comments.user',
        ]);

        /** @var User|null $viewer */
        $viewer = Auth::user();
        $isOwner = $viewer !== null && $task->user_id === $viewer->id;
        $viewerRole = $viewer !== null ? $task->roleFor($viewer) : null;
        $canManage = $viewer !== null && $task->canManage($viewer);
        $canComplete = $viewer !== null && $task->canComplete($viewer);
        $canComment = $viewer !== null && $task->canComment($viewer);

        $running = $task->runningTimeEntry();
        $totalSeconds = $task->totalTrackedSeconds();
        $baseSeconds = (int) $task->timeEntries
            ->filter(fn (TimeEntry $entry) => ! $entry->isRunning())
            ->sum(fn (TimeEntry $entry) => $entry->elapsedSeconds());
        $hasEntries = $task->timeEntries->isNotEmpty();
        $canTrack = $task->status !== TaskStatus::Completed && $canComplete;

        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'status_label' => $task->status->label(),
            'status_class' => $task->status->colorClass(),
            'is_pending' => $task->status === TaskStatus::Pending,
            'is_in_progress' => $task->status === TaskStatus::InProgress,
            'is_completed' => $task->status === TaskStatus::Completed,
            'can_complete' => $task->status !== TaskStatus::Pending,
            'priority' => $task->priority->value,
            'priority_label' => $task->priority->label(),
            'priority_class' => $task->priority->colorClass(),
            'priority_dot' => $task->priority->dotClass(),
            'start_date' => $task->start_date?->format('M j, Y'),
            'due_date' => $task->due_date?->format('M j, Y'),
            'is_overdue' => $task->isOverdue(),
            'edit_url' => route('tasks.edit', $task),
            'delete_url' => route('tasks.destroy', $task),
            'start_url' => route('tasks.start', $task),
            'complete_url' => route('tasks.toggle-complete', $task),
            'update_project_url' => route('tasks.update-project', $task),
            'show_url' => route('tasks.show', $task),
            'comments_url' => route('tasks.comments.store', $task),
            'time_tracking' => [
                'total_seconds' => $totalSeconds,
                'total_label' => TimeEntry::formatSeconds($totalSeconds),
                'base_seconds' => $baseSeconds,
                'is_running' => $running !== null,
                'has_entries' => $hasEntries,
                'can_track' => $canTrack,
                'running_started_at_unix_ms' => $running?->started_at->getTimestampMs(),
                'start_url' => $canTrack ? route('time-tracker.start') : null,
                'stop_url' => $running !== null ? route('time-tracker.stop', $running) : null,
            ],
            'assignee' => [
                'name' => $task->user->name,
                'initials' => $task->user->initials(),
                'avatar_url' => $task->user->avatar_url,
            ],
            'owner' => [
                'id' => $task->user->id,
                'name' => $task->user->name,
                'initials' => $task->user->initials(),
                'avatar_url' => $task->user->avatar_url,
            ],
            'collaborators' => $task->collaborators->map(function (User $member) use ($task, $isOwner) {
                $role = MembershipRole::tryParse($member->pivot->role);

                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'initials' => $member->initials(),
                    'avatar_url' => $member->avatar_url,
                    'role' => $role?->value,
                    'role_label' => $role?->label(),
                    'role_class' => $role?->colorClass(),
                    'update_url' => $isOwner ? route('tasks.collaborators.update', [$task->id, $member->id]) : null,
                    'remove_url' => $isOwner ? route('tasks.collaborators.destroy', [$task->id, $member->id]) : null,
                ];
            })->values(),
            'viewer' => [
                'is_owner' => $isOwner,
                'role' => $viewerRole?->value,
                'role_label' => $viewerRole?->label(),
                'role_class' => $viewerRole?->colorClass(),
                'can_edit' => $canManage,
                'can_delete' => $isOwner,
                'can_invite' => $canManage,
                'can_complete' => $canComplete,
                'can_comment' => $canComment,
            ],
            'comments' => $task->comments->sortBy('created_at')->values()->map(function (TaskComment $comment) use ($viewer, $task) {
                $canDelete = $viewer !== null
                    && ($comment->user_id === $viewer->id || $task->canManage($viewer));

                return [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'created_at' => $comment->created_at?->toIso8601String(),
                    'created_at_label' => $comment->created_at?->diffForHumans(),
                    'can_delete' => $canDelete,
                    'delete_url' => route('tasks.comments.destroy', [$task->id, $comment->id]),
                    'author' => [
                        'id' => $comment->user?->id,
                        'name' => $comment->user?->name,
                        'initials' => $comment->user?->initials(),
                        'avatar_url' => $comment->user?->avatar_url,
                    ],
                ];
            })->values(),
            'comments_count' => $task->comments->count(),
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => Str::limit($task->project->name, 30),
                'full_name' => $task->project->fullName(),
                'color' => $task->project->color,
                'url' => route('projects.show', $task->project),
                'workspace' => $task->project->workspace ? [
                    'id' => $task->project->workspace->id,
                    'name' => Str::limit($task->project->workspace->name, 30),
                    'color' => $task->project->workspace->color,
                    'url' => route('workspaces.show', $task->project->workspace),
                ] : null,
            ] : null,
            'attachments' => $task->attachments->map(fn (TaskAttachment $file) => [
                'id' => $file->id,
                'name' => $file->original_name,
                'url' => $file->url(),
                'is_image' => $file->isImage(),
                'size' => number_format($file->size / 1024, 1).' KB',
            ]),
        ];
    }
}
