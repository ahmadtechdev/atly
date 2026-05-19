<x-layouts.dashboard title="Edit task">
    <div class="mx-auto max-w-2xl rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly sm:p-8">
        <h2 class="font-display text-xl font-bold text-atly-ink">Edit task</h2>
        <p class="mt-1 text-sm text-atly-ink-soft">Update task details.</p>

        <form method="POST" action="{{ route('tasks.update', $task) }}" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            @include('tasks._form', ['task' => $task, 'statuses' => $statuses, 'priorities' => $priorities])

            <div class="flex flex-wrap gap-3">
                <x-landing.button type="submit">Save changes</x-landing.button>
                <x-landing.button :href="route('tasks.index')" variant="secondary">Cancel</x-landing.button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
