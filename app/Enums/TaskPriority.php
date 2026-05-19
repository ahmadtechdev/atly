<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
            self::Medium => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
            self::High => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-200',
        };
    }
}
