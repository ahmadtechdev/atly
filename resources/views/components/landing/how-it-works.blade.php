<section id="how-it-works" class="bg-atly-muted/40 py-20 sm:py-28">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-atly-accent-strong">How it works</p>
            <h2 class="mt-3 font-display text-3xl font-bold tracking-tight text-atly-ink sm:text-4xl text-balance">
                From chaos to clarity in three steps
            </h2>
        </div>

        <div class="mt-16 grid gap-8 lg:grid-cols-3">
            @foreach (config('atly.steps') as $step)
                <article class="relative rounded-atly-lg border border-atly-border bg-atly-card p-8 shadow-atly">
                    <span class="font-display text-5xl font-extrabold text-atly-muted">{{ $step['number'] }}</span>
                    <h3 class="mt-4 font-display text-xl font-semibold text-atly-ink">{{ $step['title'] }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-atly-ink-soft">{{ $step['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
