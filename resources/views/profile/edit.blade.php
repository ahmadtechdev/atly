<x-layouts.dashboard title="Profile">
    <div class="mx-auto max-w-2xl">
        <div class="overflow-hidden rounded-atly-lg border border-atly-border bg-atly-card shadow-atly-lg">
            <div class="bg-atly-gradient-hero px-6 py-8 sm:px-8">
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-end">
                    <div class="relative">
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="size-24 rounded-2xl border-4 border-atly-card object-cover shadow-atly">
                        @else
                            <div class="flex size-24 items-center justify-center rounded-2xl border-4 border-atly-card bg-atly-contrast-bg font-display text-3xl font-bold text-atly-contrast-fg shadow-atly">
                                {{ $user->initials() }}
                            </div>
                        @endif
                    </div>
                    <div class="text-center sm:text-left">
                        <h2 class="font-display text-2xl font-bold text-atly-ink">{{ $user->name }}</h2>
                        <p class="text-sm text-atly-ink-soft">{{ $user->email }}</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6 p-6 sm:p-8">
                @csrf
                @method('PUT')

                <x-auth.input name="name" label="Display name" :value="old('name', $user->name)" />

                <div>
                    <label for="avatar" class="mb-1.5 block text-sm font-medium text-atly-ink">Profile picture</label>
                    <input
                        id="avatar"
                        name="avatar"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        class="w-full rounded-xl border border-dashed border-atly-border bg-atly-surface px-4 py-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-atly-muted file:px-3 file:py-1.5 file:text-sm file:font-medium"
                    />
                    <p class="mt-1 text-xs text-atly-ink-soft">JPG, PNG or WebP. Max 2 MB.</p>
                    @error('avatar')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if ($user->avatar_path)
                        <label class="mt-3 flex items-center gap-2 text-sm text-atly-ink-soft">
                            <input type="checkbox" name="remove_avatar" value="1" class="rounded border-atly-border">
                            Remove current photo
                        </label>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3 border-t border-atly-border pt-6">
                    <x-landing.button type="submit">Save profile</x-landing.button>
                    <x-landing.button :href="route('dashboard')" variant="secondary">Cancel</x-landing.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
