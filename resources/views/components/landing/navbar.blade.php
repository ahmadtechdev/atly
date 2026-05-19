<header class="fixed inset-x-0 top-0 z-50 border-b border-atly-border/60 bg-atly-surface/80 backdrop-blur-lg">
    <nav class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8" aria-label="Main">
        <a href="/" class="group flex items-center gap-2.5">
            <span class="flex size-10 items-center justify-center rounded-xl bg-atly-contrast-bg text-sm font-bold tracking-tight text-atly-contrast-fg shadow-atly transition group-hover:scale-105">
                {{ strtoupper(substr(config('atly.name'), 0, 1)) }}
            </span>
            <span class="font-display text-xl font-bold tracking-tight text-atly-ink">{{ config('atly.name') }}</span>
        </a>

        <div class="hidden items-center gap-8 md:flex">
            @foreach (config('atly.nav') as $item)
                <a href="{{ $item['href'] }}" class="text-sm font-medium text-atly-ink-soft transition hover:text-atly-ink">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="hidden items-center gap-3 md:flex">
            <x-theme.toggle />
            <x-landing.button :href="config('atly.links.login')" variant="ghost" class="!px-4 !py-2">
                Log in
            </x-landing.button>
            <x-landing.button :href="config('atly.links.register')" class="!px-5 !py-2.5">
                Get started
            </x-landing.button>
        </div>

        <div class="flex items-center gap-2 md:hidden">
            <x-theme.toggle />
        <button
            id="nav-toggle"
            type="button"
            class="inline-flex size-10 items-center justify-center rounded-lg text-atly-ink hover:bg-atly-muted"
            aria-expanded="false"
            aria-controls="nav-menu"
            aria-label="Toggle menu"
        >
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>
        </div>
    </nav>

    <div id="nav-menu" class="hidden border-t border-atly-border/60 bg-atly-surface px-4 py-4 md:hidden">
        <div class="flex flex-col gap-3">
            @foreach (config('atly.nav') as $item)
                <a href="{{ $item['href'] }}" class="rounded-lg px-3 py-2 text-sm font-medium text-atly-ink-soft hover:bg-atly-muted hover:text-atly-ink">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <div class="flex items-center justify-between border-t border-atly-border/60 pt-4">
                <span class="text-sm font-medium text-atly-ink-soft">Appearance</span>
                <x-theme.toggle />
            </div>
            <div class="mt-2 flex flex-col gap-2 border-t border-atly-border/60 pt-4">
                <x-landing.button :href="config('atly.links.login')" variant="secondary" class="w-full">
                    Log in
                </x-landing.button>
                <x-landing.button :href="config('atly.links.register')" class="w-full">
                    Get started
                </x-landing.button>
            </div>
        </div>
    </div>
</header>
