@props(['title'])

<header class="sticky top-0 z-20 border-b border-atly-border bg-atly-card/90 backdrop-blur-lg">
    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <div>
            <h1 class="font-display text-xl font-bold text-atly-ink sm:text-2xl">{{ $title }}</h1>
            <p class="mt-0.5 text-sm text-atly-ink-soft">Welcome back, {{ auth()->user()->name }}</p>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @isset($actions)
                <div class="hidden sm:flex">{{ $actions }}</div>
            @endisset
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-2 rounded-xl border border-atly-border p-1 pr-2.5 transition hover:bg-atly-muted/50 sm:pr-3"
                title="Edit profile"
            >
                @if (auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="size-9 rounded-lg object-cover">
                @else
                    <span class="flex size-9 items-center justify-center rounded-lg bg-atly-contrast-bg text-xs font-bold text-atly-contrast-fg">{{ auth()->user()->initials() }}</span>
                @endif
                <span class="hidden max-w-[8rem] truncate text-sm font-medium text-atly-ink md:inline">{{ auth()->user()->name }}</span>
            </a>
            <x-theme.toggle />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-landing.button type="submit" variant="secondary" class="!px-3 !py-2 text-xs sm:!px-4">Log out</x-landing.button>
            </form>
        </div>
    </div>
</header>
