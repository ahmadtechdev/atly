<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminStatsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private AdminStatsService $adminStats) {}

    public function __invoke(): View
    {
        return view('admin.dashboard.index', [
            'userStats' => $this->adminStats->userStats(),
            'platformStats' => $this->adminStats->platformStats(),
        ]);
    }
}
