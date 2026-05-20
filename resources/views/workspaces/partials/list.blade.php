@if ($workspaces->isEmpty())
    <div class="rounded-atly-lg border border-dashed border-atly-border bg-atly-card px-6 py-16 text-center">
        <p class="text-atly-ink-soft">You don't have any workspaces yet.</p>
        <button type="button" data-open-workspace-modal class="mt-3 text-sm font-semibold text-atly-ink underline">Create your first workspace</button>
    </div>
@else
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($workspaces as $workspace)
            <article class="group relative flex flex-col gap-3 overflow-hidden rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly transition hover:-translate-y-0.5 hover:shadow-atly-lg">
                <div @class([
                    'absolute inset-x-0 top-0 h-1',
                    'bg-'.$workspace->color.'-500' => $workspace->color,
                    'bg-atly-muted' => ! $workspace->color,
                ])></div>

                <div class="flex items-start justify-between gap-3">
                    <a href="{{ route('workspaces.show', $workspace) }}" class="min-w-0 flex-1">
                        <h3 class="truncate font-display text-base font-bold text-atly-ink group-hover:text-atly-accent-strong">{{ $workspace->name }}</h3>
                        @if ($workspace->description)
                            <p class="mt-1 line-clamp-2 text-xs text-atly-ink-soft">{{ $workspace->description }}</p>
                        @endif
                    </a>
                    <a href="{{ route('workspaces.edit', $workspace) }}" class="rounded-lg p-1.5 text-atly-ink-soft transition hover:bg-atly-muted hover:text-atly-ink" aria-label="Edit workspace" title="Edit workspace">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" /></svg>
                    </a>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-xs text-atly-ink-soft">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-atly-muted/40 px-2.5 py-1">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75" /></svg>
                        {{ $workspace->projects_count }} {{ $workspace->projects_count === 1 ? 'project' : 'projects' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-atly-muted/40 px-2.5 py-1">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192" /></svg>
                        {{ $workspace->tasks_count }} {{ $workspace->tasks_count === 1 ? 'task' : 'tasks' }}
                    </span>
                </div>

                @if ($workspace->projects->isNotEmpty())
                    <ul class="mt-1 space-y-1 border-t border-atly-border pt-3 text-sm">
                        @foreach ($workspace->projects as $project)
                            <li>
                                <a href="{{ route('projects.show', $project) }}" class="flex items-center justify-between gap-2 rounded-lg px-2 py-1 text-atly-ink-soft transition hover:bg-atly-muted/40 hover:text-atly-ink">
                                    <span class="flex min-w-0 items-center gap-2">
                                        <span @class([
                                            'size-2 shrink-0 rounded-full',
                                            'bg-'.$project->color.'-500' => $project->color,
                                            'bg-atly-ink-soft/40' => ! $project->color,
                                        ])></span>
                                        <span class="truncate">{{ $project->name }}</span>
                                    </span>
                                    <span class="shrink-0 text-xs tabular-nums">{{ $project->tasks_count }}</span>
                                </a>
                            </li>
                        @endforeach
                        @if ($workspace->projects_count > $workspace->projects->count())
                            <li class="px-2 text-xs text-atly-ink-soft">+ {{ $workspace->projects_count - $workspace->projects->count() }} more</li>
                        @endif
                    </ul>
                @endif
            </article>
        @endforeach
    </div>

    @if ($workspaces->hasPages())
        <div class="rounded-atly-lg border border-atly-border bg-atly-card">
            {{ $workspaces->links('pagination.atly') }}
        </div>
    @endif
@endif
