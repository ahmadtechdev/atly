<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use App\Services\TaskPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TimeTrackerController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $running = TimeEntry::query()
            ->forUser($user)
            ->running()
            ->with('task.project.workspace')
            ->first();

        $recent = TimeEntry::query()
            ->forUser($user)
            ->whereNotNull('ended_at')
            ->with('task.project')
            ->latest('started_at')
            ->limit(20)
            ->get();

        $totalToday = (int) TimeEntry::query()
            ->forUser($user)
            ->whereDate('started_at', now()->toDateString())
            ->get()
            ->sum(fn (TimeEntry $entry) => $entry->elapsedSeconds());

        return view('time-tracker.index', [
            'running' => $running,
            'recent' => $recent,
            'totalToday' => $totalToday,
        ]);
    }

    public function start(Request $request): RedirectResponse|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'task_id' => [
                'required',
                Rule::exists('tasks', 'id')
                    ->where(fn ($q) => $q->where('user_id', $user->id)->where('status', '!=', TaskStatus::Completed->value)),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->stopAllRunning($user);

        /** @var Task $task */
        $task = Task::query()->forUser($user)->findOrFail($data['task_id']);

        if ($task->status === TaskStatus::Pending) {
            $task->update(['status' => TaskStatus::InProgress]);
        }

        $entry = $user->timeEntries()->create([
            'task_id' => $task->id,
            'description' => $data['description'] ?? null,
            'started_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'entry' => $this->entryPayload($entry->fresh(['task.project'])),
                'task' => TaskPresenter::payload($task->fresh()),
            ]);
        }

        return redirect()
            ->route('time-tracker.index')
            ->with('status', 'Time tracking started.');
    }

    public function stop(Request $request, TimeEntry $entry): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $entry);

        if ($entry->isRunning()) {
            $endedAt = now();
            $entry->update([
                'ended_at' => $endedAt,
                'duration_seconds' => (int) $entry->started_at->diffInSeconds($endedAt),
            ]);
        }

        if ($request->ajax()) {
            $task = $entry->task()->first();

            return response()->json([
                'entry' => $this->entryPayload($entry->fresh(['task.project'])),
                'task' => $task ? TaskPresenter::payload($task) : null,
            ]);
        }

        return redirect()
            ->route('time-tracker.index')
            ->with('status', 'Time tracking stopped.');
    }

    public function destroy(Request $request, TimeEntry $entry): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $entry);

        $entry->delete();

        if ($request->ajax()) {
            return response()->json(['message' => 'Time entry removed.']);
        }

        return back()->with('status', 'Time entry removed.');
    }

    private function stopAllRunning(User $user): void
    {
        TimeEntry::query()
            ->forUser($user)
            ->running()
            ->get()
            ->each(function (TimeEntry $entry): void {
                $endedAt = now();
                $entry->update([
                    'ended_at' => $endedAt,
                    'duration_seconds' => (int) $entry->started_at->diffInSeconds($endedAt),
                ]);
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function entryPayload(TimeEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'task_id' => $entry->task_id,
            'task' => $entry->task ? [
                'id' => $entry->task->id,
                'title' => $entry->task->title,
                'project_name' => $entry->task->project?->name,
            ] : null,
            'description' => $entry->description,
            'started_at' => $entry->started_at?->toIso8601String(),
            'started_at_unix_ms' => $entry->started_at?->getTimestampMs(),
            'ended_at' => $entry->ended_at?->toIso8601String(),
            'duration_seconds' => $entry->elapsedSeconds(),
            'duration_label' => TimeEntry::formatSeconds($entry->elapsedSeconds()),
            'is_running' => $entry->isRunning(),
            'stop_url' => route('time-tracker.stop', $entry),
            'delete_url' => route('time-tracker.destroy', $entry),
        ];
    }
}
