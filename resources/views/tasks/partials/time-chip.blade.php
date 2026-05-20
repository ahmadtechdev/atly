@use(App\Enums\TaskStatus)
@use(App\Models\TimeEntry)
@props(['task', 'size' => 'sm'])

@php
    $running = $task->runningTimeEntry();
    $totalSeconds = $task->totalTrackedSeconds();
    $hasEntries = $task->timeEntries->isNotEmpty();
    $baseSeconds = (int) $task->timeEntries
        ->filter(fn (TimeEntry $entry) => ! $entry->isRunning())
        ->sum(fn (TimeEntry $entry) => $entry->elapsedSeconds());
    $canTrack = $task->status !== TaskStatus::Completed;
    $sizeClasses = $size === 'lg'
        ? 'px-3 py-1.5 text-xs'
        : 'px-2 py-0.5 text-[10px]';
    $iconSize = $size === 'lg' ? 'size-3.5' : 'size-3';
@endphp

<span
    class="task-time-chip inline-flex"
    data-task-time-chip
    data-task-id="{{ $task->id }}"
    @if ($task->status !== TaskStatus::Completed)
        data-track-start-url="{{ route('time-tracker.start') }}"
    @endif
>
    @if ($running)
        <button
            type="button"
            data-track-toggle
            data-track-action="stop"
            data-track-url="{{ route('time-tracker.stop', $running) }}"
            data-task-time-running
            data-started-at-ms="{{ $running->started_at->getTimestampMs() }}"
            data-base-seconds="{{ $baseSeconds }}"
            class="group/track inline-flex items-center gap-1 rounded-full bg-emerald-100 ring-1 ring-emerald-300 font-medium text-emerald-800 transition hover:bg-emerald-200 hover:ring-emerald-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-800 {{ $sizeClasses }}"
            title="Stop tracking"
            aria-label="Stop tracking"
        >
            <span class="relative inline-flex size-1.5">
                <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-500 opacity-75"></span>
                <span class="relative inline-flex size-1.5 rounded-full bg-emerald-500"></span>
            </span>
            <span data-task-time-label class="tabular-nums">{{ TimeEntry::formatSeconds($totalSeconds) }}</span>
            <svg class="{{ $iconSize }} opacity-70 group-hover/track:opacity-100" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <rect x="6" y="6" width="12" height="12" rx="1.5"/>
            </svg>
        </button>
    @elseif ($canTrack)
        <button
            type="button"
            data-track-toggle
            data-track-action="start"
            data-track-url="{{ route('time-tracker.start') }}"
            data-task-id="{{ $task->id }}"
            class="group/track inline-flex items-center gap-1 rounded-full border border-dashed border-atly-border bg-transparent font-medium text-atly-ink-soft transition hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/40 dark:hover:bg-emerald-950/30 dark:hover:text-emerald-200 {{ $sizeClasses }}"
            title="{{ $hasEntries ? 'Resume tracking' : 'Start tracking' }}"
            aria-label="{{ $hasEntries ? 'Resume tracking' : 'Start tracking' }}"
        >
            <svg class="{{ $iconSize }}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8 5.14v13.72c0 .79.87 1.27 1.54.84l10.79-6.86c.62-.39.62-1.29 0-1.68L9.54 4.3C8.87 3.87 8 4.35 8 5.14z"/>
            </svg>
            @if ($hasEntries)
                <span class="tabular-nums">{{ TimeEntry::formatSeconds($totalSeconds) }}</span>
            @else
                <span>Track</span>
            @endif
        </button>
    @elseif ($hasEntries)
        <span
            class="inline-flex items-center gap-1 rounded-full bg-atly-muted/60 font-medium text-atly-ink-soft {{ $sizeClasses }}"
            title="Time tracked"
        >
            <svg class="{{ $iconSize }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            <span class="tabular-nums">{{ TimeEntry::formatSeconds($totalSeconds) }}</span>
        </span>
    @else
        <span class="inline-flex items-center gap-1 italic text-atly-ink-soft/70 {{ $sizeClasses }}" title="No time tracked">
            Not tracked
        </span>
    @endif
</span>
