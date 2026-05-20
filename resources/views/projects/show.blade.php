<x-layouts.dashboard :title="$project->name">
    <div class="space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <nav class="text-xs text-atly-ink-soft">
                    <a href="{{ route('projects.index') }}" class="hover:text-atly-ink">Projects</a>
                    @if ($project->workspace)
                        <span class="mx-1">/</span>
                        <a href="{{ route('workspaces.show', $project->workspace) }}" class="hover:text-atly-ink">{{ $project->workspace->name }}</a>
                    @endif
                </nav>
                <h2 class="mt-1 font-display text-2xl font-bold text-atly-ink">{{ $project->name }}</h2>
                @if ($project->description)
                    <p class="mt-1 max-w-2xl text-sm text-atly-ink-soft">{{ $project->description }}</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center gap-2 rounded-xl border border-atly-border bg-atly-card px-4 py-2 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50">Edit</a>
                <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="inline-flex items-center gap-2 rounded-xl bg-atly-contrast-bg px-4 py-2 text-sm font-semibold text-atly-contrast-fg">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192" /></svg>
                    View tasks ({{ $project->tasks_count }})
                </a>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
