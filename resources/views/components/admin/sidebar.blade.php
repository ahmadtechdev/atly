@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home', 'active' => request()->routeIs('admin.dashboard')],
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
    aria-label="Super admin navigation"
>
    <div class="sidebar-brand flex items-center gap-3 border-b border-atly-border px-4 py-5">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-sm font-bold text-white">
            SA
        </span>
        <div class="sidebar-brand-text min-w-0 flex-1">
            <p class="truncate font-display text-lg font-bold text-atly-ink">{{ config('atly.name') }}</p>
            <p class="truncate text-xs font-medium text-violet-600 dark:text-violet-400">Super Admin</p>
        </div>
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

    <nav class="flex-1 space-y-1 px-3 py-4" aria-label="Super admin">
        <p class="sidebar-section-title mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-atly-ink-soft">Menu</p>
        @foreach ($nav as $item)
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'sidebar-nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                    'bg-atly-muted text-atly-ink' => $item['active'],
                    'text-atly-ink-soft hover:bg-atly-muted/60 hover:text-atly-ink' => ! $item['active'],
                ])
            >
                <x-dashboard.nav-icon :name="$item['icon']" />
                <span class="sidebar-label">{{ $item['label'] }}</span>
            </a>
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
