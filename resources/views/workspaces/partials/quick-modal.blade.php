@auth
    @php
        $colors = ['sky', 'emerald', 'amber', 'rose', 'violet', 'fuchsia'];
    @endphp

    <div id="workspace-quick-modal" class="fixed inset-0 z-50 hidden" aria-hidden="true">
        <div data-close-workspace-modal class="absolute inset-0 bg-atly-ink/50 backdrop-blur-sm"></div>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-lg rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly-lg sm:p-8" role="dialog" aria-modal="true" aria-labelledby="workspace-modal-title">
                <div class="mb-6 flex items-center justify-between">
                    <h2 id="workspace-modal-title" class="font-display text-xl font-bold text-atly-ink">New workspace</h2>
                    <button type="button" data-close-workspace-modal class="rounded-lg p-2 text-atly-ink-soft hover:bg-atly-muted hover:text-atly-ink" aria-label="Close">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form id="workspace-quick-form" method="POST" action="{{ route('workspaces.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="modal" value="1">

                    <div>
                        <label for="workspace-quick-name" class="mb-1.5 block text-sm font-medium text-atly-ink">Name</label>
                        <input
                            id="workspace-quick-name"
                            type="text"
                            name="name"
                            required
                            placeholder="e.g. Personal, Acme Inc."
                            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        />
                    </div>

                    <div>
                        <label for="workspace-quick-description" class="mb-1.5 block text-sm font-medium text-atly-ink">Description (optional)</label>
                        <textarea
                            id="workspace-quick-description"
                            name="description"
                            rows="3"
                            placeholder="What does this workspace contain?"
                            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        ></textarea>
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

                    <div id="workspace-modal-errors" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                    <div class="flex flex-wrap gap-3">
                        <x-landing.button type="submit">Create workspace</x-landing.button>
                        <x-landing.button type="button" variant="secondary" data-close-workspace-modal>Cancel</x-landing.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endauth
