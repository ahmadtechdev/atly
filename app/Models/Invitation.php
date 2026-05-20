<?php

namespace App\Models;

use App\Enums\InvitationStatus;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    /** @use HasFactory<InvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'inviter_id',
        'invitee_id',
        'invitee_email',
        'invitable_type',
        'invitable_id',
        'role',
        'status',
        'message',
        'token',
        'expires_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvitationStatus::class,
            'expires_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    public function invitee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    public function invitable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPending(): bool
    {
        return $this->status === InvitationStatus::Pending && ! $this->isExpired();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function invitableLabel(): string
    {
        $invitable = $this->invitable;

        if ($invitable === null) {
            return '(deleted)';
        }

        return match (true) {
            $invitable instanceof Task => $invitable->title,
            $invitable instanceof Project, $invitable instanceof Workspace => $invitable->name,
            default => class_basename($invitable),
        };
    }

    public function invitableKind(): string
    {
        return match ($this->invitable_type) {
            Task::class => 'task',
            Project::class => 'project',
            Workspace::class => 'workspace',
            default => 'item',
        };
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', InvitationStatus::Pending->value)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForRecipient(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('invitee_id', $user->id)
                ->orWhereRaw('LOWER(invitee_email) = ?', [strtolower($user->email)]);
        });
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForInviter(Builder $query, User $user): Builder
    {
        return $query->where('inviter_id', $user->id);
    }

    protected static function booted(): void
    {
        static::creating(function (Invitation $invitation): void {
            if ($invitation->token === null) {
                $invitation->token = (string) Str::ulid().bin2hex(random_bytes(8));
            }
        });
    }
}
