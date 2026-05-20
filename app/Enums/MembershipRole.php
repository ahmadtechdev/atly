<?php

namespace App\Enums;

/**
 * Roles that an invited collaborator can hold on a Task, Project, or Workspace.
 *
 * The owner of the entity is always the creator and never stored as a membership
 * row — `Owner` is implicit and lives in `user_id` on the entity itself.
 */
enum MembershipRole: string
{
    case Admin = 'admin';
    case Assignee = 'assignee';
    case Guest = 'guest';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Assignee => 'Assignee',
            self::Guest => 'Guest',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Can invite others and assign work.',
            self::Assignee => 'Can complete work, cannot invite others.',
            self::Guest => 'Read-only access.',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Admin => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200',
            self::Assignee => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
            self::Guest => 'bg-atly-muted text-atly-ink-soft',
        };
    }

    public function canInvite(): bool
    {
        return $this === self::Admin;
    }

    public function canManage(): bool
    {
        return $this === self::Admin;
    }

    public function canComplete(): bool
    {
        return $this === self::Admin || $this === self::Assignee;
    }

    /**
     * Try to resolve a stored value (or null) to an enum case.
     */
    public static function tryParse(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }

    /**
     * @return array<int, self>
     */
    public static function assignable(): array
    {
        return [self::Admin, self::Assignee, self::Guest];
    }
}
