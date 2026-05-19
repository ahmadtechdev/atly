<x-layouts.auth title="Verify email">

    <x-auth.card title="Verify your email" subtitle="Enter the 6-digit code we sent to your inbox">
        <x-auth.alert />

        <form method="POST" action="{{ route('verification.verify') }}" class="space-y-5">
            @csrf

            <x-auth.input name="email" label="Email" type="email" :value="$email" autocomplete="email" />

            <x-auth.input
                name="code"
                label="Verification code"
                type="text"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                placeholder="000000"
                autocomplete="one-time-code"
            />

            <x-landing.button type="submit" class="w-full">Verify email</x-landing.button>
        </form>

        <form method="POST" action="{{ route('verification.resend') }}" class="mt-4">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email) }}">
            <button type="submit" class="w-full text-center text-sm font-medium text-atly-accent-strong hover:text-atly-ink">
                Resend code
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-atly-ink-soft">
            <a href="{{ route('login') }}" class="font-semibold text-atly-ink hover:text-atly-accent-strong">Back to sign in</a>
        </p>
    </x-auth.card>
</x-layouts.auth>
