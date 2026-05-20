@if ($projects->isEmpty())
    <div class="rounded-atly-lg border border-dashed border-atly-border bg-atly-card px-6 py-16 text-center">
        <p class="text-atly-ink-soft">You don't have any projects yet.</p>
        <button type="button" data-open-project-modal class="mt-3 text-sm font-semibold text-atly-ink underline">Create your first project</button>
    </div>
@else
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($projects as $project)
            <article class="group relative flex flex-col gap-3 overflow-hidden rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly transition hover:-translate-y-0.5 hover:shadow-atly-lg">
                <div @class([
                    'absolute inset-x-0 top-0 h-1',
                    'bg-'.$project->color.'-500' => $project->color,
                    'bg-atly-muted' => ! $project->color,
                ])></div>

                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('projects.show', $project) }}" class="min-w-0 flex-1">
                        <h3 class="truncate font-display text-base font-bold text-atly-ink group-hover:text-atly-accent-strong">{{ $project->name }}</h3>
                        @if ($project->description)
                            <p class="mt-1.5 line-clamp-2 text-xs text-atly-ink-soft">{{ $project->description }}</p>
                        @endif
                    </a>

                    <a href="{{ route('projects.edit', $project) }}" class="rounded-lg p-1.5 text-atly-ink-soft transition hover:bg-atly-muted hover:text-atly-ink" aria-label="Edit project" title="Edit project">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                    </a>
                </div>

                <div
                    data-inline-attacher
                    data-update-url="{{ route('projects.update-workspace', $project) }}"
                    data-search-url="{{ route('workspaces.search') }}"
                    data-field-name="workspace_id"
                    data-entity-label="workspace"
                    @if ($project->workspace)
                        data-current-id="{{ $project->workspace->id }}"
                        data-current-label="{{ $project->workspace->name }}"
                        data-current-color="{{ $project->workspace->color }}"
                        data-current-href="{{ route('workspaces.show', $project->workspace) }}"
                    @endif
                ></div>

                <div class="mt-auto flex flex-wrap items-center gap-3 text-xs text-atly-ink-soft">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-atly-muted/40 px-2.5 py-1">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192" /></svg>
                        {{ $project->tasks_count }} {{ $project->tasks_count === 1 ? 'task' : 'tasks' }}
                    </span>
                    <a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="text-atly-ink-soft hover:text-atly-ink">View tasks &rarr;</a>
                </div>
            </article>
        @endforeach
    </div>

    @if ($projects->hasPages())
        <div class="rounded-atly-lg border border-atly-border bg-atly-card">
            {{ $projects->links('pagination.atly') }}
        </div>
    @endif
@endif
