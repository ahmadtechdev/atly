<div id="tasks-list" class="divide-y divide-atly-border">
    @forelse ($tasks as $task)
        <button
            type="button"
            data-task-id="{{ $task->id }}"
            class="task-item group flex w-full items-center gap-4 px-4 py-4 text-left transition hover:bg-atly-muted/30 sm:px-5"
        >
            <span class="size-2.5 shrink-0 rounded-full {{ $task->priority->dotClass() }}" aria-hidden="true"></span>
            <span class="min-w-0 flex-1">
                <span class="block truncate font-medium text-atly-ink group-hover:text-atly-accent-strong">{{ $task->title }}</span>
            </span>
            <span class="hidden shrink-0 sm:inline-flex">
                <x-dashboard.task-badge type="priority" :value="$task->priority->value" />
            </span>
            @if ($task->due_date)
                <span @class([
                    'hidden shrink-0 text-xs sm:block',
                    'text-rose-600 dark:text-rose-300' => $task->isOverdue(),
                    'text-atly-ink-soft' => ! $task->isOverdue(),
                ])>{{ $task->due_date->format('M j') }}</span>
            @endif
            <svg class="size-5 shrink-0 text-atly-ink-soft transition group-hover:translate-x-0.5 group-hover:text-atly-ink" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>
    @empty
        <div class="px-6 py-16 text-center">
            <p class="text-atly-ink-soft">No tasks match your filters.</p>
            <button type="button" data-open-task-modal class="mt-3 text-sm font-semibold text-atly-ink underline">Create a task</button>
        </div>
    @endforelse
</div>

@if ($tasks->hasPages())
    <div id="tasks-pagination" class="border-t border-atly-border px-4 py-3">
        {{ $tasks->links() }}
    </div>
@endif
