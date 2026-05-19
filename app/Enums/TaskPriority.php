<?php

namespace App\Enums;

enum TaskPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:ring-slate-600',
            self::Medium => 'bg-sky-100 text-sky-800 ring-1 ring-sky-200 dark:bg-sky-900/40 dark:text-sky-200 dark:ring-sky-800',
            self::High => 'bg-amber-100 text-amber-900 ring-1 ring-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:ring-amber-800',
            self::Urgent => 'bg-red-100 text-red-800 ring-1 ring-red-300 dark:bg-red-900/50 dark:text-red-200 dark:ring-red-800',
        };
    }

    public function dotClass(): string
    {
        return match ($this) {
            self::Low => 'bg-slate-400',
            self::Medium => 'bg-sky-500',
            self::High => 'bg-amber-500',
            self::Urgent => 'bg-red-500',
        };
    }
}
