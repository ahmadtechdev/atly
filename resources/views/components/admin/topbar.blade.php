@props(['title'])

<header class="sticky top-0 z-20 border-b border-atly-border bg-atly-card/90 backdrop-blur-lg">
    <div class="flex items-center justify-between gap-3 px-3 py-3 sm:gap-4 sm:px-6 sm:py-4 lg:px-8">
        <div class="flex min-w-0 items-center gap-2 sm:gap-3">
            <button
                id="sidebar-mobile-open"
                type="button"
                class="inline-flex shrink-0 items-center justify-center rounded-xl border border-atly-border bg-atly-card p-2 text-atly-ink-soft hover:bg-atly-muted/50 hover:text-atly-ink lg:hidden"
                aria-label="Open menu"
                aria-controls="dashboard-sidebar"
                aria-expanded="false"
            >
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <div class="min-w-0">
                <h1 class="truncate font-display text-lg font-bold text-atly-ink sm:text-2xl">{{ $title }}</h1>
                <p class="mt-0.5 hidden truncate text-sm text-atly-ink-soft sm:block">{{ auth()->user()->email }}</p>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1.5 sm:gap-3">
            @isset($actions)
                <div class="hidden sm:flex">{{ $actions }}</div>
            @endisset
            <a
                href="{{ route('admin.settings.edit') }}"
                class="inline-flex items-center justify-center rounded-xl border border-atly-border p-2 text-atly-ink-soft transition hover:bg-atly-muted/50 hover:text-atly-ink"
                title="Settings"
            >
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
            </a>
            <x-theme.toggle />
            <form method="POST" action="{{ route('admin.logout') }}" class="shrink-0">
                @csrf
                <x-landing.button type="submit" variant="secondary" class="!px-2.5 !py-1.5 text-xs sm:!px-4 sm:!py-2">Log out</x-landing.button>
            </form>
        </div>
    </div>
</header>
