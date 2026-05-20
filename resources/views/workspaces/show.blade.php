@php
    $viewer = auth()->user();
    $canManage = $viewer && $workspace->canManage($viewer);
@endphp

<x-layouts.dashboard :title="$workspace->name">
    <div class="space-y-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <nav class="text-xs text-atly-ink-soft">
                    <a href="{{ route('workspaces.index') }}" class="hover:text-atly-ink">Workspaces</a>
                </nav>
                <h2 class="mt-1 flex items-center gap-2 font-display text-2xl font-bold text-atly-ink">
                    <span @class([
                        'size-3 rounded-full',
                        'bg-'.$workspace->color.'-500' => $workspace->color,
                        'bg-atly-ink-soft/40' => ! $workspace->color,
                    ])></span>
                    {{ $workspace->name }}
                </h2>
                @if ($workspace->description)
                    <p class="mt-1 max-w-2xl text-sm text-atly-ink-soft">{{ $workspace->description }}</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($canManage)
                    <a href="{{ route('workspaces.edit', $workspace) }}" class="inline-flex items-center gap-2 rounded-xl border border-atly-border bg-atly-card px-4 py-2 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50">Edit</a>
                    <button
                        type="button"
                        data-open-invite-modal
                        data-invitable-type="workspace"
                        data-invitable-id="{{ $workspace->id }}"
                        data-invitable-label="{{ $workspace->name }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-atly-border bg-atly-card px-4 py-2 text-sm font-semibold text-atly-ink hover:bg-atly-muted/50"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                        Invite
                    </button>
                @endif
                <a href="{{ route('tasks.index', ['workspace_id' => $workspace->id]) }}" class="inline-flex items-center gap-2 rounded-xl bg-atly-contrast-bg px-4 py-2 text-sm font-semibold text-atly-contrast-fg">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192" /></svg>
                    View tasks ({{ $workspace->tasks_count }})
                </a>
            </div>
        </div>

        <div class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-display text-sm font-semibold uppercase tracking-wide text-atly-ink-soft">Projects ({{ $workspace->projects_count }})</h3>
                @if ($canManage)
                    <button
                        type="button"
                        data-open-project-modal
                        data-prefill-workspace-id="{{ $workspace->id }}"
                        data-prefill-workspace-label="{{ $workspace->name }}"
                        data-prefill-workspace-color="{{ $workspace->color }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-atly-border bg-atly-surface px-3 py-1.5 text-xs font-semibold text-atly-ink hover:bg-atly-muted/50"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Add project
                    </button>
                @endif
            </div>

            @if ($workspace->projects->isEmpty())
                <p class="px-2 py-6 text-center text-sm text-atly-ink-soft">No projects in this workspace yet.</p>
            @else
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($workspace->projects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="group rounded-xl border border-atly-border bg-atly-surface p-4 transition hover:-translate-y-0.5 hover:shadow-atly">
                            <div class="flex items-center gap-2">
                                <span @class([
                                    'size-2.5 shrink-0 rounded-full',
                                    'bg-'.$project->color.'-500' => $project->color,
                                    'bg-atly-ink-soft/40' => ! $project->color,
                                ])></span>
                                <p class="truncate font-medium text-atly-ink group-hover:text-atly-accent-strong">{{ $project->name }}</p>
                            </div>
                            <p class="mt-1 text-xs text-atly-ink-soft">{{ $project->tasks_count }} {{ $project->tasks_count === 1 ? 'task' : 'tasks' }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <x-dashboard.members
            :owner="$workspace->user"
            :members="$workspace->members"
            :canInvite="$canManage"
            :inviteTarget="['type' => 'workspace', 'id' => $workspace->id, 'label' => $workspace->name]"
        />
    </div>
</x-layouts.dashboard>
