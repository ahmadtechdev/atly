<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View|JsonResponse
    {
        $user = $request->user();
        $baseQuery = Task::query()->accessibleFor($user);

        $calendarPayload = $this->buildCalendar($request, $baseQuery);

        if ($request->boolean('calendar_only')) {
            return response()->json([
                'html' => view('dashboard.partials.calendar', $calendarPayload)->render(),
                'month_label' => $calendarPayload['calendarMonth']->format('F Y'),
                'month' => $calendarPayload['calendarMonth']->format('Y-m'),
            ]);
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'completed' => (clone $baseQuery)->where('status', TaskStatus::Completed)->count(),
            'in_progress' => (clone $baseQuery)->where('status', TaskStatus::InProgress)->count(),
            'pending' => (clone $baseQuery)->where('status', TaskStatus::Pending)->count(),
            'overdue' => (clone $baseQuery)
                ->where('status', '!=', TaskStatus::Completed)
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', now())
                ->count(),
            'due_today' => (clone $baseQuery)
                ->where('status', '!=', TaskStatus::Completed)
                ->whereDate('due_date', today())
                ->count(),
        ];

        $completionRate = $stats['total'] > 0
            ? (int) round(($stats['completed'] / $stats['total']) * 100)
            : 0;

        $statusChart = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->mapWithKeys(fn ($total, $status) => [
                TaskStatus::from($status)->label() => $total,
            ]);

        $priorityChart = (clone $baseQuery)
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $weeklyActivity = collect(range(6, 0))->map(function (int $daysAgo) use ($user) {
            $date = now()->subDays($daysAgo)->startOfDay();

            return [
                'label' => $date->format('D'),
                'created' => Task::query()
                    ->accessibleFor($user)
                    ->whereDate('created_at', $date)
                    ->count(),
                'completed' => Task::query()
                    ->accessibleFor($user)
                    ->whereDate('completed_at', $date)
                    ->count(),
            ];
        });

        $upcomingTasks = (clone $baseQuery)
            ->where('status', '!=', TaskStatus::Completed)
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'stats' => $stats,
            'completionRate' => $completionRate,
            'statusChart' => $statusChart,
            'priorityChart' => $priorityChart,
            'weeklyActivity' => $weeklyActivity,
            'upcomingTasks' => $upcomingTasks,
            ...$calendarPayload,
        ]);
    }

    /**
     * @return array{calendarMonth: Carbon, calendarWeeks: array<int, array<int, array{date: Carbon, in_month: bool, is_today: bool, tasks: Collection}>>}
     */
    private function buildCalendar(Request $request, Builder $baseQuery): array
    {
        $calendarMonth = Carbon::parse($request->input('month', now()->format('Y-m')))->startOfMonth();
        $calendarStart = $calendarMonth->copy()->startOfWeek();
        $calendarEnd = $calendarMonth->copy()->endOfMonth()->endOfWeek();

        $tasksByDate = (clone $baseQuery)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$calendarStart->toDateString(), $calendarEnd->toDateString()])
            ->get()
            ->groupBy(fn (Task $task) => $task->due_date->format('Y-m-d'));

        $calendarWeeks = [];
        $cursor = $calendarStart->copy();
        while ($cursor <= $calendarEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dateKey = $cursor->format('Y-m-d');
                $week[] = [
                    'date' => $cursor->copy(),
                    'in_month' => $cursor->month === $calendarMonth->month,
                    'is_today' => $cursor->isToday(),
                    'tasks' => $tasksByDate->get($dateKey, collect()),
                ];
                $cursor->addDay();
            }
            $calendarWeeks[] = $week;
        }

        return [
            'calendarMonth' => $calendarMonth,
            'calendarWeeks' => $calendarWeeks,
        ];
    }
}
