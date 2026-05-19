@props(['label', 'value', 'hint' => null, 'accent' => false])

<div @class([
    'rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly transition hover:shadow-atly-lg',
    'ring-1 ring-atly-accent/30' => $accent,
])>
    <p class="text-sm font-medium text-atly-ink-soft">{{ $label }}</p>
    <p class="mt-2 font-display text-3xl font-bold text-atly-ink">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-atly-ink-soft">{{ $hint }}</p>
    @endif
</div>
