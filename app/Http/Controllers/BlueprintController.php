<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\GenerateBlueprintRequest;
use App\Http\Requests\StoreBlueprintRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\Workspace;
use App\Services\AI\AIModelRegistry;
use App\Services\AI\BlueprintGenerator;
use App\Services\AI\DocumentTextExtractor;
use App\Services\AI\Exceptions\AIRequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class BlueprintController extends Controller
{
    public function __construct(
        private readonly BlueprintGenerator $generator,
        private readonly DocumentTextExtractor $extractor,
        private readonly AIModelRegistry $registry,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $models = $this->registry->available();

        return view('blueprints.index', [
            'workspaces' => Workspace::query()->forUser($user)->orderBy('name')->get(['id', 'name', 'color']),
            'models' => $models,
            'hasAvailableModels' => $models !== [],
            'donationUrl' => $this->registry->donationUrl(),
        ]);
    }

    public function generate(GenerateBlueprintRequest $request): JsonResponse
    {
        try {
            $context = [
                'description' => trim((string) $request->input('description', '')),
                'assignment_type' => $request->input('assignment_type', 'individual'),
                'start_date' => $request->date('start_date')?->toDateString(),
                'end_date' => $request->date('end_date')?->toDateString(),
                'members' => $this->cleanMembers($request->input('members', [])),
            ];

            if ($request->hasFile('document')) {
                [$ext, $text] = $this->extractor->extract($request->file('document'));
                $context['document_text'] = $text;
                $context['document_extension'] = $ext;
            }

            $blueprint = $this->generator->generate(
                (string) $request->input('model'),
                $context,
            );

            return response()->json([
                'blueprint' => $blueprint,
            ]);
        } catch (AIRequestException|RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Something went wrong while generating the plan. Please try again.',
            ], 500);
        }
    }

    public function store(StoreBlueprintRequest $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $project = DB::transaction(function () use ($user, $data): Project {
            /** @var Project $project */
            $project = $user->projects()->create([
                'name' => $data['project']['name'],
                'description' => $data['project']['description'] ?? null,
                'color' => $data['project']['color'] ?? 'sky',
                'workspace_id' => $data['workspace_id'] ?? null,
            ]);

            $now = now();

            $rows = collect($data['tasks'])->map(fn (array $task): array => [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'title' => $task['title'],
                'description' => $task['description'] ?? null,
                'status' => TaskStatus::Pending->value,
                'priority' => $task['priority'],
                'start_date' => $task['start_date'],
                'due_date' => $task['due_date'],
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            Task::insert($rows);

            return $project;
        });

        $message = 'Project created with '.count($data['tasks']).' tasks from the AI blueprint.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => $message,
                'redirect' => route('projects.show', $project),
            ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('status', $message);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cleanMembers(mixed $members): array
    {
        if (! is_array($members)) {
            return [];
        }

        $clean = [];

        foreach ($members as $member) {
            if (! is_array($member)) {
                continue;
            }

            $name = trim((string) ($member['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $clean[] = [
                'name' => $name,
                'skill' => trim((string) ($member['skill'] ?? '')),
                'split' => isset($member['split']) && $member['split'] !== '' ? (float) $member['split'] : null,
            ];
        }

        return $clean;
    }
}
