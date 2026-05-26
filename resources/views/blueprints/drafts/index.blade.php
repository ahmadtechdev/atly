@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $drafts */
@endphp

<x-layouts.dashboard title="Blueprint Drafts">
    <div class="space-y-6">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-2xl font-bold text-atly-ink">Blueprint Drafts</h2>
                <p class="mt-1 text-sm text-atly-ink-soft">Plans saved before they became real projects. Edit, send invitations, or finalize when you're ready.</p>
            </div>
            <x-landing.button :href="route('blueprint.index')">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.091Z" /></svg>
                New blueprint
            </x-landing.button>
        </header>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($drafts->isEmpty())
            <div class="rounded-atly-lg border border-dashed border-atly-border bg-atly-card p-10 text-center shadow-atly">
                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-atly-muted text-atly-ink-soft">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                </div>
                <h3 class="mt-3 font-display text-lg font-bold text-atly-ink">No drafts yet</h3>
                <p class="mx-auto mt-1 max-w-md text-sm text-atly-ink-soft">When you generate an AI Blueprint, you can save it as a draft from the review screen — or for team plans, drafts are created automatically while invitations are pending.</p>
                <a href="{{ route('blueprint.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-atly-contrast-bg px-4 py-2.5 text-sm font-semibold text-atly-contrast-fg shadow-atly transition hover:scale-[1.02]">
                    Generate a blueprint
                </a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($drafts as $draft)
                    @php
                        $accepted = $draft->acceptedMembersCount();
                        $pending = $draft->pendingMembersCount();
                        $totalEmailed = $draft->members->whereNotNull('email')->count();
                    @endphp
                    <article class="group relative flex flex-col rounded-atly border border-atly-border bg-atly-card p-5 shadow-atly transition hover:border-atly-accent/40">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="size-3 rounded-full bg-{{ $draft->color }}-500"></span>
                                <h3 class="font-display text-base font-bold text-atly-ink line-clamp-1">
                                    <a href="{{ route('blueprint.drafts.show', $draft) }}" class="hover:text-atly-accent">{{ $draft->name }}</a>
                                </h3>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $draft->status->colorClass() }}">
                                {{ $draft->status->label() }}
                            </span>
                        </div>

                        @if ($draft->description)
                            <p class="mt-2 text-xs text-atly-ink-soft line-clamp-2">{{ $draft->description }}</p>
                        @endif

                        <dl class="mt-4 grid grid-cols-2 gap-3 text-xs">
                            <div>
                                <dt class="font-semibold uppercase tracking-wide text-atly-ink-soft">Type</dt>
                                <dd class="mt-0.5 text-atly-ink">{{ $draft->isTeam() ? 'Team' : 'Individual' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold uppercase tracking-wide text-atly-ink-soft">Tasks</dt>
                                <dd class="mt-0.5 text-atly-ink">{{ count($draft->tasks ?? []) }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold uppercase tracking-wide text-atly-ink-soft">Workspace</dt>
                                <dd class="mt-0.5 text-atly-ink">{{ $draft->workspace?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold uppercase tracking-wide text-atly-ink-soft">Window</dt>
                                <dd class="mt-0.5 text-atly-ink">{{ $draft->start_date->format('M j') }} – {{ $draft->end_date->format('M j') }}</dd>
                            </div>
                        </dl>

                        @if ($draft->isTeam() && $totalEmailed > 0)
                            <div class="mt-4 rounded-xl bg-atly-surface px-3 py-2 text-xs">
                                <p class="font-semibold text-atly-ink">{{ $accepted }} of {{ $totalEmailed }} accepted</p>
                                <div class="mt-1.5 h-1.5 w-full rounded-full bg-atly-muted">
                                    <div class="h-1.5 rounded-full bg-emerald-500 transition-all" style="width: {{ $totalEmailed > 0 ? min(100, ($accepted / $totalEmailed) * 100) : 0 }}%"></div>
                                </div>
                                @if ($pending > 0)
                                    <p class="mt-1.5 text-[11px] text-atly-ink-soft">{{ $pending }} pending invite{{ $pending === 1 ? '' : 's' }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="mt-5 flex items-center justify-between gap-2 border-t border-atly-border pt-4">
                            <a href="{{ route('blueprint.drafts.show', $draft) }}" class="text-sm font-semibold text-atly-accent hover:underline">Open draft</a>
                            <p class="text-[11px] text-atly-ink-soft">Updated {{ $draft->updated_at->diffForHumans() }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pt-2">
                {{ $drafts->links() }}
            </div>
        @endif
    </div>
</x-layouts.dashboard>
