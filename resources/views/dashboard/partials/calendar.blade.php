@php
    /** @var \Carbon\Carbon $calendarMonth */
    /** @var array $calendarWeeks */
    $prevMonth = $calendarMonth->copy()->subMonth()->format('Y-m');
    $nextMonth = $calendarMonth->copy()->addMonth()->format('Y-m');
@endphp

<div class="mb-4 flex items-center justify-between">
    <div>
        <h2 class="font-display text-lg font-semibold text-atly-ink">Calendar</h2>
        <p class="text-sm text-atly-ink-soft" data-calendar-label>{{ $calendarMonth->format('F Y') }}</p>
    </div>
    <div class="flex gap-2">
        <a
            href="{{ route('dashboard', ['month' => $prevMonth]) }}"
            data-calendar-nav
            data-month="{{ $prevMonth }}"
            class="rounded-lg border border-atly-border px-2 py-1 text-sm text-atly-ink hover:bg-atly-muted"
            aria-label="Previous month"
        >←</a>
        <a
            href="{{ route('dashboard', ['month' => $nextMonth]) }}"
            data-calendar-nav
            data-month="{{ $nextMonth }}"
            class="rounded-lg border border-atly-border px-2 py-1 text-sm text-atly-ink hover:bg-atly-muted"
            aria-label="Next month"
        >→</a>
    </div>
</div>

<div class="grid grid-cols-7 gap-1 text-center text-xs font-medium text-atly-ink-soft">
    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
        <div class="py-1">{{ $day }}</div>
    @endforeach
</div>

@foreach ($calendarWeeks as $week)
    <div class="mt-1 grid grid-cols-7 gap-1">
        @foreach ($week as $day)
            <div @class([
                'min-h-14 rounded-lg border p-1 text-left',
                'border-atly-border bg-atly-surface/50' => $day['in_month'],
                'border-transparent opacity-40' => ! $day['in_month'],
                'ring-2 ring-atly-accent' => $day['is_today'],
            ])>
                <span @class(['text-xs font-medium', 'text-atly-ink' => $day['in_month'], 'text-atly-ink-soft' => ! $day['in_month']])>{{ $day['date']->day }}</span>
                @if ($day['tasks']->isNotEmpty())
                    <div class="mt-1 space-y-0.5">
                        @foreach ($day['tasks']->take(2) as $task)
                            <p class="truncate rounded bg-atly-accent/25 px-1 text-[10px] text-atly-ink" title="{{ $task->title }}">{{ $task->title }}</p>
                        @endforeach
                        @if ($day['tasks']->count() > 2)
                            <p class="text-[10px] text-atly-ink-soft">+{{ $day['tasks']->count() - 2 }} more</p>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endforeach
