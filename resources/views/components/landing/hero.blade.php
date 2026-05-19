<section class="relative overflow-hidden bg-atly-gradient-hero pt-28 pb-20 sm:pt-36 sm:pb-28">
    <div class="pointer-events-none absolute inset-0 bg-atly-glow" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -right-24 top-20 size-72 rounded-full bg-atly-accent/30 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -left-16 bottom-10 size-56 rounded-full bg-atly-muted blur-3xl" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="mb-4 inline-flex items-center gap-2 rounded-full border border-atly-border bg-atly-card/60 px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-atly-ink-soft backdrop-blur-sm">
                <span class="size-1.5 rounded-full bg-atly-accent-strong"></span>
                {{ config('atly.tagline') }}
            </p>

            <h1 class="font-display text-5xl font-extrabold tracking-tight text-atly-ink sm:text-6xl lg:text-7xl">
                {{ config('atly.name') }}
            </h1>

            <p class="mx-auto mt-4 max-w-2xl font-display text-xl font-semibold text-atly-accent-strong sm:text-2xl text-balance">
                {{ config('atly.slogan') }}
            </p>

            <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-atly-ink-soft sm:text-xl text-balance">
                {{ config('atly.description') }}
            </p>

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <x-landing.button :href="config('atly.links.register')">
                    Start for free
                    <x-landing.icon name="arrow" class="size-4" />
                </x-landing.button>
                <x-landing.button href="#features" variant="secondary">
                    Explore features
                </x-landing.button>
            </div>
        </div>

        <div class="relative mx-auto mt-16 max-w-4xl">
            <div class="overflow-hidden rounded-atly-lg border border-atly-border bg-atly-card/90 p-1 shadow-atly-lg backdrop-blur-sm">
                <div class="rounded-[calc(var(--radius-atly-lg)-4px)] bg-atly-muted/40 p-4 sm:p-6">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="size-3 rounded-full bg-atly-accent/80"></span>
                        <span class="size-3 rounded-full bg-atly-muted"></span>
                        <span class="size-3 rounded-full bg-atly-border"></span>
                        <span class="ml-3 text-xs font-medium text-atly-ink-soft">Today — {{ config('atly.name') }}</span>
                    </div>
                    <div class="space-y-3">
                        @foreach ([
                            ['label' => 'Ship landing page design', 'done' => true, 'tag' => 'High'],
                            ['label' => 'Review sprint backlog with team', 'done' => false, 'tag' => 'Medium'],
                            ['label' => 'Prepare product demo for stakeholders', 'done' => false, 'tag' => 'Today'],
                        ] as $task)
                            <div class="flex items-center gap-3 rounded-xl border border-atly-border/80 bg-atly-card px-4 py-3 shadow-sm transition hover:border-atly-accent/50">
                                <span @class([
                                    'flex size-5 shrink-0 items-center justify-center rounded-md border-2',
                                    'border-atly-accent bg-atly-accent text-atly-surface' => $task['done'],
                                    'border-atly-border' => ! $task['done'],
                                ])>
                                    @if ($task['done'])
                                        <x-landing.icon name="check" class="size-3" />
                                    @endif
                                </span>
                                <span @class([
                                    'flex-1 text-sm font-medium',
                                    'text-atly-ink-soft line-through' => $task['done'],
                                    'text-atly-ink' => ! $task['done'],
                                ])>{{ $task['label'] }}</span>
                                <span class="rounded-md bg-atly-muted px-2 py-0.5 text-xs font-medium text-atly-ink-soft">{{ $task['tag'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-auto mt-16 grid max-w-3xl grid-cols-3 gap-6 border-t border-atly-border/60 pt-10">
            @foreach (config('atly.stats') as $stat)
                <div class="text-center">
                    <p class="font-display text-2xl font-bold text-atly-ink sm:text-3xl">{{ $stat['value'] }}</p>
                    <p class="mt-1 text-xs font-medium text-atly-ink-soft sm:text-sm">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
