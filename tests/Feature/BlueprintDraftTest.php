<?php

use App\Enums\BlueprintDraftStatus;
use App\Enums\MembershipRole;
use App\Models\BlueprintDraft;
use App\Models\Invitation;
use App\Models\User;
use App\Services\BlueprintDraftService;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Notification::fake();
});

function blueprintPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'project' => [
            'name' => 'Bakery Storefront',
            'description' => 'Tiny e-commerce site for a bakery.',
            'color' => 'amber',
        ],
        'workspace_id' => null,
        'assignment_type' => 'individual',
        'start_date' => now()->toDateString(),
        'end_date' => now()->addWeeks(4)->toDateString(),
        'tasks' => [
            [
                'title' => 'Set up project',
                'description' => 'Scaffold the repo.',
                'priority' => 'high',
                'start_date' => now()->toDateString(),
                'due_date' => now()->addDays(2)->toDateString(),
                'assigned_to' => null,
            ],
            [
                'title' => 'Build cart',
                'description' => 'Add product cart UI.',
                'priority' => 'medium',
                'start_date' => now()->addDays(3)->toDateString(),
                'due_date' => now()->addDays(10)->toDateString(),
                'assigned_to' => null,
            ],
        ],
        'members' => [],
    ], $overrides);
}

it('stores a draft via the http endpoint', function (): void {
    $user = User::factory()->create();
    $user->markEmailAsVerified();

    $response = $this->actingAs($user)
        ->postJson(route('blueprint.drafts.store'), blueprintPayload());

    $response->assertOk()
        ->assertJsonPath('draft.name', 'Bakery Storefront')
        ->assertJsonPath('draft.assignment_type', 'individual');

    expect(BlueprintDraft::count())->toBe(1);
    expect(BlueprintDraft::first())
        ->name->toBe('Bakery Storefront')
        ->status->toBe(BlueprintDraftStatus::Draft);
});

it('shows the drafts index page only to the owner', function (): void {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $owner->markEmailAsVerified();
    $other->markEmailAsVerified();

    $draft = app(BlueprintDraftService::class)->save($owner, blueprintPayload());

    $this->actingAs($other)
        ->get(route('blueprint.drafts.show', $draft))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('blueprint.drafts.show', $draft))
        ->assertOk()
        ->assertSee('Bakery Storefront');
});

it('finalizes an individual draft into a project with tasks', function (): void {
    $user = User::factory()->create();
    $user->markEmailAsVerified();

    $draft = app(BlueprintDraftService::class)->save($user, blueprintPayload());

    $this->actingAs($user)
        ->postJson(route('blueprint.drafts.finalize', $draft))
        ->assertOk();

    $draft->refresh();
    expect($draft->status)->toBe(BlueprintDraftStatus::Finalized)
        ->and($draft->finalized_project_id)->not->toBeNull();

    expect($draft->project)
        ->name->toBe('Bakery Storefront')
        ->user_id->toBe($user->id);

    expect($draft->project->tasks)->toHaveCount(2);
});

it('sends invitations for a team draft using existing invitation system', function (): void {
    $owner = User::factory()->create();
    $owner->markEmailAsVerified();

    $draft = app(BlueprintDraftService::class)->save($owner, blueprintPayload([
        'assignment_type' => 'team',
        'tasks' => [
            [
                'title' => 'Design homepage',
                'priority' => 'medium',
                'start_date' => now()->toDateString(),
                'due_date' => now()->addDays(5)->toDateString(),
                'assigned_to' => 'Alice',
            ],
        ],
        'members' => [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ],
    ]));

    $this->actingAs($owner)
        ->postJson(route('blueprint.drafts.invite', $draft))
        ->assertOk();

    $draft->refresh();
    expect($draft->status)->toBe(BlueprintDraftStatus::AwaitingInvites)
        ->and(Invitation::where('invitable_type', BlueprintDraft::class)->count())->toBe(2);
});

it('auto-finalizes a team draft once every invited member accepts', function (): void {
    $owner = User::factory()->create();
    $owner->markEmailAsVerified();

    $alice = User::factory()->create(['email' => 'alice@example.com']);
    $bob = User::factory()->create(['email' => 'bob@example.com']);
    $alice->markEmailAsVerified();
    $bob->markEmailAsVerified();

    $draft = app(BlueprintDraftService::class)->save($owner, blueprintPayload([
        'assignment_type' => 'team',
        'tasks' => [
            [
                'title' => 'Design homepage',
                'priority' => 'medium',
                'start_date' => now()->toDateString(),
                'due_date' => now()->addDays(5)->toDateString(),
                'assigned_to' => 'Alice',
            ],
        ],
        'members' => [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ],
    ]));

    app(BlueprintDraftService::class)->sendInvitations($draft);

    $invitations = Invitation::where('invitable_type', BlueprintDraft::class)->get();
    $aliceInv = $invitations->firstWhere('invitee_email', 'alice@example.com');
    $bobInv = $invitations->firstWhere('invitee_email', 'bob@example.com');

    app(InvitationService::class)->accept($aliceInv->refresh(), $alice);

    expect($draft->fresh()->status)->toBe(BlueprintDraftStatus::AwaitingInvites);

    app(InvitationService::class)->accept($bobInv->refresh(), $bob);

    $draft->refresh();
    expect($draft->status)->toBe(BlueprintDraftStatus::Finalized)
        ->and($draft->finalized_project_id)->not->toBeNull();

    $project = $draft->project;
    expect($project->members()->whereKey($alice->id)->exists())->toBeTrue()
        ->and($project->members()->whereKey($bob->id)->exists())->toBeTrue();

    $task = $project->tasks()->first();
    expect($task->collaborators()->whereKey($alice->id)->exists())->toBeTrue();
});

