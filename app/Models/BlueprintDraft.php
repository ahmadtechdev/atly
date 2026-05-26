<?php

namespace App\Models;

use App\Enums\BlueprintDraftStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BlueprintDraft extends Model
{
    protected $fillable = [
        'user_id',
        'workspace_id',
        'finalized_project_id',
        'name',
        'description',
        'color',
        'assignment_type',
        'status',
        'start_date',
        'end_date',
        'tasks',
        'invited_at',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BlueprintDraftStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'tasks' => 'array',
            'invited_at' => 'datetime',
            'finalized_at' => 'datetime',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'finalized_project_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(BlueprintDraftMember::class);
    }

    public function invitations(): MorphMany
    {
        return $this->morphMany(Invitation::class, 'invitable');
    }

    public function isTeam(): bool
    {
        return $this->assignment_type === 'team';
    }

    public function isFinalized(): bool
    {
        return $this->status === BlueprintDraftStatus::Finalized;
    }

    public function acceptedMembersCount(): int
    {
        return $this->members->whereNotNull('accepted_at')->count();
    }

    public function pendingMembersCount(): int
    {
        return $this->members
            ->whereNull('accepted_at')
            ->whereNull('declined_at')
            ->count();
    }

    public function allTeamMembersAccepted(): bool
    {
        if (! $this->isTeam()) {
            return true;
        }

        $emailedMembers = $this->members->whereNotNull('email');

        if ($emailedMembers->isEmpty()) {
            return false;
        }

        return $emailedMembers->every(fn (BlueprintDraftMember $m) => $m->accepted_at !== null);
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
        return $query->whereNot('status', BlueprintDraftStatus::Finalized->value);
    }
}
