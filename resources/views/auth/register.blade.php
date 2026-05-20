<x-layouts.auth title="Create account">

    <x-auth.card title="Create your account" subtitle="Start organizing smarter with {{ config('atly.name') }}">
        <x-auth.alert />

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <x-auth.input name="name" label="Full name" autocomplete="name" placeholder="Ahmad Khan" />

            <x-auth.input name="email" label="Email" type="email" autocomplete="email" placeholder="you@example.com" :value="request()->query('email')" />

            <x-auth.input name="password" label="Password" type="password" autocomplete="new-password" placeholder="••••••••" />

            <x-auth.input name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" placeholder="••••••••" />

            <x-landing.button type="submit" class="w-full">Create account</x-landing.button>
        </form>

        <p class="mt-6 text-center text-sm text-atly-ink-soft">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-atly-ink hover:text-atly-accent-strong">Sign in</a>
        </p>
    </x-auth.card>
</x-layouts.auth>
