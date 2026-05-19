<section id="features" class="bg-atly-card py-20 sm:py-28">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-atly-accent-strong">Features</p>
            <h2 class="mt-3 font-display text-3xl font-bold tracking-tight text-atly-ink sm:text-4xl text-balance">
                Everything you need to stay on top of work
            </h2>
            <p class="mt-4 text-lg text-atly-ink-soft">
                Built for focus, designed for teams — {{ config('atly.name') }} keeps task management simple and powerful.
            </p>
        </div>

        <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (config('atly.features') as $feature)
                <article class="group rounded-atly-lg border border-atly-border bg-atly-surface/50 p-6 transition duration-300 hover:border-atly-accent hover:bg-atly-muted/30 hover:shadow-atly">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-atly-accent/30 text-atly-ink transition group-hover:bg-atly-accent group-hover:text-atly-surface">
                        <x-landing.icon :name="$feature['icon']" class="size-6" />
                    </div>
                    <h3 class="font-display text-lg font-semibold text-atly-ink">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-atly-ink-soft">{{ $feature['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
