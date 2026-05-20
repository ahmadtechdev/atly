@props(['task' => null, 'statuses', 'priorities'])

@php
    $selectedProject = $task?->project_id ? \App\Models\Project::query()->with('workspace:id,name')->whereKey($task->project_id)->first() : null;
    $selectedProjectId = old('project_id', $task?->project_id);
    $selectedProjectLabel = $selectedProject?->name ?? 'No project';
    $selectedProjectColor = $selectedProject?->color ?? '';
@endphp

<div class="space-y-5">
    <x-auth.input name="title" label="Title" :value="$task?->title" placeholder="What needs to be done?" />

    <div>
        <label class="mb-1.5 block text-sm font-medium text-atly-ink">Project (optional)</label>
        <div
            data-searchable-picker
            data-search-url="{{ route('projects.search') }}"
            data-name="project_id"
            data-empty-label="No project"
            data-placeholder="No project"
            data-selected-id="{{ $selectedProjectId }}"
            data-selected-label="{{ $selectedProjectLabel }}"
            data-selected-color="{{ $selectedProjectColor }}"
        ></div>
        @error('project_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="description" class="mb-1.5 block text-sm font-medium text-atly-ink">Description</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            placeholder="Add details (optional)"
            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 transition focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
        >{{ old('description', $task?->description) }}</textarea>
        @error('description')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="status" class="mb-1.5 block text-sm font-medium text-atly-ink">Status</label>
            <select id="status" name="status" class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $task?->status?->value ?? 'pending') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="priority" class="mb-1.5 block text-sm font-medium text-atly-ink">Priority</label>
            <select id="priority" name="priority" class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30">
                @foreach ($priorities as $priority)
                    <option value="{{ $priority->value }}" @selected(old('priority', $task?->priority?->value ?? 'medium') === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </select>
            @error('priority')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-auth.input name="start_date" label="Start date" type="date" :value="old('start_date', $task?->start_date?->format('Y-m-d') ?? now()->format('Y-m-d'))" />
        <x-auth.input name="due_date" label="Due date (optional)" type="date" :value="old('due_date', $task?->due_date?->format('Y-m-d'))" :required="false" />
    </div>

    <div>
        <label for="attachments" class="mb-1.5 block text-sm font-medium text-atly-ink">Attachments (optional)</label>
        <input
            id="attachments"
            name="attachments[]"
            type="file"
            multiple
            accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip"
            class="w-full rounded-xl border border-dashed border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink file:mr-4 file:rounded-lg file:border-0 file:bg-atly-muted file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-atly-ink"
        />
        <p class="mt-1 text-xs text-atly-ink-soft">Images or documents, up to 10 MB each (max 5 files).</p>
        @error('attachments')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('attachments.*')
            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @if ($task?->attachments->isNotEmpty())
            <ul class="mt-3 space-y-2">
                @foreach ($task->attachments as $attachment)
                    <li class="flex items-center justify-between rounded-lg border border-atly-border bg-atly-muted/30 px-3 py-2 text-sm">
                        <a href="{{ $attachment->url() }}" target="_blank" class="truncate text-atly-ink hover:underline">{{ $attachment->original_name }}</a>
                        <label class="flex shrink-0 items-center gap-1.5 text-xs text-rose-600">
                            <input type="checkbox" name="remove_attachments[]" value="{{ $attachment->id }}" class="rounded border-atly-border">
                            Remove
                        </label>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
