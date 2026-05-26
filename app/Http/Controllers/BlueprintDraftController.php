<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlueprintDraftRequest;
use App\Http\Requests\UpdateBlueprintDraftRequest;
use App\Models\BlueprintDraft;
use App\Models\Workspace;
use App\Services\BlueprintDraftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BlueprintDraftController extends Controller
{
    public function __construct(private readonly BlueprintDraftService $service)
    {
        $this->authorizeResource(BlueprintDraft::class, 'draft');
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $drafts = BlueprintDraft::query()
            ->forUser($user)
            ->with(['members', 'workspace:id,name,color', 'project:id'])
            ->withCount(['members'])
            ->latest()
            ->paginate(20);

        return view('blueprints.drafts.index', [
            'drafts' => $drafts,
        ]);
    }

    public function show(Request $request, BlueprintDraft $draft): View
    {
        $draft->load(['members', 'workspace:id,name,color', 'project:id']);

        return view('blueprints.drafts.show', [
            'draft' => $draft,
            'workspaces' => Workspace::query()->forUser($request->user())->orderBy('name')->get(['id', 'name', 'color']),
        ]);
    }

    public function store(StoreBlueprintDraftRequest $request): JsonResponse
    {
        $draft = $this->service->save($request->user(), $request->validated());

        return response()->json([
            'message' => 'Draft saved.',
            'draft' => $this->payload($draft),
            'redirect' => route('blueprint.drafts.show', $draft),
        ]);
    }

    public function update(UpdateBlueprintDraftRequest $request, BlueprintDraft $draft): JsonResponse
    {
        $draft = $this->service->update($draft, $request->validated());

        return response()->json([
            'message' => 'Draft updated.',
            'draft' => $this->payload($draft),
        ]);
    }

    public function invite(Request $request, BlueprintDraft $draft): JsonResponse
    {
        $this->authorize('invite', $draft);

        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $draft = $this->service->sendInvitations($draft, $data['message'] ?? null);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Some invitations could not be sent.',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'message' => 'Invitations sent. The project will appear in Projects once everyone accepts.',
            'draft' => $this->payload($draft),
        ]);
    }

    public function finalize(Request $request, BlueprintDraft $draft): JsonResponse
    {
        $this->authorize('finalize', $draft);

        $project = $this->service->finalize($draft);

        return response()->json([
            'message' => 'Project created from blueprint.',
            'redirect' => route('projects.show', $project),
        ]);
    }

    public function destroy(BlueprintDraft $draft): RedirectResponse|JsonResponse
    {
        $this->service->delete($draft);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['message' => 'Draft deleted.']);
        }

        return redirect()
            ->route('blueprint.drafts.index')
            ->with('status', 'Draft deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(BlueprintDraft $draft): array
    {
        $draft->loadMissing(['members', 'workspace:id,name,color']);

        return [
            'id' => $draft->id,
            'name' => $draft->name,
            'status' => $draft->status->value,
            'status_label' => $draft->status->label(),
            'assignment_type' => $draft->assignment_type,
            'workspace' => $draft->workspace ? [
                'id' => $draft->workspace->id,
                'name' => $draft->workspace->name,
                'color' => $draft->workspace->color,
            ] : null,
            'accepted_count' => $draft->acceptedMembersCount(),
            'pending_count' => $draft->pendingMembersCount(),
            'members_count' => $draft->members->count(),
            'show_url' => route('blueprint.drafts.show', $draft),
        ];
    }
}
