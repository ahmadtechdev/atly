<?php

namespace App\Enums;

enum BlueprintDraftStatus: string
{
    case Draft = 'draft';
    case AwaitingInvites = 'awaiting_invites';
    case Finalized = 'finalized';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::AwaitingInvites => 'Awaiting invites',
            self::Finalized => 'Finalized',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Draft => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-700',
            self::AwaitingInvites => 'bg-amber-100 text-amber-900 ring-1 ring-amber-200 dark:bg-amber-950/40 dark:text-amber-200 dark:ring-amber-800',
            self::Finalized => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-200 dark:ring-emerald-800',
        };
    }
}
