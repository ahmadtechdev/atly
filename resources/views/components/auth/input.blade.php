@props([
    'label',
    'name',
    'type' => 'text',
    'placeholder' => null,
    'required' => true,
    'autocomplete' => null,
    'value' => null,
])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-atly-ink">
        {{ $label }}
    </label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 transition focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30']) }}
    />
    @error($name)
        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
