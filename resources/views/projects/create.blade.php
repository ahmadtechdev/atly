<x-layouts.dashboard title="New project">
    <div class="mx-auto max-w-2xl rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly sm:p-8">
        <h2 class="font-display text-xl font-bold text-atly-ink">Create project</h2>
        <p class="mt-1 text-sm text-atly-ink-soft">Group related tasks together. Projects can have sub-projects too.</p>

        <form method="POST" action="{{ route('projects.store') }}" class="mt-6 space-y-6">
            @csrf
            @include('projects._form', ['workspaces' => $workspaces])

            <div class="flex flex-wrap gap-3">
                <x-landing.button type="submit">Create project</x-landing.button>
                <x-landing.button :href="route('projects.index')" variant="secondary">Cancel</x-landing.button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
