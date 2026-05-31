<x-layouts.admin title="Settings">
    <div class="mx-auto max-w-lg">
        <div class="overflow-hidden rounded-atly-lg border border-atly-border bg-atly-card shadow-atly-lg">
            <div class="border-b border-atly-border px-6 py-5 sm:px-8">
                <h2 class="font-display text-xl font-bold text-atly-ink">Account settings</h2>
                <p class="mt-1 text-sm text-atly-ink-soft">Update your super admin password.</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5 p-6 sm:p-8">
                @csrf
                @method('PUT')

                <x-auth.alert />

                <div class="rounded-xl border border-atly-border bg-atly-muted/30 px-4 py-3">
                    <p class="text-sm font-medium text-atly-ink">{{ $user->name }}</p>
                    <p class="text-sm text-atly-ink-soft">{{ $user->email }}</p>
                </div>

                <x-auth.input name="current_password" label="Current password" type="password" autocomplete="current-password" />

                <x-auth.input name="password" label="New password" type="password" autocomplete="new-password" />

                <x-auth.input name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" />

                <div class="flex flex-wrap gap-3 border-t border-atly-border pt-6">
                    <x-landing.button type="submit">Update password</x-landing.button>
                    <x-landing.button :href="route('admin.dashboard')" variant="secondary">Cancel</x-landing.button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
