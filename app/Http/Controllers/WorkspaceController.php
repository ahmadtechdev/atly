<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Workspace::class, 'workspace');
    }

    public function index(Request $request): View|JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $workspaces = Workspace::query()
            ->accessibleFor($user)
            ->withCount(['projects', 'tasks'])
            ->with(['projects' => fn ($q) => $q->orderBy('name')->limit(5)->withCount('tasks')])
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $search = $request->string('search');
                $q->where(function (Builder $b) use ($search) {
                    $b->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('workspaces.partials.list', compact('workspaces'))->render(),
            ]);
        }

        return view('workspaces.index', [
            'workspaces' => $workspaces,
        ]);
    }

    public function create(): View
    {
        return view('workspaces.create');
    }

    public function store(StoreWorkspaceRequest $request): RedirectResponse|JsonResponse
    {
        $workspace = $request->user()->workspaces()->create($request->validated());

        if ($request->ajax() || $request->boolean('modal')) {
            return response()->json([
                'message' => 'Workspace created successfully.',
                'workspace' => $this->workspacePayload($workspace),
                'redirect' => route('workspaces.index'),
            ]);
        }

        return redirect()
            ->route('workspaces.index')
            ->with('status', 'Workspace created successfully.');
    }

    public function show(Request $request, Workspace $workspace): View|JsonResponse
    {
        $workspace->load([
            'projects' => fn ($q) => $q->orderBy('name')->withCount('tasks'),
            'members',
        ]);
        $workspace->loadCount(['projects', 'tasks']);

        if ($request->ajax()) {
            return response()->json([
                'workspace' => $this->workspacePayload($workspace),
            ]);
        }

        return view('workspaces.show', [
            'workspace' => $workspace,
        ]);
    }

    public function edit(Workspace $workspace): View
    {
        return view('workspaces.edit', [
            'workspace' => $workspace,
        ]);
    }

    public function update(UpdateWorkspaceRequest $request, Workspace $workspace): RedirectResponse
    {
        $workspace->update($request->validated());

        return redirect()
            ->route('workspaces.index')
            ->with('status', 'Workspace updated successfully.');
    }

    public function destroy(Workspace $workspace): RedirectResponse|JsonResponse
    {
        $workspace->delete();

        if (request()->ajax()) {
            return response()->json(['message' => 'Workspace deleted successfully.']);
        }

        return redirect()
            ->route('workspaces.index')
            ->with('status', 'Workspace deleted successfully.');
    }

    public function search(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $search = trim((string) $request->string('search'));

        $workspaces = Workspace::query()
            ->accessibleFor($user)
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'color']);

        return response()->json([
            'results' => $workspaces->map(fn (Workspace $workspace) => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'subtitle' => null,
                'color' => $workspace->color,
            ])->values(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function workspacePayload(Workspace $workspace): array
    {
        return [
            'id' => $workspace->id,
            'name' => $workspace->name,
            'description' => $workspace->description,
            'color' => $workspace->color,
            'projects_count' => $workspace->projects_count ?? $workspace->projects()->count(),
            'tasks_count' => $workspace->tasks_count ?? $workspace->tasks()->count(),
            'edit_url' => route('workspaces.edit', $workspace),
            'delete_url' => route('workspaces.destroy', $workspace),
        ];
    }
}
