@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Workspace> $workspaces */
    /** @var array<int, array<string, mixed>> $models */
    /** @var bool $hasAvailableModels */
    /** @var string|null $donationUrl */
    $today = now()->toDateString();
    $defaultEnd = now()->addWeeks(4)->toDateString();
    $colors = ['sky', 'emerald', 'amber', 'rose', 'violet', 'fuchsia'];
@endphp

<x-layouts.dashboard title="AI Blueprint">
    <div class="space-y-6">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-2xl font-bold text-atly-ink">AI Blueprint</h2>
                <p class="mt-1 text-sm text-atly-ink-soft">Describe your project (or upload a brief) and let AI draft a complete task plan you can review before saving.</p>
            </div>
            @if ($hasAvailableModels)
                <span class="inline-flex w-fit items-center gap-2 rounded-full bg-atly-muted px-3 py-1.5 text-xs font-semibold text-atly-ink">
                    <span class="size-2 rounded-full bg-emerald-500"></span>
                    {{ count($models) }} {{ count($models) === 1 ? 'model' : 'models' }} available
                </span>
            @endif
        </header>

        @unless ($hasAvailableModels)
            {{-- Donation / no-availability state --}}
            <div class="overflow-hidden rounded-atly-lg border border-atly-border bg-atly-gradient-hero shadow-atly">
                <div class="px-6 py-10 text-center sm:px-10 sm:py-14">
                    <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-atly-card text-atly-ink shadow-atly">
                        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                    </div>
                    <h3 class="mt-4 font-display text-xl font-bold text-atly-ink">AI Blueprint is taking a breather</h3>
                    <p class="mx-auto mt-2 max-w-lg text-sm text-atly-ink-soft">
                        Every available AI model is either out of monthly credits or hasn't been connected yet. Running these models isn't free for the dev team — your help keeps the feature alive for everyone.
                    </p>
                    @if ($donationUrl)
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ $donationUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-atly-contrast-bg px-5 py-3 text-sm font-semibold text-atly-contrast-fg shadow-atly transition hover:scale-[1.02]">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5a2.25 2.25 0 1 1 4.5 0c0 1.5-3 2.25-3 3.75M12 18h.008v.008H12V18Zm9-6a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                Chip in for AI credits
                            </a>
                            <a href="{{ route('projects.index') }}" class="text-sm font-semibold text-atly-ink-soft hover:text-atly-ink">Plan manually instead →</a>
                        </div>
                    @else
                        <p class="mt-6 text-xs text-atly-ink-soft">Please check back later — fresh credits arrive every month.</p>
                    @endif
                </div>
            </div>
        @endunless

        @if ($hasAvailableModels)
            {{-- Step 1: Input form --}}
            <section id="blueprint-form-section" class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly sm:p-7">
                <h3 class="font-display text-lg font-bold text-atly-ink">1. Describe your project</h3>
                <p class="mt-1 text-sm text-atly-ink-soft">Provide a written brief, upload a document, or both.</p>

                <form id="blueprint-form" class="mt-6 space-y-5" enctype="multipart/form-data">
                    @csrf

                    <div>
                        <label for="bp-model" class="mb-1.5 block text-sm font-medium text-atly-ink">AI model</label>
                        <select
                            id="bp-model"
                            name="model"
                            required
                            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        >
                            @foreach ($models as $model)
                                <option value="{{ $model['id'] }}">
                                    {{ $model['label'] }}
                                    @if ($model['tagline']) — {{ $model['tagline'] }} @endif
                                    @if ($model['remaining'] !== null) ({{ $model['remaining'] }} of {{ $model['monthly_limit'] }} left this month) @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-atly-ink-soft">Models are powered by API keys provided by the team — pick whichever feels right.</p>
                    </div>

                    <div>
                        <label for="bp-description" class="mb-1.5 block text-sm font-medium text-atly-ink">Project description</label>
                        <textarea
                            id="bp-description"
                            name="description"
                            rows="6"
                            placeholder="e.g. Build a small e-commerce site for a local bakery with product catalog, cart, and Stripe checkout. Mobile-first..."
                            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        ></textarea>
                    </div>

                    <div>
                        <label for="bp-document" class="mb-1.5 block text-sm font-medium text-atly-ink">Or upload a brief (optional)</label>
                        <input
                            id="bp-document"
                            type="file"
                            name="document"
                            accept=".txt,.md,.pdf,.docx"
                            class="w-full rounded-xl border border-dashed border-atly-border bg-atly-surface px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-atly-muted file:px-3 file:py-1.5 file:text-sm file:font-medium"
                        />
                        <p class="mt-1 text-xs text-atly-ink-soft">Accepted: PDF, DOCX, TXT, MD — up to 8 MB.</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="bp-start" class="mb-1.5 block text-sm font-medium text-atly-ink">Start date</label>
                            <input
                                id="bp-start"
                                type="date"
                                name="start_date"
                                value="{{ $today }}"
                                required
                                class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                            />
                        </div>
                        <div>
                            <label for="bp-end" class="mb-1.5 block text-sm font-medium text-atly-ink">Estimated end date</label>
                            <input
                                id="bp-end"
                                type="date"
                                name="end_date"
                                value="{{ $defaultEnd }}"
                                required
                                class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                            />
                        </div>
                    </div>

                    <div>
                        <span class="mb-1.5 block text-sm font-medium text-atly-ink">Assignment</span>
                        <div class="inline-flex rounded-xl border border-atly-border bg-atly-surface p-1" role="tablist">
                            <label class="cursor-pointer">
                                <input type="radio" name="assignment_type" value="individual" class="peer sr-only" checked>
                                <span class="block rounded-lg px-4 py-2 text-sm font-medium text-atly-ink-soft transition peer-checked:bg-atly-contrast-bg peer-checked:text-atly-contrast-fg">Individual</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="assignment_type" value="team" class="peer sr-only">
                                <span class="block rounded-lg px-4 py-2 text-sm font-medium text-atly-ink-soft transition peer-checked:bg-atly-contrast-bg peer-checked:text-atly-contrast-fg">Team</span>
                            </label>
                        </div>
                    </div>

                    <div id="bp-team-block" class="hidden space-y-3 rounded-xl border border-atly-border bg-atly-surface/60 p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-atly-ink">Team members</span>
                            <button type="button" id="bp-add-member" class="inline-flex items-center gap-1 rounded-lg bg-atly-muted px-2.5 py-1.5 text-xs font-semibold text-atly-ink hover:bg-atly-muted/80">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Add member
                            </button>
                        </div>
                        <div id="bp-members"></div>
                        <p class="text-xs text-atly-ink-soft">Workload % is optional and used as guidance for the AI to balance tasks.</p>
                    </div>

                    <div id="bp-form-errors" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                    <div class="flex flex-wrap gap-3 border-t border-atly-border pt-5">
                        <x-landing.button type="submit" id="bp-generate-btn">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.847.813a4.5 4.5 0 0 0-3.09 3.091Z" /></svg>
                            <span id="bp-generate-label">Generate plan</span>
                        </x-landing.button>
                        @if ($donationUrl)
                            <a href="{{ $donationUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 text-sm font-semibold text-atly-ink-soft hover:text-atly-ink">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                                Support this feature
                            </a>
                        @endif
                    </div>
                </form>
            </section>

            {{-- Step 2: Draft review --}}
            <section id="blueprint-draft-section" class="hidden rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly sm:p-7">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-lg font-bold text-atly-ink">2. Review & edit your plan</h3>
                        <p class="mt-1 text-sm text-atly-ink-soft">Tweak anything before saving. Nothing is stored until you confirm.</p>
                    </div>
                    <button type="button" id="bp-regenerate" class="text-sm font-semibold text-atly-ink-soft hover:text-atly-ink">
                        ← Start over
                    </button>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="bp-project-name" class="mb-1.5 block text-sm font-medium text-atly-ink">Project name</label>
                        <input
                            id="bp-project-name"
                            type="text"
                            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        />
                    </div>
                    <div>
                        <label for="bp-project-workspace" class="mb-1.5 block text-sm font-medium text-atly-ink">Workspace (optional)</label>
                        <select id="bp-project-workspace" class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                            <option value="">No workspace</option>
                            @foreach ($workspaces as $workspace)
                                <option value="{{ $workspace->id }}">{{ $workspace->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-5">
                    <label for="bp-project-description" class="mb-1.5 block text-sm font-medium text-atly-ink">Project description</label>
                    <textarea id="bp-project-description" rows="3" class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"></textarea>
                </div>

                <div class="mt-5">
                    <span class="mb-1.5 block text-sm font-medium text-atly-ink">Color</span>
                    <div class="flex flex-wrap gap-2" id="bp-project-color">
                        @foreach ($colors as $color)
                            <label class="cursor-pointer">
                                <input type="radio" name="bp-color" value="{{ $color }}" class="peer sr-only" @checked($loop->first)>
                                <span class="block size-7 rounded-full ring-2 ring-transparent transition peer-checked:ring-atly-accent peer-checked:ring-offset-2 peer-checked:ring-offset-atly-card bg-{{ $color }}-500"></span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between">
                    <h4 class="font-display text-base font-bold text-atly-ink">Generated tasks <span id="bp-tasks-count" class="text-sm font-medium text-atly-ink-soft"></span></h4>
                    <button type="button" id="bp-add-task" class="inline-flex items-center gap-1.5 rounded-lg bg-atly-muted px-3 py-1.5 text-xs font-semibold text-atly-ink hover:bg-atly-muted/80">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Add task
                    </button>
                </div>

                <div id="bp-tasks-list" class="mt-4 space-y-3"></div>

                <div id="bp-finalize-errors" class="mt-4 hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

                <div class="mt-6 flex flex-wrap gap-3 border-t border-atly-border pt-5">
                    <x-landing.button type="button" id="bp-finalize-btn">
                        Confirm & create project
                    </x-landing.button>
                    <x-landing.button type="button" variant="secondary" id="bp-cancel-draft">Discard draft</x-landing.button>
                </div>
            </section>
        @endif
    </div>

    <template id="bp-member-template">
        <div class="bp-member grid gap-2 sm:grid-cols-[1fr_1fr_120px_auto]" data-member-row>
            <input type="text" data-field="name" placeholder="Member name" class="rounded-xl border border-atly-border bg-atly-card px-3 py-2 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
            <input type="text" data-field="skill" placeholder="Skill (e.g. Frontend)" class="rounded-xl border border-atly-border bg-atly-card px-3 py-2 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
            <input type="number" data-field="split" min="0" max="100" placeholder="% (opt)" class="rounded-xl border border-atly-border bg-atly-card px-3 py-2 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
            <button type="button" data-remove-member class="rounded-lg p-2 text-atly-ink-soft hover:bg-red-50 hover:text-red-600" aria-label="Remove member">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </template>

    @push('scripts')
        <script>
            window.atlyBlueprint = {
                generateUrl: @json(route('blueprint.generate')),
                storeUrl: @json(route('blueprint.store')),
                csrf: @json(csrf_token()),
                priorities: @json(\App\Enums\TaskPriority::cases()),
                hasAvailableModels: @json($hasAvailableModels),
            };
        </script>
    @endpush
</x-layouts.dashboard>
