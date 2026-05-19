<footer class="border-t border-atly-border bg-atly-surface py-12">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
            <div class="flex items-center gap-2.5">
                <span class="flex size-9 items-center justify-center rounded-lg bg-atly-ink text-xs font-bold text-atly-surface">
                    {{ strtoupper(substr(config('atly.name'), 0, 1)) }}
                </span>
                <div>
                    <p class="font-display font-bold text-atly-ink">{{ config('atly.name') }}</p>
                    <p class="text-xs text-atly-ink-soft">{{ config('atly.tagline') }}</p>
                </div>
            </div>

            <p class="text-sm text-atly-ink-soft">
                &copy; {{ date('Y') }} {{ config('atly.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</footer>
