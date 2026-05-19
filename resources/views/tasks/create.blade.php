<x-layouts.dashboard title="New task">
    <div class="mx-auto max-w-2xl rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly sm:p-8">
        <h2 class="font-display text-xl font-bold text-atly-ink">Create task</h2>
        <p class="mt-1 text-sm text-atly-ink-soft">Add a new task to your workspace.</p>

        <form method="POST" action="{{ route('tasks.store') }}" class="mt-6 space-y-6">
            @csrf
            @include('tasks._form', ['statuses' => $statuses, 'priorities' => $priorities])

            <div class="flex flex-wrap gap-3">
                <x-landing.button type="submit">Create task</x-landing.button>
                <x-landing.button :href="route('tasks.index')" variant="secondary">Cancel</x-landing.button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
