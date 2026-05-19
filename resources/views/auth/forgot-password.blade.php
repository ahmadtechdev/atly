<x-layouts.auth title="Forgot password">

    <x-auth.card title="Forgot password?" subtitle="We'll email you a 6-digit code to reset it">
        <x-auth.alert />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <x-auth.input name="email" label="Email" type="email" autocomplete="email" placeholder="you@example.com" />

            <x-landing.button type="submit" class="w-full">Send reset code</x-landing.button>
        </form>

        <p class="mt-6 text-center text-sm text-atly-ink-soft">
            <a href="{{ route('login') }}" class="font-semibold text-atly-ink hover:text-atly-accent-strong">Back to sign in</a>
        </p>
    </x-auth.card>
</x-layouts.auth>
