<section id="why-atly" class="py-20 sm:py-28">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-atly-accent-strong">Why {{ config('atly.name') }}</p>
                <h2 class="mt-3 font-display text-3xl font-bold tracking-tight text-atly-ink sm:text-4xl text-balance">
                    Task management that respects your attention
                </h2>
                <p class="mt-4 text-lg leading-relaxed text-atly-ink-soft">
                    Most tools add noise. {{ config('atly.name') }} removes it — giving you a calm, intelligent workspace where priorities are obvious and progress feels natural.
                </p>

                <ul class="mt-8 space-y-4">
                    @foreach ([
                        'Intuitive interface — no steep learning curve',
                        'Adaptive priorities that learn your workflow',
                        'Works for solo creators and growing teams',
                        'Beautiful design you will actually enjoy using',
                    ] as $point)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-atly-accent/40 text-atly-ink">
                                <x-landing.icon name="check" class="size-3.5" />
                            </span>
                            <span class="text-sm font-medium text-atly-ink">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 rounded-atly-lg bg-atly-gradient-hero opacity-80 blur-2xl" aria-hidden="true"></div>
                <div class="relative overflow-hidden rounded-atly-lg border border-atly-border bg-atly-card p-8 shadow-atly-lg">
                    <blockquote class="border-l-4 border-atly-accent pl-6">
                        <p class="font-display text-xl font-medium leading-relaxed text-atly-ink">
                            &ldquo;{{ config('atly.name') }} turned our scattered lists into one source of truth. We ship faster and stress less.&rdquo;
                        </p>
                        <footer class="mt-6">
                            <p class="font-semibold text-atly-ink">Product team lead</p>
                            <p class="text-sm text-atly-ink-soft">Early adopter</p>
                        </footer>
                    </blockquote>

                    <div class="mt-8 grid grid-cols-2 gap-4">
                        <div class="rounded-xl bg-atly-muted/60 p-4 text-center">
                            <p class="font-display text-2xl font-bold text-atly-ink">40%</p>
                            <p class="mt-1 text-xs text-atly-ink-soft">Less context switching</p>
                        </div>
                        <div class="rounded-xl bg-atly-muted/60 p-4 text-center">
                            <p class="font-display text-2xl font-bold text-atly-ink">3hrs</p>
                            <p class="mt-1 text-xs text-atly-ink-soft">Saved per week</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
