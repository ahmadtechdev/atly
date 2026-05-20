<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Project::class, 'project');
    }

    public function index(Request $request): View|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $baseQuery = fn () => Project::query()
            ->forUser($user)
            ->with('workspace:id,name,color')
            ->withCount(['tasks', 'tasks as completed_tasks_count' => function (Builder $q) {
                $q->where('status', TaskStatus::Completed->value);
            }])
            ->when($request->filled('workspace_id'), function (Builder $q) use ($request) {
                $value = $request->input('workspace_id');

                if ($value === 'none' || $value === '0') {
                    $q->whereNull('workspace_id');
                } else {
                    $q->where('workspace_id', (int) $value);
                }
            })
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $search = $request->string('search');
                $q->where(function (Builder $b) use ($search) {
                    $b->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        $activeProjects = $baseQuery()->active()->get();
        $completedProjects = $baseQuery()->completed()->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('projects.partials.list', [
                    'activeProjects' => $activeProjects,
                    'completedProjects' => $completedProjects,
                ])->render(),
            ]);
        }

        return view('projects.index', [
            'activeProjects' => $activeProjects,
            'completedProjects' => $completedProjects,
            'totalCount' => $activeProjects->count() + $completedProjects->count(),
        ]);
    }

    public function create(): View
    {
        return view('projects.create', [
            'workspaces' => Workspace::query()->forUser(auth()->user())->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse|JsonResponse
    {
        $project = $request->user()->projects()->create($request->validated());

        if ($request->ajax() || $request->boolean('modal')) {
            return response()->json([
                'message' => 'Project created successfully.',
                'project' => $this->projectPayload($project),
                'redirect' => route('projects.index'),
            ]);
        }

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project created successfully.');
    }

    public function show(Request $request, Project $project): View|JsonResponse
    {
        $project->loadCount([
            'tasks',
            'tasks as completed_tasks_count' => function (Builder $q) {
                $q->where('status', TaskStatus::Completed->value);
            },
        ]);
        $project->load('workspace');

        $tasks = $project->tasks()
            ->with(['user', 'timeEntries'])
            ->latest()
            ->limit(20)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'project' => $this->projectPayload($project),
            ]);
        }

        return view('projects.show', [
            'project' => $project,
            'tasks' => $tasks,
        ]);
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', [
            'project' => $project,
            'workspaces' => Workspace::query()->forUser(auth()->user())->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project updated successfully.');
    }

    public function updateWorkspace(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'workspace_id' => [
                'nullable',
                Rule::exists('workspaces', 'id')->where(fn ($q) => $q->where('user_id', $request->user()->id)),
            ],
        ]);

        $project->update(['workspace_id' => $data['workspace_id'] ?? null]);
        $project->load('workspace');

        return response()->json([
            'project' => [
                'id' => $project->id,
                'workspace_id' => $project->workspace_id,
                'workspace' => $project->workspace ? [
                    'id' => $project->workspace->id,
                    'name' => $project->workspace->name,
                    'color' => $project->workspace->color,
                    'url' => route('workspaces.show', $project->workspace),
                ] : null,
            ],
        ]);
    }

    public function complete(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $project);

        if ($project->hasOutstandingTasks()) {
            throw ValidationException::withMessages([
                'project' => 'Complete all tasks in this project before marking the project complete.',
            ]);
        }

        $project->update([
            'status' => ProjectStatus::Completed->value,
            'completed_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Project marked as complete.',
                'project' => $this->projectPayload($project->fresh()),
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project marked as complete.');
    }

    public function reopen(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $project);

        $project->update([
            'status' => ProjectStatus::Active->value,
            'completed_at' => null,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Project reopened.',
                'project' => $this->projectPayload($project->fresh()),
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project reopened.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse|JsonResponse
    {
        $taskAction = $request->input('task_action', 'unassign');

        DB::transaction(function () use ($project, $taskAction): void {
            if ($taskAction === 'delete') {
                $project->tasks()->get()->each->delete();
            } else {
                $project->tasks()->update(['project_id' => null]);
            }

            $project->delete();
        });

        $message = $taskAction === 'delete'
            ? 'Project and its tasks were deleted.'
            : 'Project deleted. Tasks were kept and unassigned.';

        if ($request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return redirect()
            ->route('projects.index')
            ->with('status', $message);
    }

    public function search(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $search = trim((string) $request->string('search'));

        $projects = Project::query()
            ->forUser($user)
            ->with('workspace:id,name')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'workspace_id', 'color']);

        return response()->json([
            'results' => $projects->map(fn (Project $project) => [
                'id' => $project->id,
                'name' => $project->name,
                'subtitle' => $project->workspace?->name,
                'color' => $project->color,
            ])->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function projectPayload(Project $project): array
    {
        return [
            'id' => $project->id,
            'name' => $project->name,
            'full_name' => $project->fullName(),
            'description' => $project->description,
            'color' => $project->color,
            'status' => $project->status?->value,
            'status_label' => $project->status?->label(),
            'is_completed' => $project->isCompleted(),
            'workspace_id' => $project->workspace_id,
            'workspace' => $project->workspace ? [
                'id' => $project->workspace->id,
                'name' => $project->workspace->name,
                'color' => $project->workspace->color,
            ] : null,
            'tasks_count' => $project->tasks_count ?? $project->tasks()->count(),
            'completed_tasks_count' => $project->completed_tasks_count ?? null,
            'edit_url' => route('projects.edit', $project),
            'delete_url' => route('projects.destroy', $project),
            'complete_url' => route('projects.complete', $project),
            'reopen_url' => route('projects.reopen', $project),
        ];
    }
}
