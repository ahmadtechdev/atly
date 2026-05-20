@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Project> $activeProjects */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Project> $completedProjects */
@endphp

@if ($activeProjects->isEmpty() && $completedProjects->isEmpty())
    <div class="rounded-atly-lg border border-dashed border-atly-border bg-atly-card px-6 py-16 text-center">
        <p class="text-atly-ink-soft">You don't have any projects yet.</p>
        <button type="button" data-open-project-modal class="mt-3 text-sm font-semibold text-atly-ink underline">Create your first project</button>
    </div>
@else
    @if ($activeProjects->isNotEmpty())
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($activeProjects as $project)
                @include('projects.partials.card', ['project' => $project])
            @endforeach
        </div>
    @else
        <div class="rounded-atly-lg border border-dashed border-atly-border bg-atly-card px-6 py-10 text-center">
            <p class="text-atly-ink-soft">No active projects.</p>
        </div>
    @endif

    @if ($completedProjects->isNotEmpty())
        <div class="mt-8 space-y-3">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5" /></svg>
                    Completed
                </span>
                <span class="text-xs text-atly-ink-soft">{{ $completedProjects->count() }} {{ $completedProjects->count() === 1 ? 'project' : 'projects' }}</span>
                <hr class="flex-1 border-atly-border">
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($completedProjects as $project)
                    @include('projects.partials.card', ['project' => $project, 'completed' => true])
                @endforeach
            </div>
        </div>
    @endif
@endif
