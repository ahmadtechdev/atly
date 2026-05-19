<x-layouts.app title="Dashboard">
    <div class="rounded-atly-lg border border-atly-border bg-atly-card p-10 shadow-atly">
        <h1 class="font-display text-3xl font-bold text-atly-ink">
            Welcome to your {{ config('atly.name') }} dashboard
        </h1>
        <p class="mt-3 text-atly-ink-soft">
            Hi {{ auth()->user()->name }} — you're signed in. Task features will appear here soon.
        </p>
    </div>
</x-layouts.app>
