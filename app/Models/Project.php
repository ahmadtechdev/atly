<?php

namespace App\Models;

use App\Enums\MembershipRole;
use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workspace_id',
        'name',
        'description',
        'color',
        'status',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
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

        if ($this->members()->whereKey($user->id)->exists()) {
            return true;
        }

        return $this->workspace_id !== null
            && $this->workspace()->first()?->hasAccessFor($user) === true;
    }

    public function roleFor(User $user): ?MembershipRole
    {
        if ($this->user_id === $user->id) {
            return MembershipRole::Admin;
        }

        $direct = null;
        $member = $this->members()->whereKey($user->id)->first();

        if ($member) {
            $direct = MembershipRole::tryParse($member->pivot->role);
        }

        if ($direct !== null) {
            return $direct;
        }

        return $this->workspace_id !== null
            ? $this->workspace()->first()?->roleFor($user)
            : null;
    }

    public function canManage(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->roleFor($user)?->canManage() === true;
    }

    public function canComment(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->roleFor($user)?->canComment() === true;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeAccessibleFor(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('members', fn (Builder $m) => $m->whereKey($user->id))
                ->orWhereHas('workspace', fn (Builder $w) => $w->where(function (Builder $w2) use ($user) {
                    $w2->where('user_id', $user->id)
                        ->orWhereHas('members', fn (Builder $m) => $m->whereKey($user->id));
                }));
        });
    }

    public function fullName(): string
    {
        return $this->relationLoaded('workspace') && $this->workspace
            ? $this->workspace->name.' / '.$this->name
            : $this->name;
    }

    public function isCompleted(): bool
    {
        return $this->status === ProjectStatus::Completed;
    }

    public function hasOutstandingTasks(): bool
    {
        return $this->tasks()->where('status', '!=', TaskStatus::Completed->value)->exists();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::Active->value);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::Completed->value);
    }
}
