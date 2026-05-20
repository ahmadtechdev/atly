@props(['type' => 'success'])

@php
    $styles = match ($type) {
        'error' => 'border-red-200 bg-red-50 text-red-800',
        default => 'border-atly-accent/40 bg-atly-muted/50 text-atly-ink',
    };
@endphp

@if ($slot->isNotEmpty())
    <div {{ $attributes->merge(['class' => "mb-6 rounded-xl border px-4 py-3 text-sm {$styles}"]) }}>
        {{ $slot }}
    </div>
@endif
