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
            <div class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-atly-ink">Calendar</h2>
                        <p class="text-sm text-atly-ink-soft">{{ $calendarMonth->format('F Y') }}</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('dashboard', ['month' => $calendarMonth->copy()->subMonth()->format('Y-m')]) }}" class="rounded-lg border border-atly-border px-2 py-1 text-sm text-atly-ink hover:bg-atly-muted">←</a>
                        <a href="{{ route('dashboard', ['month' => $calendarMonth->copy()->addMonth()->format('Y-m')]) }}" class="rounded-lg border border-atly-border px-2 py-1 text-sm text-atly-ink hover:bg-atly-muted">→</a>
                    </div>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-atly-ink-soft">
                    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                        <div class="py-1">{{ $day }}</div>
                    @endforeach
                </div>
                @foreach ($calendarWeeks as $week)
                    <div class="mt-1 grid grid-cols-7 gap-1">
                        @foreach ($week as $day)
                            <div @class([
                                'min-h-14 rounded-lg border p-1 text-left',
                                'border-atly-border bg-atly-surface/50' => $day['in_month'],
                                'border-transparent opacity-40' => ! $day['in_month'],
                                'ring-2 ring-atly-accent' => $day['is_today'],
                            ])>
                                <span @class(['text-xs font-medium', 'text-atly-ink' => $day['in_month'], 'text-atly-ink-soft' => ! $day['in_month']])>{{ $day['date']->day }}</span>
                                @if ($day['tasks']->isNotEmpty())
                                    <div class="mt-1 space-y-0.5">
                                        @foreach ($day['tasks']->take(2) as $task)
                                            <p class="truncate rounded bg-atly-accent/25 px-1 text-[10px] text-atly-ink" title="{{ $task->title }}">{{ $task->title }}</p>
                                        @endforeach
                                        @if ($day['tasks']->count() > 2)
                                            <p class="text-[10px] text-atly-ink-soft">+{{ $day['tasks']->count() - 2 }} more</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
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
