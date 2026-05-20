<x-layouts.dashboard title="Edit project">
    <div class="mx-auto max-w-2xl space-y-4">
        <div class="rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly sm:p-8">
            <h2 class="font-display text-xl font-bold text-atly-ink">Edit project</h2>

            <form method="POST" action="{{ route('projects.update', $project) }}" class="mt-6 space-y-6">
                @csrf
                @method('PUT')
                @include('projects._form', ['project' => $project, 'workspaces' => $workspaces])

                <div class="flex flex-wrap gap-3">
                    <x-landing.button type="submit">Save changes</x-landing.button>
                    <x-landing.button :href="route('projects.index')" variant="secondary">Cancel</x-landing.button>
                </div>
            </form>
        </div>

        <form method="POST" action="{{ route('projects.destroy', $project) }}" data-confirm="Delete this project? Tasks inside will keep their data but lose their project link." class="rounded-atly-lg border border-rose-200/70 bg-rose-50/50 p-4 dark:border-rose-900/40 dark:bg-rose-950/20">
            @csrf
            @method('DELETE')
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-rose-700 dark:text-rose-200">Delete this project. Existing tasks become un-grouped.</p>
                <button type="submit" class="inline-flex rounded-xl border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-100 dark:bg-rose-950/40 dark:hover:bg-rose-900/60">Delete project</button>
            </div>
        </form>
    </div>
</x-layouts.dashboard>
