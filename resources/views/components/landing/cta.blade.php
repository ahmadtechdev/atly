<section class="py-20 sm:py-28">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-atly-lg bg-atly-gradient-cta px-8 py-16 text-center shadow-atly-lg sm:px-16 sm:py-20">
            <div class="pointer-events-none absolute inset-0 bg-atly-glow opacity-40" aria-hidden="true"></div>
            <div class="relative">
                <h2 class="font-display text-3xl font-bold tracking-tight text-atly-surface sm:text-4xl text-balance">
                    Ready to work smarter with {{ config('atly.name') }}?
                </h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-atly-muted">
                    {{ config('atly.slogan') }} — join thousands who have already upgraded how they manage tasks.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <x-landing.button :href="config('atly.links.register')" variant="on-dark">
                        Create free account
                        <x-landing.icon name="arrow" class="size-4" />
                    </x-landing.button>
                    <x-landing.button :href="config('atly.links.login')" variant="secondary" class="!border-atly-surface/30 !bg-atly-surface/10 !text-atly-surface hover:!bg-atly-surface/20">
                        Sign in
                    </x-landing.button>
                </div>
            </div>
        </div>
    </div>
</section>
