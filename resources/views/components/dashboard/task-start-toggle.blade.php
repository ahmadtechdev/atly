@props([
    'startUrl' => '',
    'size' => 'md',
])

@php
    $buttonSize = $size === 'sm' ? 'size-8' : 'size-9';
    $iconSize = $size === 'sm' ? 'size-4' : 'size-[1.125rem]';
@endphp

<button
    type="button"
    data-start-task
    data-start-url="{{ $startUrl }}"
    {{ $attributes->merge(['class' => "group/start relative flex {$buttonSize} shrink-0 items-center justify-center rounded-full text-atly-ink-soft transition hover:bg-sky-500/10 hover:text-sky-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500/40 focus-visible:ring-offset-2 focus-visible:ring-offset-atly-card dark:hover:text-sky-400"]) }}
    aria-label="Start task"
    title="Start task"
>
    <span class="flex {{ $iconSize }} items-center justify-center rounded-full border-2 border-dashed border-atly-ink-soft/50 bg-atly-card transition-all duration-200 group-hover/start:border-sky-500 group-hover/start:bg-sky-500/10 group-hover/start:text-sky-600">
        <svg class="{{ $size === 'sm' ? 'size-2.5' : 'size-3' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 0 1 9 0v3.75M8.25 10.5V6.75a4.5 4.5 0 0 0-9 0v3.75m0 0v4.875c0 .621.504 1.125 1.125 1.125h15.75c.621 0 1.125-.504 1.125-1.125V10.5m-16.5 0h16.5" />
        </svg>
    </span>
</button>
