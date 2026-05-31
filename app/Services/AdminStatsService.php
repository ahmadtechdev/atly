<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;

class AdminStatsService
{
    /**
     * @return array<string, int|float>
     */
    public function userStats(): array
    {
        $users = User::query()->where('is_super_admin', false);

        $total = (clone $users)->count();
        $verified = (clone $users)->whereNotNull('email_verified_at')->count();
        $pendingVerification = (clone $users)->whereNull('email_verified_at')->count();

        return [
            'total_registered' => $total,
            'verified' => $verified,
            'verification_pending' => $pendingVerification,
            'registered_today' => (clone $users)->whereDate('created_at', today())->count(),
            'registered_this_week' => (clone $users)->where('created_at', '>=', now()->startOfWeek())->count(),
            'verification_rate' => $total > 0 ? round(($verified / $total) * 100, 1) : 0.0,
        ];
    }

    /**
     * @return array<string, int>
     */
    public function platformStats(): array
    {
        return [
            'workspaces' => Workspace::query()->count(),
            'projects' => Project::query()->count(),
            'tasks' => Task::query()->count(),
            'invitations' => Invitation::query()->count(),
        ];
    }
}
