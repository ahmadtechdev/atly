<?php

namespace App\Enums;

enum InvitationStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
            self::Accepted => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
            self::Declined => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200',
            self::Cancelled => 'bg-atly-muted text-atly-ink-soft',
            self::Expired => 'bg-atly-muted text-atly-ink-soft',
        };
    }

    public function isFinal(): bool
    {
        return $this !== self::Pending;
    }
}
