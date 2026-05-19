<x-layouts.dashboard title="Tasks">
    <x-slot:actions>
        <x-landing.button :href="route('tasks.create')">New task</x-landing.button>
    </x-slot:actions>

    <div class="mb-4 sm:hidden">
        <x-landing.button :href="route('tasks.create')" class="w-full">New task</x-landing.button>
    </div>

    <div class="space-y-6">
        <div class="rounded-atly-lg border border-atly-border bg-atly-card p-4 shadow-atly sm:p-5">
            <form method="GET" action="{{ route('tasks.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="search" class="mb-1.5 block text-sm font-medium text-atly-ink">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        placeholder="Search tasks..."
                        class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-2.5 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                    />
                </div>
                <div class="grid flex-1 grid-cols-2 gap-3 sm:max-w-md">
                    <div>
                        <label for="status" class="mb-1.5 block text-sm font-medium text-atly-ink">Status</label>
                        <select id="status" name="status" class="w-full rounded-xl border border-atly-border bg-atly-surface px-3 py-2.5 text-sm">
                            <option value="">All</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="priority" class="mb-1.5 block text-sm font-medium text-atly-ink">Priority</label>
                        <select id="priority" name="priority" class="w-full rounded-xl border border-atly-border bg-atly-surface px-3 py-2.5 text-sm">
                            <option value="">All</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->value }}" @selected(request('priority') === $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <x-landing.button type="submit" variant="secondary" class="!py-2.5">Filter</x-landing.button>
            </form>
        </div>

        <div class="overflow-hidden rounded-atly-lg border border-atly-border bg-atly-card shadow-atly">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-atly-border bg-atly-muted/40 text-atly-ink-soft">
                        <tr>
                            <th class="px-4 py-3 font-medium">Task</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Priority</th>
                            <th class="px-4 py-3 font-medium">Due</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-atly-border">
                        @forelse ($tasks as $task)
                            <tr class="hover:bg-atly-muted/20">
                                <td class="px-4 py-4">
                                    <p class="font-medium text-atly-ink">{{ $task->title }}</p>
                                    @if ($task->description)
                                        <p class="mt-0.5 line-clamp-1 text-xs text-atly-ink-soft">{{ $task->description }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <x-dashboard.task-badge type="status" :value="$task->status->value" />
                                </td>
                                <td class="px-4 py-4">
                                    <x-dashboard.task-badge type="priority" :value="$task->priority->value" />
                                </td>
                                <td class="px-4 py-4 text-atly-ink-soft">
                                    @if ($task->due_date)
                                        <span @class(['text-rose-600 dark:text-rose-300' => $task->isOverdue()])>
                                            {{ $task->due_date->format('M j, Y') }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('tasks.edit', $task) }}" class="rounded-lg px-3 py-1.5 text-xs font-medium text-atly-ink hover:bg-atly-muted">Edit</a>
                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-atly-ink-soft">
                                    No tasks yet.
                                    <a href="{{ route('tasks.create') }}" class="font-medium text-atly-ink underline">Create your first task</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($tasks->hasPages())
                <div class="border-t border-atly-border px-4 py-3">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
