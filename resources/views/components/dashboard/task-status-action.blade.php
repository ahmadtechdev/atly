@use(App\Enums\TaskStatus)

@props(['task', 'size' => 'md'])

<div data-task-action class="shrink-0">
    @if ($task->status === TaskStatus::Pending)
        <x-dashboard.task-start-toggle :start-url="route('tasks.start', $task)" :size="$size" />
    @else
        <x-dashboard.task-complete-toggle
            :completed="$task->status === TaskStatus::Completed"
            :complete-url="route('tasks.toggle-complete', $task)"
            :size="$size"
        />
    @endif
</div>
