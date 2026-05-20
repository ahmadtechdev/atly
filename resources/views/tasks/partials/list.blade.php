@use(App\Enums\TaskStatus)

<div id="tasks-list" class="divide-y divide-atly-border">
    @forelse ($tasks as $task)
        <div
            class="task-row group flex w-full items-center gap-2 px-3 py-2.5 transition sm:gap-3 sm:px-4 sm:py-3"
            data-task-id="{{ $task->id }}"
        >
            <x-dashboard.task-status-action :task="$task" size="sm" />

            <x-dashboard.user-avatar :user="$task->user" size="sm" :title="$task->user->name" />

            <div
                data-task-select
                role="button"
                tabindex="0"
                class="task-item grid min-w-0 flex-1 cursor-pointer items-center gap-x-2 text-left sm:gap-x-3 grid-cols-[6px_minmax(0,1fr)_auto_1.25rem] sm:grid-cols-[6px_minmax(0,1fr)_5.75rem_4.5rem_2.75rem_1.25rem]"
            >
                <span class="size-1.5 shrink-0 self-center rounded-full sm:size-2 {{ $task->priority->dotClass() }}" aria-hidden="true"></span>

                <span class="flex min-w-0 flex-col gap-0.5 self-center">
                    <span
                        data-task-title
                        @class([
                            'min-w-0 truncate text-sm font-medium sm:text-base',
                            'text-atly-ink-soft line-through' => $task->status === TaskStatus::Completed,
                            'text-atly-ink group-hover:text-atly-accent-strong' => $task->status !== TaskStatus::Completed,
                        ])
                    >{{ $task->title }}</span>

                    <span
                        data-inline-attacher
                        data-update-url="{{ route('tasks.update-project', $task) }}"
                        data-search-url="{{ route('projects.search') }}"
                        data-field-name="project_id"
                        data-entity-label="project"
                        @if ($task->project)
                            data-current-id="{{ $task->project->id }}"
                            data-current-label="{{ $task->project->name }}"
                            data-current-color="{{ $task->project->color }}"
                            data-current-href="{{ route('projects.show', $task->project) }}"
                        @endif
                    ></span>
                </span>

                <span class="flex min-w-0 items-center justify-end gap-1.5 self-center sm:contents">
                    <x-dashboard.task-badge
                        type="status"
                        :value="$task->status->value"
                        data-task-status-badge
                        class="whitespace-nowrap sm:flex sm:w-full sm:justify-center"
                    />
                    <x-dashboard.task-badge
                        type="priority"
                        :value="$task->priority->value"
                        class="whitespace-nowrap sm:flex sm:w-full sm:justify-center"
                    />
                </span>

                <span
                    @class([
                        'hidden self-center text-xs tabular-nums sm:block sm:text-right',
                        'text-rose-600 dark:text-rose-300' => $task->due_date && $task->isOverdue(),
                        'text-atly-ink-soft' => ! $task->due_date || ! $task->isOverdue(),
                    ])
                >{{ $task->due_date?->format('M j') }}</span>

                <svg class="size-4 shrink-0 self-center justify-self-end text-atly-ink-soft transition group-hover:translate-x-0.5 group-hover:text-atly-ink sm:size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                </svg>
            </div>
        </div>
    @empty
        <div class="px-6 py-16 text-center">
            <p class="text-atly-ink-soft">No tasks match your filters.</p>
            <button type="button" data-open-task-modal class="mt-3 text-sm font-semibold text-atly-ink underline">Create a task</button>
        </div>
    @endforelse
</div>

{{ $tasks->links('pagination.atly') }}
