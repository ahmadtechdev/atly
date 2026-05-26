@php
    $pendingInvitationsCount = $pendingInvitationsCount ?? 0;
    $activeDraftsCount = 0;

    if (auth()->check()) {
        $activeDraftsCount = \App\Models\BlueprintDraft::query()
            ->forUser(auth()->user())
            ->active()
            ->count();
    }

    $nav = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Tasks', 'route' => 'tasks.index', 'icon' => 'tasks', 'active' => request()->routeIs('tasks.*'), 'addAction' => 'data-open-task-modal', 'addTitle' => 'Add task'],
        ['label' => 'Projects', 'route' => 'projects.index', 'icon' => 'projects', 'active' => request()->routeIs('projects.*'), 'addAction' => 'data-open-project-modal', 'addTitle' => 'Add project'],
        ['label' => 'Workspaces', 'route' => 'workspaces.index', 'icon' => 'workspaces', 'active' => request()->routeIs('workspaces.*'), 'addAction' => 'data-open-workspace-modal', 'addTitle' => 'Add workspace'],
        ['label' => 'AI Blueprint', 'route' => 'blueprint.index', 'icon' => 'blueprint', 'active' => request()->routeIs('blueprint.index') || request()->routeIs('blueprint.generate') || request()->routeIs('blueprint.store')],
        ['label' => 'Blueprint Drafts', 'route' => 'blueprint.drafts.index', 'icon' => 'blueprint-drafts', 'active' => request()->routeIs('blueprint.drafts.*'), 'badge' => $activeDraftsCount],
        ['label' => 'Time Tracker', 'route' => 'time-tracker.index', 'icon' => 'timer', 'active' => request()->routeIs('time-tracker.*')],
        ['label' => 'Invitations', 'route' => 'invitations.index', 'icon' => 'invitations', 'active' => request()->routeIs('invitations.*'), 'badge' => $pendingInvitationsCount],
        ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'profile', 'active' => request()->routeIs('profile.*')],
    ];
@endphp

<div
    id="dashboard-sidebar-backdrop"
    class="fixed inset-0 z-30 hidden bg-atly-ink/40 backdrop-blur-sm lg:hidden"
    aria-hidden="true"
></div>

<aside
    id="dashboard-sidebar"
    class="fixed inset-y-0 left-0 z-40 flex h-screen w-64 shrink-0 -translate-x-full flex-col border-r border-atly-border bg-atly-card transition-transform duration-300 lg:sticky lg:top-0 lg:z-auto lg:translate-x-0"
    aria-label="Primary navigation"
>
    <div class="sidebar-brand flex items-center gap-3 border-b border-atly-border px-4 py-5">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-atly-contrast-bg text-sm font-bold text-atly-contrast-fg">
            {{ strtoupper(substr(config('atly.name'), 0, 1)) }}
        </span>
        <span class="sidebar-brand-text flex-1 font-display text-lg font-bold text-atly-ink">{{ config('atly.name') }}</span>
        <button
            type="button"
            id="sidebar-mobile-close"
            class="rounded-lg p-1.5 text-atly-ink-soft hover:bg-atly-muted hover:text-atly-ink lg:hidden"
            aria-label="Close menu"
        >
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-4" aria-label="Dashboard">
        <p class="sidebar-section-title mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-atly-ink-soft">Menu</p>
        @foreach ($nav as $item)
            @php
                $hasAddButton = ! empty($item['addAction']);
            @endphp

            @if ($hasAddButton)
                <div class="flex items-center gap-1">
                    <a
                        href="{{ route($item['route']) }}"
                        @class([
                            'sidebar-nav-link flex flex-1 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                            'bg-atly-muted text-atly-ink' => $item['active'],
                            'text-atly-ink-soft hover:bg-atly-muted/60 hover:text-atly-ink' => ! $item['active'],
                        ])
                    >
                        <x-dashboard.nav-icon :name="$item['icon']" />
                        <span class="sidebar-label">{{ $item['label'] }}</span>
                    </a>
                    <button
                        type="button"
                        {{ $item['addAction'] }}
                        class="sidebar-task-add flex size-9 shrink-0 items-center justify-center rounded-xl bg-atly-contrast-bg text-atly-contrast-fg shadow-sm transition hover:scale-105"
                        title="{{ $item['addTitle'] }}"
                        aria-label="{{ $item['addTitle'] }}"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </button>
                </div>
            @else
                @php
                    $badge = $item['badge'] ?? 0;
                @endphp
                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'sidebar-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                        'bg-atly-muted text-atly-ink' => $item['active'],
                        'text-atly-ink-soft hover:bg-atly-muted/60 hover:text-atly-ink' => ! $item['active'],
                    ])
                >
                    <x-dashboard.nav-icon :name="$item['icon']" />
                    <span class="sidebar-label flex-1">{{ $item['label'] }}</span>
                    @if ($badge > 0)
                        <span class="sidebar-label inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-atly-contrast-bg px-1.5 py-0.5 text-[10px] font-bold leading-none text-atly-contrast-fg">
                            {{ $badge > 99 ? '99+' : $badge }}
                        </span>
                    @endif
                </a>
            @endif
        @endforeach
    </nav>

    <div class="hidden border-t border-atly-border p-3 lg:block">
        <button
            id="sidebar-toggle"
            type="button"
            aria-expanded="true"
            aria-label="Collapse sidebar"
            class="sidebar-nav-link flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-atly-ink-soft transition hover:bg-atly-muted/60 hover:text-atly-ink"
        >
            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
            </svg>
            <span class="sidebar-label">Collapse</span>
        </button>
    </div>
</aside>
