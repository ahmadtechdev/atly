@php
    use App\Models\TimeEntry;
@endphp

<x-layouts.dashboard title="Time Tracker">
    <div class="mx-auto max-w-3xl space-y-6">
        <section class="rounded-atly-lg border border-atly-border bg-atly-card shadow-atly">
            <div class="flex flex-col items-center justify-center gap-2 px-6 py-10 sm:py-12">
                <div
                    id="time-tracker-clock"
                    class="font-display text-5xl font-bold tracking-wider text-atly-ink sm:text-6xl"
                    aria-live="polite"
                >
                    --:--:--
                </div>
                <div class="flex items-center gap-2 text-xs text-atly-ink-soft">
                    <span id="time-tracker-date">{{ now()->format('Y-m-d') }}</span>
                    <span class="rounded bg-atly-muted/50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide">{{ now()->format('T') }}</span>
                </div>
                <p class="mt-1 text-xs text-atly-ink-soft">Today: <span class="font-semibold text-atly-ink">{{ TimeEntry::formatSeconds($totalToday) }}</span> tracked</p>
            </div>
        </section>

        @if ($running)
            <section class="rounded-atly-lg border border-emerald-200 bg-emerald-50/40 p-6 shadow-atly dark:border-emerald-900/50 dark:bg-emerald-950/20 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-300">
                            <span class="relative inline-flex size-2">
                                <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-75"></span>
                                <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                            </span>
                            Tracking now
                        </div>
                        <h2 class="mt-2 truncate font-display text-xl font-bold text-atly-ink">{{ $running->task->title ?? 'Untitled task' }}</h2>
                        @if ($running->task?->project)
                            <p class="mt-1 inline-flex items-center gap-1.5 text-xs text-atly-ink-soft">
                                <span @class([
                                    'size-2 rounded-full',
                                    'bg-'.$running->task->project->color.'-500' => $running->task->project->color,
                                    'bg-atly-ink-soft/40' => ! $running->task->project->color,
                                ])></span>
                                {{ $running->task->project->fullName() }}
                            </p>
                        @endif
                        @if ($running->description)
                            <p class="mt-3 text-sm text-atly-ink-soft">{{ $running->description }}</p>
                        @endif
                        <p class="mt-3 text-xs text-atly-ink-soft">Started at {{ $running->started_at->format('g:i A') }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-3">
                        <div
                            class="font-display text-3xl font-bold tabular-nums text-atly-ink sm:text-4xl"
                            data-running-timer
                            data-started-at-ms="{{ $running->started_at->getTimestampMs() }}"
                        >0:00:00</div>
                        <form method="POST" action="{{ route('time-tracker.stop', $running) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 0 1 7.5 5.25h9a2.25 2.25 0 0 1 2.25 2.25v9a2.25 2.25 0 0 1-2.25 2.25h-9a2.25 2.25 0 0 1-2.25-2.25v-9Z" /></svg>
                                Stop tracking
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        @else
            <section class="rounded-atly-lg border border-atly-border bg-atly-card p-6 shadow-atly sm:p-8">
                <h2 class="font-display text-lg font-bold text-atly-ink">Start New Session</h2>
                <p class="mt-1 text-xs text-atly-ink-soft">Pick a task and start tracking your time on it.</p>

                <form method="POST" action="{{ route('time-tracker.start') }}" class="mt-5 space-y-4">
                    @csrf

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-atly-ink">Task <span class="text-rose-600">*</span></label>
                        <div
                            data-searchable-picker
                            data-search-url="{{ route('tasks.search') }}"
                            data-name="task_id"
                            data-empty-label="Choose a task"
                            data-placeholder="Choose a task"
                        ></div>
                        @error('task_id')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="description" class="mb-1.5 block text-sm font-medium text-atly-ink">Description (Optional)</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="What are you working on?"
                            class="w-full rounded-xl border border-atly-border bg-atly-surface px-4 py-3 text-sm text-atly-ink placeholder:text-atly-ink-soft/60 focus:border-atly-accent focus:outline-none focus:ring-2 focus:ring-atly-accent/30"
                        >{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex justify-center">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                            Start Tracking
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <section class="rounded-atly-lg border border-atly-border bg-atly-card shadow-atly">
            <header class="border-b border-atly-border px-6 py-4">
                <h2 class="font-display text-sm font-semibold uppercase tracking-wide text-atly-ink-soft">Recent sessions</h2>
            </header>
            @if ($recent->isEmpty())
                <p class="px-6 py-10 text-center text-sm text-atly-ink-soft">No sessions yet. Start tracking to see them here.</p>
            @else
                <ul class="divide-y divide-atly-border">
                    @foreach ($recent as $entry)
                        <li class="flex items-center gap-4 px-6 py-3 text-sm">
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-atly-ink">{{ $entry->task->title ?? '—' }}</p>
                                <p class="mt-0.5 flex items-center gap-2 text-xs text-atly-ink-soft">
                                    @if ($entry->task?->project)
                                        <span class="inline-flex items-center gap-1">
                                            <span @class([
                                                'size-1.5 rounded-full',
                                                'bg-'.$entry->task->project->color.'-500' => $entry->task->project->color,
                                                'bg-atly-ink-soft/40' => ! $entry->task->project->color,
                                            ])></span>
                                            {{ $entry->task->project->name }}
                                        </span>
                                    @endif
                                    <span>{{ $entry->started_at->format('M j, g:i A') }}</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold tabular-nums text-atly-ink">{{ TimeEntry::formatSeconds($entry->elapsedSeconds()) }}</p>
                            </div>
                            <form method="POST" action="{{ route('time-tracker.destroy', $entry) }}" data-confirm="Delete this time entry?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg p-1.5 text-atly-ink-soft transition hover:bg-atly-muted hover:text-rose-600" aria-label="Delete entry">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
</x-layouts.dashboard>
