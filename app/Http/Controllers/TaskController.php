<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use App\Services\TaskAttachmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskAttachmentService $attachmentService,
    ) {
        $this->authorizeResource(Task::class, 'task');
    }

    public function index(Request $request): View|JsonResponse
    {
        $tasks = $this->filteredQuery($request)->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('tasks.partials.list', compact('tasks'))->render(),
            ]);
        }

        return view('tasks.index', [
            'tasks' => $tasks,
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function create(): View
    {
        return view('tasks.create', [
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse|JsonResponse
    {
        $task = $request->user()->tasks()->create($request->safe()->except(['attachments']));

        $this->attachmentService->storeMany($task, $request->file('attachments', []));

        if ($request->ajax() || $request->boolean('modal')) {
            return response()->json([
                'message' => 'Task created successfully.',
                'redirect' => route('tasks.index'),
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task created successfully.');
    }

    public function show(Task $task): JsonResponse
    {
        $task->load('attachments');

        return response()->json([
            'task' => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status->value,
                'status_label' => $task->status->label(),
                'status_class' => $task->status->colorClass(),
                'priority' => $task->priority->value,
                'priority_label' => $task->priority->label(),
                'priority_class' => $task->priority->colorClass(),
                'priority_dot' => $task->priority->dotClass(),
                'start_date' => $task->start_date?->format('M j, Y'),
                'due_date' => $task->due_date?->format('M j, Y'),
                'is_overdue' => $task->isOverdue(),
                'edit_url' => route('tasks.edit', $task),
                'delete_url' => route('tasks.destroy', $task),
                'attachments' => $task->attachments->map(fn (TaskAttachment $file) => [
                    'id' => $file->id,
                    'name' => $file->original_name,
                    'url' => $file->url(),
                    'is_image' => $file->isImage(),
                    'size' => number_format($file->size / 1024, 1).' KB',
                ]),
            ],
        ]);
    }

    public function edit(Task $task): View
    {
        $task->load('attachments');

        return view('tasks.edit', [
            'task' => $task,
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update($request->safe()->except(['attachments', 'remove_attachments']));

        foreach ($request->input('remove_attachments', []) as $attachmentId) {
            $attachment = $task->attachments()->whereKey($attachmentId)->first();

            if ($attachment) {
                $this->attachmentService->delete($attachment);
            }
        }

        $this->attachmentService->storeMany($task, $request->file('attachments', []));

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse|JsonResponse
    {
        $task->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Task deleted successfully.']);
        }

        return redirect()
            ->route('tasks.index')
            ->with('status', 'Task deleted successfully.');
    }

    /**
     * @return Builder<Task>
     */
    private function filteredQuery(Request $request): Builder
    {
        /** @var User $user */
        $user = $request->user();

        $query = Task::query()
            ->forUser($user)
            ->with('attachments')
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
