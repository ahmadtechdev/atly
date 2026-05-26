<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlueprintDraftMember extends Model
{
    protected $fillable = [
        'blueprint_draft_id',
        'user_id',
        'invitation_id',
        'name',
        'email',
        'skills',
        'split',
        'accepted_at',
        'declined_at',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'declined_at' => 'datetime',
            'split' => 'integer',
        ];
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(BlueprintDraft::class, 'blueprint_draft_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isDeclined(): bool
    {
        return $this->declined_at !== null;
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && $this->declined_at === null;
    }
}
