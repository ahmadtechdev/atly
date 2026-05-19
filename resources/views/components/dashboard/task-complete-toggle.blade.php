@props([
    'completed' => false,
    'completeUrl' => '',
    'size' => 'md',
])

@php
    $buttonSize = $size === 'sm' ? 'size-8' : 'size-9';
    $indicatorSize = $size === 'sm' ? 'size-[1.125rem]' : 'size-5';
@endphp

<button
    type="button"
    data-toggle-complete
    data-complete-url="{{ $completeUrl }}"
    data-is-completed="{{ $completed ? '1' : '0' }}"
    {{ $attributes->merge(['class' => "group/check relative flex {$buttonSize} shrink-0 items-center justify-center rounded-full transition hover:bg-atly-muted/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-atly-accent/50 focus-visible:ring-offset-2 focus-visible:ring-offset-atly-card"]) }}
    aria-label="{{ $completed ? 'Mark as incomplete' : 'Mark as complete' }}"
    title="{{ $completed ? 'Mark as incomplete' : 'Mark as complete' }}"
>
    <span
        data-complete-indicator
        @class([
            "{$indicatorSize} flex items-center justify-center rounded-full border-2 transition-all duration-200",
            'border-emerald-500 bg-emerald-500 text-white shadow-sm shadow-emerald-500/30' => $completed,
            'border-atly-border bg-atly-card group-hover/check:border-atly-accent group-hover/check:bg-atly-muted/30' => ! $completed,
        ])
    >
        @if ($completed)
            <svg class="{{ $size === 'sm' ? 'size-2.5' : 'size-3' }}" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-7.5" />
            </svg>
        @endif
    </span>
</button>
