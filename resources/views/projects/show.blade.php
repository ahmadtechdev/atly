@use(App\Enums\TaskStatus)
@use(App\Models\TimeEntry)

<x-layouts.dashboard :title="$project->name">
    <div class="space-y-5">
        @if ($errors->has('project'))
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-200">
                {{ $errors->first('project') }}
            </div>
        @endif

        <div class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <nav class="flex flex-wrap items-center gap-1 text-xs text-atly-ink-soft">
                        <a href="{{ route('projects.index') }}" class="hover:text-atly-ink">Projects</a>
                        @if ($project->workspace)
                            <span>/</span>
                            <a href="{{ route('workspaces.show', $project->workspace) }}" class="hover:text-atly-ink">{{ $project->workspace->name }}</a>
                        @endif
                    </nav>

                    <div class="mt-1 flex flex-wrap items-center gap-2">
                        <h2 @class([
                            'min-w-0 truncate font-display text-2xl font-bold',
                            'text-atly-ink' => ! $project->isCompleted(),
                            'text-atly-ink-soft line-through' => $project->isCompleted(),
                        ])>{{ $project->name }}</h2>
                        @if ($project->status)
                            <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold {{ $project->status->colorClass() }}">{{ $project->status->label() }}</span>
                        @endif
                    </div>

                    @if ($project->description)
                        <p class="mt-1.5 max-w-2xl text-sm text-atly-ink-soft">{{ $project->description }}</p>
                    @endif

                    @php
                        $total = $project->tasks_count ?? 0;
                        $done = $project->completed_tasks_count ?? 0;
                        $pct = $total > 0 ? (int) round(($done / $total) * 100) : 0;
                    @endphp

                    <div class="mt-4 max-w-md">
                        <div class="flex items-center justify-between text-xs text-atly-ink-soft">
                            <span>{{ $done }} / {{ $total }} {{ $total === 1 ? 'task' : 'tasks' }} completed</span>
                            <span class="font-semibold text-atly-ink">{{ $pct }}%</span>
                        </div>
                        <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-atly-muted">
                            <div class="h-full bg-emerald-500 transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-atly-border bg-atly-card px-3 py-2 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                        Edit
                    </a>

                    @if ($project->isCompleted())
                        <form method="POST" action="{{ route('projects.reopen', $project) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl border border-atly-border bg-atly-card px-3 py-2 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                Reopen
                            </button>
                        </form>
                    @else
                        @php
                            $canComplete = ! $project->hasOutstandingTasks() && $total > 0;
                        @endphp
                        <form method="POST" action="{{ route('projects.complete', $project) }}" @if (! $canComplete) onsubmit="event.preventDefault();" @endif>
                            @csrf
                            @method('PATCH')
                            <button
                                type="submit"
                                @disabled(! $canComplete)
                                @class([
                                    'inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold transition',
                                    'bg-emerald-600 text-white hover:bg-emerald-700' => $canComplete,
                                    'cursor-not-allowed border border-atly-border bg-atly-muted/30 text-atly-ink-soft/60' => ! $canComplete,
                                ])
                                title="{{ $canComplete ? 'Mark this project as complete' : ($total === 0 ? 'Add and complete tasks first' : 'Complete all tasks first') }}"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5" /></svg>
                                Mark complete
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-atly-contrast-bg px-3 py-2 text-sm font-semibold text-atly-contrast-fg">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192" /></svg>
                        View all ({{ $total }})
                    </a>

                    <button
                        type="button"
                        data-open-project-delete
                        data-tasks-count="{{ $total }}"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 px-3 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <section class="rounded-atly-lg border border-atly-border bg-atly-card shadow-atly">
            <header class="flex flex-wrap items-center justify-between gap-2 border-b border-atly-border px-5 py-4">
                <h3 class="font-display text-base font-bold text-atly-ink">Tasks</h3>
                <div class="flex items-center gap-2">
                    @if (! $project->isCompleted())
                        <button
                            type="button"
                            data-open-task-modal
                            data-prefill-project-id="{{ $project->id }}"
                            data-prefill-project-label="{{ $project->name }}"
                            data-prefill-project-color="{{ $project->color }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-atly-border bg-atly-surface px-3 py-1.5 text-xs font-semibold text-atly-ink hover:bg-atly-muted/50"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            Add task
                        </button>
                    @endif
                    <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="text-xs font-semibold text-atly-ink-soft hover:text-atly-ink">View all &rarr;</a>
                </div>
            </header>

            @if ($tasks->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="text-sm text-atly-ink-soft">No tasks in this project yet.</p>
                    @if (! $project->isCompleted())
                        <button
                            type="button"
                            data-open-task-modal
                            data-prefill-project-id="{{ $project->id }}"
                            data-prefill-project-label="{{ $project->name }}"
                            data-prefill-project-color="{{ $project->color }}"
                            class="mt-3 text-sm font-semibold text-atly-ink underline"
                        >Add a task</button>
                    @endif
                </div>
            @else
                <ul class="divide-y divide-atly-border">
                    @foreach ($tasks as $task)
                        @php
                            $totalSeconds = $task->totalTrackedSeconds();
                            $isCompleted = $task->status === TaskStatus::Completed;
                        @endphp
                        <li>
                            <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}#task-{{ $task->id }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-atly-muted/30">
                                <span @class([
                                    'flex size-5 shrink-0 items-center justify-center rounded-full border-2',
                                    'border-emerald-500 bg-emerald-500 text-white' => $isCompleted,
                                    'border-atly-border' => ! $isCompleted,
                                ])>
                                    @if ($isCompleted)
                                        <svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5" /></svg>
                                    @endif
                                </span>

                                <span class="size-2 shrink-0 rounded-full {{ $task->priority->dotClass() }}" title="{{ $task->priority->label() }} priority"></span>

                                <span @class([
                                    'min-w-0 flex-1 truncate text-sm',
                                    'text-atly-ink-soft line-through' => $isCompleted,
                                    'text-atly-ink' => ! $isCompleted,
                                ])>{{ $task->title }}</span>

                                <span class="hidden text-xs sm:inline-flex rounded-md px-2 py-0.5 font-semibold {{ $task->status->colorClass() }}">{{ $task->status->label() }}</span>

                                @if ($totalSeconds > 0)
                                    <span class="hidden items-center gap-1 text-xs tabular-nums text-atly-ink-soft sm:inline-flex">
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        {{ TimeEntry::formatSeconds($totalSeconds) }}
                                    </span>
                                @endif

                                @if ($task->due_date)
                                    <span @class([
                                        'hidden text-xs tabular-nums sm:inline',
                                        'text-rose-600 dark:text-rose-300' => $task->isOverdue(),
                                        'text-atly-ink-soft' => ! $task->isOverdue(),
                                    ])>{{ $task->due_date->format('M j') }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>

                @if ($total > $tasks->count())
                    <div class="border-t border-atly-border px-5 py-3 text-center">
                        <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="text-xs font-semibold text-atly-ink hover:text-atly-accent-strong">View all {{ $total }} tasks &rarr;</a>
                    </div>
                @endif
            @endif
        </section>
    </div>

    @include('projects.partials.delete-modal', ['project' => $project, 'totalTasks' => $total])
</x-layouts.dashboard>
