<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'start_date',
        'due_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_collaborators')
            ->withPivot(['role', 'invited_by'])
            ->withTimestamps();
    }

    public function invitations(): MorphMany
    {
        return $this->morphMany(Invitation::class, 'invitable');
    }

    public function hasAccessFor(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        if ($this->collaborators()->whereKey($user->id)->exists()) {
            return true;
        }

        return $this->project_id !== null
            && $this->project()->first()?->hasAccessFor($user) === true;
    }

    public function roleFor(User $user): ?MembershipRole
    {
        if ($this->user_id === $user->id) {
            return MembershipRole::Admin;
        }

        $direct = null;
        $collaborator = $this->collaborators()->whereKey($user->id)->first();

        if ($collaborator) {
            $direct = MembershipRole::tryParse($collaborator->pivot->role);
        }

        if ($direct !== null) {
            return $direct;
        }

        return $this->project_id !== null
            ? $this->project()->first()?->roleFor($user)
            : null;
    }

    public function canManage(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->roleFor($user)?->canManage() === true;
    }

    public function canComplete(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->roleFor($user)?->canComplete() === true;
    }

    public function canComment(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->roleFor($user)?->canComment() === true;
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeAccessibleFor(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('collaborators', fn (Builder $c) => $c->whereKey($user->id))
                ->orWhereHas('project', function (Builder $p) use ($user) {
                    $p->where(function (Builder $p2) use ($user) {
                        $p2->where('user_id', $user->id)
                            ->orWhereHas('members', fn (Builder $m) => $m->whereKey($user->id))
                            ->orWhereHas('workspace', fn (Builder $w) => $w->where(function (Builder $w2) use ($user) {
                                $w2->where('user_id', $user->id)
                                    ->orWhereHas('members', fn (Builder $m) => $m->whereKey($user->id));
                            }));
                    });
                });
        });
    }

    public function totalTrackedSeconds(): int
    {
        return (int) $this->timeEntries->sum(fn (TimeEntry $entry) => $entry->elapsedSeconds());
    }

    public function runningTimeEntry(): ?TimeEntry
    {
        return $this->timeEntries->firstWhere('ended_at', null);
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->status !== TaskStatus::Completed
            && $this->due_date->isPast();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    protected static function booted(): void
    {
        static::creating(function (Task $task): void {
            if ($task->start_date === null) {
                $task->start_date = now()->toDateString();
            }
        });

        static::saving(function (Task $task): void {
            if ($task->start_date === null) {
                $task->start_date = now()->toDateString();
            }

            if ($task->status === TaskStatus::Completed && $task->completed_at === null) {
                $task->completed_at = now();
            }

            if ($task->status !== TaskStatus::Completed) {
                $task->completed_at = null;
            }
        });

        static::deleting(function (Task $task): void {
            $task->loadMissing('attachments');

            foreach ($task->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->path);
            }
        });
    }
}
