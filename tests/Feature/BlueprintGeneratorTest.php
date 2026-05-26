<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Services\AI\AIClientFactory;
use App\Services\AI\AIUsageTracker;
use App\Services\AI\Contracts\AIClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();

    config()->set('ai.donation_url', 'https://example.com/donate');

    config()->set('ai.providers', [
        'openai' => ['api_key' => 'sk-test-openai'],
        'gemini' => ['api_key' => null],
        'claude' => ['api_key' => null],
    ]);

    config()->set('ai.models', [
        'openai-gpt-4o-mini' => [
            'provider' => 'openai',
            'model' => 'gpt-4o-mini',
            'label' => 'GPT-4o Mini',
            'tagline' => 'Fast',
            'enabled' => true,
            'monthly_limit' => 5,
        ],
        'gemini-flash' => [
            'provider' => 'gemini',
            'model' => 'gemini-3.5-flash',
            'label' => 'Gemini Flash',
            'tagline' => '',
            'enabled' => true,
            'monthly_limit' => null,
        ],
        'claude-haiku' => [
            'provider' => 'claude',
            'model' => 'claude-3-5-haiku-latest',
            'label' => 'Claude Haiku',
            'tagline' => '',
            'enabled' => false,
            'monthly_limit' => null,
        ],
    ]);
});

function fakeAiClient(array $payload): AIClient
{
    return new class($payload) implements AIClient
    {
        public function __construct(private readonly array $payload) {}

        public function generateJson(string $systemPrompt, string $userPrompt, string $model): array
        {
            return $this->payload;
        }
    };
}

function bindFakeClient(array $blueprintJson): void
{
    test()->mock(AIClientFactory::class, function ($mock) use ($blueprintJson) {
        $mock->shouldReceive('forModel')->andReturn(fakeAiClient($blueprintJson));
    });
}

it('only lists models that are enabled and have a configured api key', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('blueprint.index'));

    $response->assertOk();
    $models = $response->viewData('models');

    expect(collect($models)->pluck('id')->all())->toEqual(['openai-gpt-4o-mini']);
    expect($response->viewData('hasAvailableModels'))->toBeTrue();
});

it('shows the donation state when no models are available', function () {
    config()->set('ai.providers.openai.api_key', null);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('blueprint.index'));

    $response->assertOk()
        ->assertSee('AI Blueprint is taking a breather')
        ->assertSee('Chip in for AI credits');

    expect($response->viewData('hasAvailableModels'))->toBeFalse();
});

it('generates a draft using the selected model', function () {
    bindFakeClient([
        'project' => ['name' => 'Bakery Shop', 'description' => 'Mini ecommerce.'],
        'tasks' => [[
            'title' => 'Design data model',
            'description' => 'Schema',
            'milestone' => 'Discovery',
            'skill_required' => 'Backend',
            'estimated_hours' => 6,
            'priority' => 'high',
            'status' => 'pending',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'assigned_to' => null,
            'depends_on' => [],
        ]],
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('blueprint.generate'), [
        'model' => 'openai-gpt-4o-mini',
        'description' => 'Build a small bakery ecommerce.',
        'assignment_type' => 'individual',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addWeeks(2)->toDateString(),
    ]);

    $response->assertOk()->assertJsonPath('blueprint.project.name', 'Bakery Shop');

    expect(app(AIUsageTracker::class)->count('openai-gpt-4o-mini'))->toBe(1);
});

it('rejects generation when the chosen model is unavailable', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('blueprint.generate'), [
        'model' => 'claude-haiku',
        'description' => 'Test',
        'assignment_type' => 'individual',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addWeeks(1)->toDateString(),
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['model']);
});

it('refuses generation once the model hits its monthly limit', function () {
    $tracker = app(AIUsageTracker::class);
    for ($i = 0; $i < 5; $i++) {
        $tracker->increment('openai-gpt-4o-mini');
    }

    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('blueprint.generate'), [
        'model' => 'openai-gpt-4o-mini',
        'description' => 'Test',
        'assignment_type' => 'individual',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addWeeks(1)->toDateString(),
    ])->assertStatus(422)
        ->assertJsonValidationErrors(['model']);
});

it('clamps task dates outside the project timeline', function () {
    $start = now()->startOfDay();
    $end = $start->copy()->addDays(5);

    bindFakeClient([
        'project' => ['name' => 'P', 'description' => null],
        'tasks' => [[
            'title' => 'Overdue task',
            'description' => null,
            'milestone' => 'Build',
            'skill_required' => 'Backend',
            'estimated_hours' => 4,
            'priority' => 'medium',
            'status' => 'pending',
            'start_date' => $end->copy()->subDay()->toDateString(),
            'due_date' => $end->copy()->addWeeks(2)->toDateString(),
            'assigned_to' => null,
            'depends_on' => [],
        ]],
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson(route('blueprint.generate'), [
        'model' => 'openai-gpt-4o-mini',
        'description' => 'Test',
        'assignment_type' => 'individual',
        'start_date' => $start->toDateString(),
        'end_date' => $end->toDateString(),
    ]);

    $response->assertOk();
    expect($response->json('blueprint.tasks.0.due_date'))->toBe($end->toDateString());
});

it('finalizes the draft into a real project with tasks in one transaction', function () {
    $user = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson(route('blueprint.store'), [
        'project' => [
            'name' => 'Launch Site',
            'description' => 'Marketing launch',
            'color' => 'emerald',
        ],
        'workspace_id' => $workspace->id,
        'tasks' => [
            [
                'title' => 'Wireframes',
                'description' => 'Sketch screens',
                'priority' => TaskPriority::High->value,
                'start_date' => now()->toDateString(),
                'due_date' => now()->addDays(2)->toDateString(),
            ],
            [
                'title' => 'Copy review',
                'description' => null,
                'priority' => TaskPriority::Medium->value,
                'start_date' => now()->addDays(1)->toDateString(),
                'due_date' => now()->addDays(3)->toDateString(),
            ],
        ],
    ]);

    $response->assertOk();

    $project = Project::query()->where('name', 'Launch Site')->firstOrFail();

    expect($project->user_id)->toBe($user->id)
        ->and($project->workspace_id)->toBe($workspace->id)
        ->and($project->color)->toBe('emerald');

    $tasks = Task::query()->where('project_id', $project->id)->get();

    expect($tasks)->toHaveCount(2)
        ->and($tasks->pluck('title')->all())->toEqualCanonicalizing(['Wireframes', 'Copy review'])
        ->and($tasks->every(fn (Task $t) => $t->status === TaskStatus::Pending))->toBeTrue();
});

it('rejects finalize without any tasks', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('blueprint.store'), [
        'project' => ['name' => 'Empty'],
        'tasks' => [],
    ])->assertStatus(422);
});
