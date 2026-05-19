<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
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

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
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
