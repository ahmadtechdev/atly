@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'rounded-atly-lg border border-atly-border bg-atly-card/95 p-8 shadow-atly-lg backdrop-blur-sm']) }}>
    @if ($title)
        <div class="mb-6 text-center">
            <h1 class="font-display text-2xl font-bold text-atly-ink">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-2 text-sm text-atly-ink-soft">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</div>
