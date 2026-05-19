<x-layouts.dashboard title="Tasks">
    <div class="grid gap-6 xl:grid-cols-[1fr_minmax(0,22rem)]">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-atly-ink-soft">{{ $tasks->total() }} {{ $tasks->total() === 1 ? 'task' : 'tasks' }}</p>
                <button
                    type="button"
                    data-open-task-modal
                    class="inline-flex items-center gap-2 rounded-xl bg-atly-contrast-bg px-4 py-2.5 text-sm font-semibold text-atly-contrast-fg shadow-sm transition hover:scale-[1.02]"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add task
                </button>
            </div>

            <div class="rounded-atly-lg border border-atly-border bg-atly-card p-4 shadow-atly sm:p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-atly-ink-soft" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input
                            id="task-search"
                            type="search"
                            value="{{ request('search') }}"
                            placeholder="Search tasks instantly..."
                            autocomplete="off"
                            class="w-full rounded-xl border border-atly-border bg-atly-surface py-2.5 pl-10 pr-4 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:max-w-sm">
                        <select id="task-filter-status" class="rounded-xl border border-atly-border bg-atly-surface px-3 py-2.5 text-sm text-atly-ink">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                        <select id="task-filter-priority" class="rounded-xl border border-atly-border bg-atly-surface px-3 py-2.5 text-sm text-atly-ink">
                            <option value="">All priorities</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-atly-lg border border-atly-border bg-atly-card shadow-atly">
                <div id="tasks-list-wrapper">
                    @include('tasks.partials.list', ['tasks' => $tasks])
                </div>
            </div>
        </div>

        <aside id="task-detail-panel" class="hidden min-w-0 xl:block">
            <div class="sticky top-24 min-w-0 rounded-atly-lg border border-atly-border bg-atly-card shadow-atly">
                <div id="task-detail-empty" class="px-6 py-12 text-center">
                    <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-atly-muted/50 text-atly-ink-soft">
                        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c1.01-.049 1.959-.218 2.816-.608a48.114 48.114 0 0 0 3.487 0 2.916 2.916 0 0 0 2.166 1.607m-9.75 8.25h7.5m-7.5 3H12" />
                        </svg>
                    </div>
                    <p class="text-sm text-atly-ink-soft">Select a task to view details</p>
                </div>
                <div id="task-detail-content" class="hidden min-w-0 p-6"></div>
            </div>
        </aside>
    </div>

    <div id="task-detail-drawer" class="fixed inset-0 z-50 hidden xl:hidden" aria-hidden="true">
        <div data-close-task-detail class="absolute inset-0 bg-atly-ink/40 backdrop-blur-sm"></div>
        <div class="absolute bottom-0 left-0 right-0 max-h-[85vh] overflow-hidden rounded-t-2xl border border-atly-border bg-atly-card shadow-atly-lg">
            <div id="task-detail-drawer-content" class="min-w-0 overflow-hidden p-6"></div>
        </div>
    </div>

</x-layouts.dashboard>
