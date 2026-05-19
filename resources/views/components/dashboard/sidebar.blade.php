@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home', 'active' => request()->routeIs('dashboard')],
        ['label' => 'Tasks', 'route' => 'tasks.index', 'icon' => 'tasks', 'active' => request()->routeIs('tasks.*')],
    ];
@endphp

<aside
    id="dashboard-sidebar"
    class="sticky top-0 flex h-screen w-64 shrink-0 flex-col border-r border-atly-border bg-atly-card transition-all duration-300"
>
    <div class="sidebar-brand flex items-center gap-3 border-b border-atly-border px-4 py-5">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-atly-contrast-bg text-sm font-bold text-atly-contrast-fg">
            {{ strtoupper(substr(config('atly.name'), 0, 1)) }}
        </span>
        <span class="sidebar-brand-text font-display text-lg font-bold text-atly-ink">{{ config('atly.name') }}</span>
    </div>

    <nav class="flex-1 space-y-1 px-3 py-4" aria-label="Dashboard">
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
                @if ($item['icon'] === 'home')
                    <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                @else
                    <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c1.01-.049 1.959-.218 2.816-.608a48.114 48.114 0 0 0 3.487 0 2.916 2.916 0 0 0 2.166 1.607m-9.75 8.25h7.5m-7.5 3H12" />
                    </svg>
                @endif
                <span class="sidebar-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="border-t border-atly-border p-3">
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
