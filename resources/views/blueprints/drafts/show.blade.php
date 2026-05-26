@php
    /** @var \App\Models\BlueprintDraft $draft */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Workspace> $workspaces */
    $colors = ['sky', 'emerald', 'amber', 'rose', 'violet', 'fuchsia'];

    $draftMembersPayload = $draft->members->map(function ($m) {
        return [
            'id' => $m->id,
            'name' => $m->name,
            'email' => $m->email,
            'skills' => $m->skills,
            'split' => $m->split,
            'status' => $m->isAccepted() ? 'accepted' : ($m->isDeclined() ? 'declined' : 'pending'),
        ];
    })->values()->all();

    $draftPayload = [
        'id' => $draft->id,
        'is_team' => $draft->isTeam(),
        'is_finalized' => $draft->isFinalized(),
        'name' => $draft->name,
        'description' => $draft->description,
        'color' => $draft->color,
        'workspace_id' => $draft->workspace_id,
        'tasks' => $draft->tasks ?? [],
        'members' => $draftMembersPayload,
    ];

    $prioritiesPayload = collect(\App\Enums\TaskPriority::cases())
        ->map(fn ($p) => ['value' => $p->value, 'label' => $p->label()])
        ->values()
        ->all();
@endphp

<x-layouts.dashboard title="{{ $draft->name }} — Draft">
    <div class="space-y-6">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <span class="mt-1 size-3 shrink-0 rounded-full bg-{{ $draft->color }}-500"></span>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="font-display text-2xl font-bold text-atly-ink line-clamp-1">{{ $draft->name }}</h2>
                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $draft->status->colorClass() }}">{{ $draft->status->label() }}</span>
                    </div>
                    <p class="mt-1 text-sm text-atly-ink-soft">{{ $draft->isTeam() ? 'Team blueprint' : 'Individual blueprint' }} · {{ $draft->start_date->format('M j, Y') }} – {{ $draft->end_date->format('M j, Y') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('blueprint.drafts.index') }}" class="rounded-xl border border-atly-border bg-atly-card px-3 py-2 text-sm font-semibold text-atly-ink-soft hover:text-atly-ink">← All drafts</a>
                <button type="button" id="bpd-delete" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">Delete draft</button>
            </div>
        </header>

        @if ($draft->isFinalized() && $draft->project)
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200">
                This draft has been finalized into the project
                <a href="{{ route('projects.show', $draft->finalized_project_id) }}" class="font-semibold underline">{{ $draft->name }}</a>.
            </div>
        @endif

        <div id="bpd-errors" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>

        <section class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly sm:p-7">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="bpd-name" class="mb-1.5 block text-sm font-medium text-atly-ink">Project name</label>
                    <input id="bpd-name" type="text" value="{{ $draft->name }}" class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                </div>
                <div>
                    <label for="bpd-workspace" class="mb-1.5 block text-sm font-medium text-atly-ink">Workspace</label>
                    <select id="bpd-workspace" class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                        <option value="">No workspace</option>
                        @foreach ($workspaces as $workspace)
                            <option value="{{ $workspace->id }}" @selected($draft->workspace_id === $workspace->id)>{{ $workspace->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <label for="bpd-description" class="mb-1.5 block text-sm font-medium text-atly-ink">Description</label>
                <textarea id="bpd-description" rows="3" class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">{{ $draft->description }}</textarea>
            </div>

            <div class="mt-5">
                <span class="mb-1.5 block text-sm font-medium text-atly-ink">Color</span>
                <div class="flex flex-wrap gap-2" id="bpd-color">
                    @foreach ($colors as $color)
                        <label class="cursor-pointer">
                            <input type="radio" name="bpd-color" value="{{ $color }}" class="peer sr-only" @checked($draft->color === $color)>
                            <span class="block size-7 rounded-full ring-2 ring-transparent transition peer-checked:ring-atly-accent peer-checked:ring-offset-2 peer-checked:ring-offset-atly-card bg-{{ $color }}-500"></span>
                        </label>
                    @endforeach
                </div>
            </div>
        </section>

        @if ($draft->isTeam())
            <section class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly sm:p-7">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-base font-bold text-atly-ink">Team & invitations</h3>
                    <button type="button" id="bpd-add-member" class="inline-flex items-center gap-1 rounded-lg bg-atly-muted px-2.5 py-1.5 text-xs font-semibold text-atly-ink hover:bg-atly-muted/80">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Add member
                    </button>
                </div>
                <p class="mt-1 text-xs text-atly-ink-soft">Each member with an email gets an invitation. The real project is created once everyone accepts (or when you click <strong>Finalize now</strong>).</p>

                <div id="bpd-members" class="mt-4 space-y-2"></div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <x-landing.button type="button" id="bpd-invite" variant="secondary">Resend pending invitations</x-landing.button>
                </div>
            </section>
        @endif

        <section class="rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly sm:p-7">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-base font-bold text-atly-ink">Tasks <span id="bpd-tasks-count" class="text-sm font-medium text-atly-ink-soft"></span></h3>
                <button type="button" id="bpd-add-task" class="inline-flex items-center gap-1.5 rounded-lg bg-atly-muted px-3 py-1.5 text-xs font-semibold text-atly-ink hover:bg-atly-muted/80">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add task
                </button>
            </div>
            <div id="bpd-tasks" class="mt-4 space-y-3"></div>
        </section>

        <section class="flex flex-wrap gap-3 rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly sm:p-7">
            <x-landing.button type="button" id="bpd-save">Save changes</x-landing.button>
            @unless ($draft->isFinalized())
                <x-landing.button type="button" id="bpd-finalize" variant="secondary">
                    @if ($draft->isTeam())
                        Finalize now ({{ $draft->acceptedMembersCount() }} accepted)
                    @else
                        Create project from this draft
                    @endif
                </x-landing.button>
            @endunless
        </section>
    </div>

    <template id="bpd-member-template">
        <div class="bpd-member space-y-2 rounded-xl border border-atly-border bg-atly-surface p-3" data-member-row>
            <div class="flex items-start justify-between gap-2">
                <div class="flex flex-1 items-center gap-2">
                    <input type="text" data-field="name" placeholder="Member name" class="w-full rounded-lg border border-atly-border bg-atly-card px-3 py-2 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                </div>
                <span class="member-status inline-flex items-center gap-1 rounded-full px-2 py-1 text-[10px] font-semibold uppercase" data-status></span>
                <button type="button" data-remove-member class="rounded-lg p-2 text-atly-ink-soft hover:bg-red-50 hover:text-red-600" aria-label="Remove member">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="grid gap-2 sm:grid-cols-[1.5fr_1fr_110px]">
                <input type="email" data-field="email" placeholder="Email" class="rounded-lg border border-atly-border bg-atly-card px-3 py-2 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                <input type="text" data-field="skills" placeholder="Skills (comma-separated)" class="rounded-lg border border-atly-border bg-atly-card px-3 py-2 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                <input type="number" data-field="split" min="0" max="100" placeholder="% (opt)" class="rounded-lg border border-atly-border bg-atly-card px-3 py-2 text-sm focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
            </div>
        </div>
    </template>

    @push('scripts')
        <script>
            window.atlyBlueprintDraft = {
                draft: @json($draftPayload),
                updateUrl: @json(route('blueprint.drafts.update', $draft)),
                inviteUrl: @json(route('blueprint.drafts.invite', $draft)),
                finalizeUrl: @json(route('blueprint.drafts.finalize', $draft)),
                destroyUrl: @json(route('blueprint.drafts.destroy', $draft)),
                draftsIndexUrl: @json(route('blueprint.drafts.index')),
                csrf: @json(csrf_token()),
                priorities: @json($prioritiesPayload),
            };
        </script>
    @endpush
</x-layouts.dashboard>
