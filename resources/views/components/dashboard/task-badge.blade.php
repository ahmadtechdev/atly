@props(['type' => 'status', 'value'])

@php
    $enum = $type === 'priority'
        ? \App\Enums\TaskPriority::from($value)
        : \App\Enums\TaskStatus::from($value);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-md px-2 py-0.5 text-xs font-semibold '.$enum->colorClass()]) }}>
    {{ $enum->label() }}
</span>
