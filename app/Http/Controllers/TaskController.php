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
use Illuminate\Validation\Rule;
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
        $tasks = $this->filteredQuery($request)->paginate(10)->withQueryString();

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
        $data = $request->safe()->except(['attachments']);
        $data['start_date'] ??= now()->toDateString();

        $task = $request->user()->tasks()->create($data);

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
        $task->load(['attachments', 'user', 'project.workspace']);

        return response()->json([
            'task' => $this->taskPayload($task),
        ]);
    }

    public function start(Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        if ($task->status !== TaskStatus::Pending) {
            return response()->json([
                'message' => 'Only pending tasks can be started.',
            ], 422);
        }

        $task->status = TaskStatus::InProgress;
        $task->save();

        $task->load(['attachments', 'user', 'project.workspace']);

        return response()->json([
            'task' => $this->taskPayload($task),
        ]);
    }

    public function updateProject(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'project_id' => [
                'nullable',
                Rule::exists('projects', 'id')->where(fn ($q) => $q->where('user_id', $request->user()->id)),
            ],
        ]);

        $task->update(['project_id' => $data['project_id'] ?? null]);
        $task->load(['attachments', 'user', 'project.workspace']);

        return response()->json([
            'task' => $this->taskPayload($task),
        ]);
    }

    public function toggleComplete(Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task->status = match ($task->status) {
            TaskStatus::InProgress => TaskStatus::Completed,
            TaskStatus::Completed => TaskStatus::InProgress,
            default => null,
        };

        if ($task->status === null) {
            return response()->json([
                'message' => 'Start the task before marking it complete.',
            ], 422);
        }

        $task->save();

        $task->load(['attachments', 'user', 'project.workspace']);

        return response()->json([
            'task' => $this->taskPayload($task),
        ]);
    }

    public function edit(Task $task): View
    {
        $task->load(['attachments', 'project.workspace']);

        return view('tasks.edit', [
            'task' => $task,
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse
    {
        $data = $request->safe()->except(['attachments', 'remove_attachments']);
        $data['start_date'] ??= now()->toDateString();

        $task->update($data);

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
            ->with(['attachments', 'user', 'project.workspace'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', (int) $request->input('project_id'));
        }

        if ($request->filled('workspace_id')) {
            $workspaceId = (int) $request->input('workspace_id');
            $query->whereHas('project', fn ($q) => $q->where('workspace_id', $workspaceId));
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

    /**
     * @return array<string, mixed>
     */
    private function taskPayload(Task $task): array
    {
        $task->loadMissing(['attachments', 'user', 'project.workspace']);

        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status->value,
            'status_label' => $task->status->label(),
            'status_class' => $task->status->colorClass(),
            'is_pending' => $task->status === TaskStatus::Pending,
            'is_in_progress' => $task->status === TaskStatus::InProgress,
            'is_completed' => $task->status === TaskStatus::Completed,
            'can_complete' => $task->status !== TaskStatus::Pending,
            'priority' => $task->priority->value,
            'priority_label' => $task->priority->label(),
            'priority_class' => $task->priority->colorClass(),
            'priority_dot' => $task->priority->dotClass(),
            'start_date' => $task->start_date?->format('M j, Y'),
            'due_date' => $task->due_date?->format('M j, Y'),
            'is_overdue' => $task->isOverdue(),
            'edit_url' => route('tasks.edit', $task),
            'delete_url' => route('tasks.destroy', $task),
            'start_url' => route('tasks.start', $task),
            'complete_url' => route('tasks.toggle-complete', $task),
            'update_project_url' => route('tasks.update-project', $task),
            'assignee' => [
                'name' => $task->user->name,
                'initials' => $task->user->initials(),
                'avatar_url' => $task->user->avatar_url,
            ],
            'project' => $task->project ? [
                'id' => $task->project->id,
                'name' => $task->project->name,
                'full_name' => $task->project->fullName(),
                'color' => $task->project->color,
                'url' => route('projects.show', $task->project),
                'workspace' => $task->project->workspace ? [
                    'id' => $task->project->workspace->id,
                    'name' => $task->project->workspace->name,
                    'color' => $task->project->workspace->color,
                    'url' => route('workspaces.show', $task->project->workspace),
                ] : null,
            ] : null,
            'attachments' => $task->attachments->map(fn (TaskAttachment $file) => [
                'id' => $file->id,
                'name' => $file->original_name,
                'url' => $file->url(),
                'is_image' => $file->isImage(),
                'size' => number_format($file->size / 1024, 1).' KB',
            ]),
        ];
    }
}
