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
            <x-theme.toggle />
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-landing.button type="submit" variant="secondary" class="!px-3 !py-2 text-xs sm:!px-4">Log out</x-landing.button>
            </form>
        </div>
    </div>
</header>
