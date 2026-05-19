@props(['task' => null, 'statuses', 'priorities'])

<div class="space-y-5">
    <x-auth.input name="title" label="Title" :value="$task?->title" placeholder="What needs to be done?" />

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
                    <option value="{{ $status->value }}" @selected(old('status', $task?->status?->value) === $status->value)>{{ $status->label() }}</option>
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
                    <option value="{{ $priority->value }}" @selected(old('priority', $task?->priority?->value) === $priority->value)>{{ $priority->label() }}</option>
                @endforeach
            </select>
            @error('priority')
                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <x-auth.input name="due_date" label="Due date" type="date" :value="old('due_date', $task?->due_date?->format('Y-m-d'))" :required="false" />
</div>
