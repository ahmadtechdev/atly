@auth
    @php
        $workspaceOptions = \App\Models\Workspace::query()
            ->where('user_id', auth()->id())
            ->orderBy('name')
            ->get(['id', 'name', 'color']);
        $colors = ['sky', 'emerald', 'amber', 'rose', 'violet', 'fuchsia'];
    @endphp

    <div id="project-quick-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div data-close-project-modal class="absolute inset-0 bg-atly-ink/50 backdrop-blur-sm"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-lg rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly-lg sm:p-8" role="dialog" aria-modal="true" aria-labelledby="project-modal-title">
                <div class="mb-6 flex items-center justify-between">
                    <h2 id="project-modal-title" class="font-display text-xl font-bold text-atly-ink">New project</h2>
                    <button type="button" data-close-project-modal class="rounded-lg p-2 text-atly-ink-soft hover:bg-atly-muted hover:text-atly-ink" aria-label="Close">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form id="project-quick-form" method="POST" action="{{ route('projects.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="modal" value="1">

                    <div>
                        <label for="project-quick-name" class="mb-1.5 block text-sm font-medium text-atly-ink">Name</label>
                        <input
                            id="project-quick-name"
                            type="text"
                            name="name"
                            required
                            placeholder="e.g. Marketing site redesign"
                            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        />
                    </div>

                    <div>
                        <label for="project-quick-description" class="mb-1.5 block text-sm font-medium text-atly-ink">Description (optional)</label>
                        <textarea
                            id="project-quick-description"
                            name="description"
                            rows="3"
                            placeholder="What is this project about?"
                            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        ></textarea>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-atly-ink">Workspace (optional)</label>
                            <div
                                data-searchable-picker
                                data-search-url="{{ route('workspaces.search') }}"
                                data-name="workspace_id"
                                data-empty-label="No workspace"
                                data-placeholder="No workspace"
                            ></div>
                            <p class="mt-1 text-xs text-atly-ink-soft">Optional — leave empty for a standalone project.</p>
                        </div>

                        <div>
                            <span class="mb-1.5 block text-sm font-medium text-atly-ink">Color</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($colors as $color)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked($loop->first)>
                                        <span class="block size-7 rounded-full ring-2 ring-transparent transition peer-checked:ring-atly-accent peer-checked:ring-offset-2 peer-checked:ring-offset-atly-card bg-{{ $color }}-500"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div id="project-modal-errors" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                    <div class="flex flex-wrap gap-3">
                        <x-landing.button type="submit">Create project</x-landing.button>
                        <x-landing.button type="button" variant="secondary" data-close-project-modal>Cancel</x-landing.button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Suppress unused warning --}}
    @php $workspaceOptions; @endphp
@endauth
