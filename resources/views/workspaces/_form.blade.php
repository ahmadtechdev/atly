@props(['workspace' => null])

@php
    $colors = ['sky', 'emerald', 'amber', 'rose', 'violet', 'fuchsia'];
    $selectedColor = old('color', $workspace?->color ?? 'sky');
@endphp

<div class="space-y-5">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-atly-ink">Name</label>
        <input
            id="name"
            type="text"
            name="name"
            required
            value="{{ old('name', $workspace?->name) }}"
            placeholder="Workspace name"
            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
        />
        @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="mb-1.5 block text-sm font-medium text-atly-ink">Description</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            placeholder="Optional notes about this workspace"
            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
        >{{ old('description', $workspace?->description) }}</textarea>
        @error('description')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <span class="mb-1.5 block text-sm font-medium text-atly-ink">Color</span>
        <div class="flex flex-wrap gap-2">
            @foreach ($colors as $color)
                <label class="cursor-pointer">
                    <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked($selectedColor === $color)>
                    <span class="block size-7 rounded-full ring-2 ring-transparent transition peer-checked:ring-atly-accent peer-checked:ring-offset-2 peer-checked:ring-offset-atly-card bg-{{ $color }}-500"></span>
                </label>
            @endforeach
        </div>
    </div>
</div>
