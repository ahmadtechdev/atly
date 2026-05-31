<x-layouts.auth title="Super Admin">

    <x-auth.card title="Super Admin" subtitle="Sign in to the {{ config('atly.name') }} control panel">
        <x-auth.alert />

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-5">
            @csrf

            <x-auth.input name="email" label="Email" type="email" autocomplete="email" placeholder="admin@atly.com" />

            <x-auth.input name="password" label="Password" type="password" autocomplete="current-password" placeholder="••••••••" />

            <div class="flex items-center">
                <label class="flex items-center gap-2 text-sm text-atly-ink-soft">
                    <input type="checkbox" name="remember" value="1" class="rounded border-atly-border text-atly-ink focus:ring-atly-accent" @checked(old('remember'))>
                    Remember me
                </label>
            </div>

            <x-landing.button type="submit" class="w-full">Sign in to admin</x-landing.button>
        </form>

        <p class="mt-6 text-center text-sm text-atly-ink-soft">
            <a href="{{ route('home') }}" class="font-semibold text-atly-ink hover:text-atly-accent-strong">Back to site</a>
        </p>
    </x-auth.card>
</x-layouts.auth>
