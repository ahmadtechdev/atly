@php
    use App\Enums\TaskPriority;
    use App\Enums\TaskStatus;

    $statuses = TaskStatus::cases();
    $priorities = TaskPriority::cases();
@endphp

<div id="task-quick-modal" class="fixed inset-0 z-50 hidden overflow-y-auto overscroll-contain" aria-hidden="true">
    <div data-close-task-modal class="fixed inset-0 bg-atly-ink/50 backdrop-blur-sm"></div>
    <div class="pointer-events-none relative flex min-h-full items-start justify-center p-4 sm:items-center sm:p-6">
        <div class="pointer-events-auto w-full max-w-lg rounded-atly-lg border border-atly-border bg-atly-card p-5 shadow-atly-lg sm:p-7" role="dialog" aria-modal="true" aria-labelledby="task-modal-title">
            <div class="mb-6 flex items-center justify-between">
                <h2 id="task-modal-title" class="font-display text-xl font-bold text-atly-ink">New task</h2>
                <button type="button" data-close-task-modal class="rounded-lg p-2 text-atly-ink-soft hover:bg-atly-muted hover:text-atly-ink" aria-label="Close">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form id="task-quick-form" method="POST" action="{{ route('tasks.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="modal" value="1">
                @include('tasks._form', ['statuses' => $statuses, 'priorities' => $priorities])
                <div id="task-modal-errors" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"></div>
                <div class="flex flex-wrap gap-3">
                    <x-landing.button type="submit">Create task</x-landing.button>
                    <x-landing.button type="button" variant="secondary" data-close-task-modal>Cancel</x-landing.button>
                </div>
            </form>
        </div>
    </div>
</div>
