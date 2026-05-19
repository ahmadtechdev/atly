<x-layouts.auth title="Reset password">

    <x-auth.card title="Reset password" subtitle="Enter the code from your email and choose a new password">
        <x-auth.alert />

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf

            <x-auth.input name="email" label="Email" type="email" :value="$email" autocomplete="email" />

            <x-auth.input
                name="code"
                label="Reset code"
                type="text"
                inputmode="numeric"
                pattern="[0-9]{6}"
                maxlength="6"
                placeholder="000000"
                autocomplete="one-time-code"
            />

            <x-auth.input name="password" label="New password" type="password" autocomplete="new-password" placeholder="••••••••" />

            <x-auth.input name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" placeholder="••••••••" />

            <x-landing.button type="submit" class="w-full">Update password</x-landing.button>
        </form>

        <p class="mt-6 text-center text-sm text-atly-ink-soft">
            <a href="{{ route('login') }}" class="font-semibold text-atly-ink hover:text-atly-accent-strong">Back to sign in</a>
        </p>
    </x-auth.card>
</x-layouts.auth>
