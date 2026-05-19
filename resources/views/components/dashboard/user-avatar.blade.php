@props(['user', 'size' => 'md'])

@php
    $sizeClasses = match ($size) {
        'sm' => 'size-8 text-[10px]',
        'lg' => 'size-11 text-sm',
        default => 'size-9 text-xs',
    };
@endphp

@if ($user->avatar_url)
    <img
        {{ $attributes->merge(['class' => "{$sizeClasses} shrink-0 rounded-full object-cover ring-2 ring-atly-card"]) }}
        src="{{ $user->avatar_url }}"
        alt="{{ $user->name }}"
    />
@else
    <span
        {{ $attributes->merge(['class' => "flex {$sizeClasses} shrink-0 items-center justify-center rounded-full bg-atly-contrast-bg font-semibold text-atly-contrast-fg ring-2 ring-atly-card"]) }}
        aria-hidden="true"
    >{{ $user->initials() }}</span>
@endif
