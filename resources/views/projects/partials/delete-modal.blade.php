@props(['project', 'totalTasks' => 0])

<div id="project-delete-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
    <div data-close-project-delete class="absolute inset-0 bg-atly-ink/50 backdrop-blur-sm"></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-md rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly-lg" role="dialog" aria-modal="true" aria-labelledby="project-delete-title">
            <div class="flex items-start gap-3">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:text-rose-300">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                </span>
                <div class="min-w-0">
                    <h2 id="project-delete-title" class="font-display text-lg font-bold text-atly-ink">Delete "{{ $project->name }}"?</h2>
                    <p class="mt-1 text-sm text-atly-ink-soft">
                        @if ($totalTasks > 0)
                            This project has {{ $totalTasks }} {{ $totalTasks === 1 ? 'task' : 'tasks' }}. What should happen to them?
                        @else
                            This action can't be undone.
                        @endif
                    </p>
                </div>
            </div>

            <form id="project-delete-form" method="POST" action="{{ route('projects.destroy', $project) }}" class="mt-5 space-y-3">
                @csrf
                @method('DELETE')

                @if ($totalTasks > 0)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-atly-border bg-atly-surface p-3 transition hover:border-atly-accent has-[:checked]:border-atly-accent has-[:checked]:bg-atly-muted/30">
                        <input type="radio" name="task_action" value="unassign" class="mt-0.5 size-4 text-atly-accent" checked>
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-atly-ink">Keep tasks (recommended)</span>
                            <span class="mt-0.5 block text-xs text-atly-ink-soft">Tasks stay, just unassigned from any project.</span>
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-atly-border bg-atly-surface p-3 transition hover:border-rose-300 has-[:checked]:border-rose-400 has-[:checked]:bg-rose-50 dark:has-[:checked]:bg-rose-950/20">
                        <input type="radio" name="task_action" value="delete" class="mt-0.5 size-4 text-rose-600">
                        <span class="min-w-0">
                            <span class="block text-sm font-semibold text-rose-700 dark:text-rose-300">Delete all tasks</span>
                            <span class="mt-0.5 block text-xs text-atly-ink-soft">Permanently removes the {{ $totalTasks }} {{ $totalTasks === 1 ? 'task' : 'tasks' }} in this project too.</span>
                        </span>
                    </label>
                @else
                    <input type="hidden" name="task_action" value="unassign">
                @endif

                <div class="mt-2 flex flex-wrap justify-end gap-2">
                    <button type="button" data-close-project-delete class="rounded-xl border border-atly-border bg-atly-card px-4 py-2 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50">Cancel</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">Delete project</button>
                </div>
            </form>
        </div>
    </div>
</div>
