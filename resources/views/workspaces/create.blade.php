<x-layouts.dashboard title="New workspace">
    <div class="mx-auto max-w-2xl rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly sm:p-8">
        <h2 class="font-display text-xl font-bold text-atly-ink">Create workspace</h2>
        <p class="mt-1 text-sm text-atly-ink-soft">A workspace groups related projects together.</p>

        <form method="POST" action="{{ route('workspaces.store') }}" class="mt-6 space-y-6">
            @csrf
            @include('workspaces._form')

            <div class="flex flex-wrap gap-3">
                <x-landing.button type="submit">Create workspace</x-landing.button>
                <x-landing.button :href="route('workspaces.index')" variant="secondary">Cancel</x-landing.button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
