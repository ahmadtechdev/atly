<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskAttachmentService;
use App\Services\TaskPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskAttachmentService $attachmentService,
    ) {
        $this->authorizeResource(Task::class, 'task');
    }

    public function search(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $search = trim((string) $request->string('search'));

        $tasks = Task::query()
            ->accessibleFor($user)
            ->with('project:id,name,color')
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', (int) $request->input('project_id')))
            ->when($search !== '', fn ($q) => $q->where('title', 'like', "%{$search}%"))
            ->when(! $request->boolean('include_completed'), fn ($q) => $q->where('status', '!=', TaskStatus::Completed->value))
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'project_id']);

        return response()->json([
            'results' => $tasks->map(fn (Task $task) => [
                'id' => $task->id,
                'name' => $task->title,
                'subtitle' => $task->project?->name,
                'color' => $task->project?->color,
            ])->values(),
        ]);
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
            'project_id' => ['nullable', 'integer'],
        ]);

        if ($data['project_id'] !== null) {
            $project = Project::query()
                ->accessibleFor($request->user())
                ->find($data['project_id']);

            if ($project === null) {
                throw ValidationException::withMessages([
                    'project_id' => 'You do not have access to that project.',
                ]);
            }
        }

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
            ->accessibleFor($user)
            ->with(['attachments', 'user', 'project.workspace', 'timeEntries', 'collaborators'])
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
        return TaskPresenter::payload($task);
    }
}
