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
                <p class="mt-0.5 hidden truncate text-sm text-atly-ink-soft sm:block">Welcome back, {{ auth()->user()->name }}</p>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1.5 sm:gap-3">
            @isset($actions)
                <div class="hidden sm:flex">{{ $actions }}</div>
            @endisset
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-2 rounded-xl border border-atly-border p-1 transition hover:bg-atly-muted/50 sm:pr-3"
                title="Edit profile"
            >
                @if (auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="size-8 rounded-lg object-cover sm:size-9">
                @else
                    <span class="flex size-8 items-center justify-center rounded-lg bg-atly-contrast-bg text-xs font-bold text-atly-contrast-fg sm:size-9">{{ auth()->user()->initials() }}</span>
                @endif
                <span class="hidden max-w-[8rem] truncate text-sm font-medium text-atly-ink md:inline">{{ auth()->user()->name }}</span>
            </a>
            <x-theme.toggle />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-landing.button type="submit" variant="secondary" class="!px-2.5 !py-1.5 text-xs sm:!px-4 sm:!py-2">Log out</x-landing.button>
            </form>
        </div>
    </div>
</header>
