<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        $query = Project::query()
            ->forUser($user)
            ->with('workspace:id,name,color')
            ->withCount('tasks')
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

        $projects = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('projects.partials.list', compact('projects'))->render(),
            ]);
        }

        return view('projects.index', [
            'projects' => $projects,
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
        $project->loadCount('tasks');
        $project->load('workspace');

        if ($request->ajax()) {
            return response()->json([
                'project' => $this->projectPayload($project),
            ]);
        }

        return view('projects.show', [
            'project' => $project,
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

    public function destroy(Project $project): RedirectResponse|JsonResponse
    {
        $project->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Project deleted successfully.']);
        }

        return redirect()
            ->route('projects.index')
            ->with('status', 'Project deleted successfully.');
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
            'workspace_id' => $project->workspace_id,
            'workspace' => $project->workspace ? [
                'id' => $project->workspace->id,
                'name' => $project->workspace->name,
                'color' => $project->workspace->color,
            ] : null,
            'tasks_count' => $project->tasks_count ?? $project->tasks()->count(),
            'edit_url' => route('projects.edit', $project),
            'delete_url' => route('projects.destroy', $project),
        ];
    }
}
