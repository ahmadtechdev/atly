<?php

namespace App\Enums;

/**
 * Roles a collaborator can hold on a Task, Project, or Workspace.
 *
 * The owner of the entity is implicit (the creator, stored in `user_id`) and
 * is never written as a membership row.
 */
enum MembershipRole: string
{
    case Admin = 'admin';
    case Assignee = 'assignee';
    case Viewer = 'viewer';
    case Guest = 'guest';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Assignee => 'Assignee',
            self::Viewer => 'Viewer',
            self::Guest => 'Guest',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Can invite others, assign work, and edit settings.',
            self::Assignee => 'Can complete work and comment, cannot invite others.',
            self::Viewer => 'Can view and comment only.',
            self::Guest => 'Can view and read comments. Cannot post comments.',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Admin => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200',
            self::Assignee => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
            self::Viewer => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
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

    public function canComment(): bool
    {
        return $this !== self::Guest;
    }

    /**
     * Numeric weight so we can pick the "highest" role across a hierarchy.
     */
    public function weight(): int
    {
        return match ($this) {
            self::Admin => 40,
            self::Assignee => 30,
            self::Viewer => 20,
            self::Guest => 10,
        };
    }

    public static function tryParse(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }

    /**
     * Pick the strongest of two roles (either may be null).
     */
    public static function strongest(?self $a, ?self $b): ?self
    {
        if ($a === null) {
            return $b;
        }

        if ($b === null) {
            return $a;
        }

        return $a->weight() >= $b->weight() ? $a : $b;
    }

    /**
     * @return array<int, self>
     */
    public static function assignable(): array
    {
        return [self::Admin, self::Assignee, self::Viewer, self::Guest];
    }
}
