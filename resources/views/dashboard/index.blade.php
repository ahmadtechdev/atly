<x-layouts.dashboard title="Dashboard">
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <x-dashboard.stat-card label="Total tasks" :value="$stats['total']" />
            <x-dashboard.stat-card label="Completed" :value="$stats['completed']" :hint="$completionRate.'% completion rate'" />
            <x-dashboard.stat-card label="In progress" :value="$stats['in_progress']" />
            <x-dashboard.stat-card label="Pending" :value="$stats['pending']" />
            <x-dashboard.stat-card label="Due today" :value="$stats['due_today']" :accent="true" />
            <x-dashboard.stat-card label="Overdue" :value="$stats['overdue']" :hint="$stats['overdue'] > 0 ? 'Needs attention' : 'You are on track'" />
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly lg:col-span-2">
                <h2 class="font-display text-lg font-semibold text-atly-ink">Weekly activity</h2>
                <p class="text-sm text-atly-ink-soft">Tasks created vs completed (last 7 days)</p>
                <div class="mt-4 h-64">
                    <canvas id="activity-chart" data-chart='@json($weeklyActivity)'></canvas>
                </div>
            </div>

            <div class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly">
                <h2 class="font-display text-lg font-semibold text-atly-ink">Completion</h2>
                <p class="text-sm text-atly-ink-soft">Overall progress</p>
                <div class="mt-6 flex flex-col items-center justify-center">
                    <div class="relative flex size-36 items-center justify-center rounded-full" style="background: conic-gradient(var(--color-atly-accent) {{ $completionRate }}%, var(--color-atly-muted) 0);">
                        <div class="flex size-28 flex-col items-center justify-center rounded-full bg-atly-card">
                            <span class="font-display text-3xl font-bold text-atly-ink">{{ $completionRate }}%</span>
                            <span class="text-xs text-atly-ink-soft">done</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly">
                <h2 class="font-display text-lg font-semibold text-atly-ink">By status</h2>
                <div class="mt-4 h-56">
                    <canvas id="status-chart" data-chart='@json($statusChart)'></canvas>
                </div>
            </div>
            <div class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly">
                <h2 class="font-display text-lg font-semibold text-atly-ink">By priority</h2>
                <div class="mt-4 h-56">
                    <canvas id="priority-chart" data-chart='@json($priorityChart)'></canvas>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <div
                id="dashboard-calendar"
                class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly transition"
                data-calendar
                data-calendar-url="{{ route('dashboard') }}"
            >
                @include('dashboard.partials.calendar', ['calendarMonth' => $calendarMonth, 'calendarWeeks' => $calendarWeeks])
            </div>

            <div class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-display text-lg font-semibold text-atly-ink">Upcoming tasks</h2>
                    <a href="{{ route('tasks.index') }}" class="text-sm font-medium text-atly-accent-strong hover:text-atly-ink">View all</a>
                </div>
                @forelse ($upcomingTasks as $task)
                    <a href="{{ route('tasks.edit', $task) }}" class="mb-2 flex items-start justify-between gap-3 rounded-xl border border-atly-border px-4 py-3 transition hover:border-atly-accent hover:bg-atly-muted/30">
                        <div class="min-w-0">
                            <p class="truncate font-medium text-atly-ink">{{ $task->title }}</p>
                            <p class="mt-0.5 text-xs text-atly-ink-soft">Due {{ $task->due_date->format('M j, Y') }}</p>
                        </div>
                        <x-dashboard.task-badge type="priority" :value="$task->priority->value" />
                    </a>
                @empty
                    <p class="rounded-xl border border-dashed border-atly-border px-4 py-8 text-center text-sm text-atly-ink-soft">No upcoming tasks. <a href="{{ route('tasks.create') }}" class="font-medium text-atly-ink underline">Create one</a></p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.dashboard>
