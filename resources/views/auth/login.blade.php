<x-layouts.auth title="Sign in">

    <x-auth.card title="Welcome back" subtitle="Sign in to your {{ config('atly.name') }} account">
        <x-auth.alert />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <x-auth.input name="email" label="Email" type="email" autocomplete="email" placeholder="you@example.com" />

            <x-auth.input name="password" label="Password" type="password" autocomplete="current-password" placeholder="••••••••" />

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-atly-ink-soft">
                    <input type="checkbox" name="remember" value="1" class="rounded border-atly-border text-atly-ink focus:ring-atly-accent" @checked(old('remember'))>
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-atly-accent-strong hover:text-atly-ink">
                    Forgot password?
                </a>
            </div>

            <x-landing.button type="submit" class="w-full">Sign in</x-landing.button>
        </form>

        <p class="mt-6 text-center text-sm text-atly-ink-soft">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-atly-ink hover:text-atly-accent-strong">Create one</a>
        </p>
    </x-auth.card>
</x-layouts.auth>
