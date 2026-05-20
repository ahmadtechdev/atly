<x-layouts.dashboard title="Projects">
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-atly-ink-soft">{{ $projects->total() }} {{ $projects->total() === 1 ? 'project' : 'projects' }}</p>
            <button
                type="button"
                data-open-project-modal
                class="inline-flex items-center gap-2 rounded-xl bg-atly-contrast-bg px-4 py-2.5 text-sm font-semibold text-atly-contrast-fg shadow-sm transition hover:scale-[1.02]"
            >
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add project
            </button>
        </div>

        <div class="rounded-atly-lg border border-atly-border bg-atly-card p-4 shadow-atly sm:p-5">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-atly-ink-soft" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    id="project-search"
                    type="search"
                    value="{{ request('search') }}"
                    placeholder="Search projects..."
                    autocomplete="off"
                    class="w-full rounded-xl border border-atly-border bg-atly-surface py-2.5 pl-10 pr-4 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                />
            </div>
        </div>

        <div id="projects-list-wrapper" class="space-y-3">
            @include('projects.partials.list', ['projects' => $projects])
        </div>
    </div>
</x-layouts.dashboard>
