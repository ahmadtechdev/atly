<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-atly-muted text-atly-ink',
            self::InProgress => 'bg-atly-accent/30 text-atly-ink',
            self::Completed => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
        };
    }
}