it('marks a draft member as declined when their invitation is declined', function (): void {
    $owner = User::factory()->create();
    $owner->markEmailAsVerified();

    $alice = User::factory()->create(['email' => 'alice@example.com']);
    $alice->markEmailAsVerified();

    $draft = app(BlueprintDraftService::class)->save($owner, blueprintPayload([
        'assignment_type' => 'team',
        'members' => [['name' => 'Alice', 'email' => 'alice@example.com']],
    ]));

    app(BlueprintDraftService::class)->sendInvitations($draft);

    $aliceInv = Invitation::where('invitable_type', BlueprintDraft::class)->first();
    app(InvitationService::class)->decline($aliceInv->refresh(), $alice);

    $member = $draft->fresh()->members->first();
    expect($member->declined_at)->not->toBeNull()
        ->and($draft->fresh()->status)->toBe(BlueprintDraftStatus::AwaitingInvites);
});

it('allows manual finalization of a team draft with partial acceptance', function (): void {
    $owner = User::factory()->create();
    $owner->markEmailAsVerified();
    $alice = User::factory()->create(['email' => 'alice@example.com']);
    $alice->markEmailAsVerified();

    $draft = app(BlueprintDraftService::class)->save($owner, blueprintPayload([
        'assignment_type' => 'team',
        'members' => [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ],
    ]));

    app(BlueprintDraftService::class)->sendInvitations($draft);

    $aliceInv = Invitation::where('invitee_email', 'alice@example.com')->first();
    app(InvitationService::class)->accept($aliceInv->refresh(), $alice);

    $draft->refresh();
    expect($draft->status)->toBe(BlueprintDraftStatus::AwaitingInvites);

    $this->actingAs($owner)
        ->postJson(route('blueprint.drafts.finalize', $draft))
        ->assertOk();

    $draft->refresh();
    expect($draft->status)->toBe(BlueprintDraftStatus::Finalized);
    expect($draft->project->members()->whereKey($alice->id)->exists())->toBeTrue();
});

it('blocks non-owners from accessing or editing drafts', function (): void {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $owner->markEmailAsVerified();
    $stranger->markEmailAsVerified();

    $draft = app(BlueprintDraftService::class)->save($owner, blueprintPayload());

    $this->actingAs($stranger)
        ->putJson(route('blueprint.drafts.update', $draft), ['project' => ['name' => 'Hacked']])
        ->assertForbidden();

    $this->actingAs($stranger)
        ->deleteJson(route('blueprint.drafts.destroy', $draft))
        ->assertForbidden();
});

it('cancels pending invitations when a member is removed from a team draft', function (): void {
    $owner = User::factory()->create();
    $owner->markEmailAsVerified();

    $draft = app(BlueprintDraftService::class)->save($owner, blueprintPayload([
        'assignment_type' => 'team',
        'members' => [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com'],
        ],
    ]));

    app(BlueprintDraftService::class)->sendInvitations($draft);

    $remaining = $draft->fresh()->members->firstWhere('name', 'Alice');

    $this->actingAs($owner)
        ->putJson(route('blueprint.drafts.update', $draft), [
            'members' => [
                ['id' => $remaining->id, 'name' => 'Alice', 'email' => 'alice@example.com'],
            ],
        ])
        ->assertOk();

    expect($draft->fresh()->members)->toHaveCount(1);
});

it('does not consider role lower than draft invite role default', function (): void {
    $owner = User::factory()->create();
    $owner->markEmailAsVerified();

    $draft = app(BlueprintDraftService::class)->save($owner, blueprintPayload([
        'assignment_type' => 'team',
        'members' => [['name' => 'Alice', 'email' => 'alice@example.com']],
    ]));

    app(BlueprintDraftService::class)->sendInvitations($draft);
    $invitation = Invitation::where('invitable_type', BlueprintDraft::class)->first();

    expect($invitation->role)->toBe(MembershipRole::Assignee->value);
});
