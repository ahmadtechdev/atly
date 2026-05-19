@props([
    'href' => null,
    'variant' => 'primary',
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold transition-all duration-200 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-atly-accent';
    $variants = [
        'primary' => 'bg-atly-contrast-bg text-atly-contrast-fg shadow-atly hover:bg-atly-primary-hover hover:shadow-atly-lg',
        'secondary' => 'border border-atly-border bg-atly-card/80 text-atly-ink backdrop-blur-sm hover:border-atly-accent hover:bg-atly-muted/50',
        'ghost' => 'text-atly-ink-soft hover:bg-atly-muted/60 hover:text-atly-ink',
        'on-dark' => 'bg-atly-cta-fg text-atly-contrast-bg shadow-atly hover:bg-white hover:text-atly-ink',
    ];
    $classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
