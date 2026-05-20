<?php

namespace App\Models;

use App\Enums\MembershipRole;
use Database\Factories\WorkspaceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Workspace extends Model
{
    /** @use HasFactory<WorkspaceFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'color',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Project::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_members')
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

        return $this->members()->whereKey($user->id)->exists();
    }

    public function roleFor(User $user): ?MembershipRole
    {
        if ($this->user_id === $user->id) {
            return MembershipRole::Admin;
        }

        $member = $this->members()->whereKey($user->id)->first();

        return $member ? MembershipRole::tryParse($member->pivot->role) : null;
    }

    public function canManage(User $user): bool
    {
        if ($this->user_id === $user->id) {
            return true;
        }

        return $this->roleFor($user)?->canManage() === true;
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeAccessibleFor(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('members', fn (Builder $m) => $m->whereKey($user->id));
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }
}
