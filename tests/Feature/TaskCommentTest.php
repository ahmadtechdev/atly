<?php

use App\Enums\MembershipRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Models\Workspace;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function verifiedUser(array $attrs = []): User
{
    $user = User::factory()->create($attrs);
    $user->markEmailAsVerified();

    return $user;
}

function addTaskCollaborator(Task $task, User $owner, User $member, string $role): void
{
    $service = app(InvitationService::class);
    $invitation = $service->send($owner, $task, $member->email, role: $role);
    $service->accept($invitation->refresh(), $member);
}

it('allows assignees to post comments on a task', function () {
    $owner = verifiedUser();
    $assignee = verifiedUser();
    $task = Task::factory()->create(['user_id' => $owner->id]);

    addTaskCollaborator($task, $owner, $assignee, 'assignee');

    $this->actingAs($assignee)
        ->postJson(route('tasks.comments.store', $task), ['body' => 'Looks good to me.'])
        ->assertOk()
        ->assertJsonPath('comment.body', 'Looks good to me.');

    expect($task->comments()->count())->toBe(1);
});

it('allows viewers to post comments but not guests', function () {
    $owner = verifiedUser();
    $viewer = verifiedUser();
    $guest = verifiedUser();
    $task = Task::factory()->create(['user_id' => $owner->id]);

    addTaskCollaborator($task, $owner, $viewer, 'viewer');
    addTaskCollaborator($task, $owner, $guest, 'guest');

    expect($task->canComment($viewer))->toBeTrue()
        ->and($task->canComment($guest))->toBeFalse();

    $this->actingAs($viewer)
        ->postJson(route('tasks.comments.store', $task), ['body' => 'Question about scope.'])
        ->assertOk();

    $this->actingAs($guest)
        ->postJson(route('tasks.comments.store', $task), ['body' => 'Should not post.'])
        ->assertForbidden();
});

it('inherits viewer role from workspace membership for commenting on tasks', function () {
    $owner = verifiedUser();
    $viewer = verifiedUser();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    $project = Project::factory()->create(['user_id' => $owner->id, 'workspace_id' => $workspace->id]);
    $task = Task::factory()->create(['user_id' => $owner->id, 'project_id' => $project->id]);

    $service = app(InvitationService::class);
    $invitation = $service->send($owner, $workspace, $viewer->email, role: 'viewer');
    $service->accept($invitation->refresh(), $viewer);

    expect($task->roleFor($viewer))->toBe(MembershipRole::Viewer)
        ->and($task->canComment($viewer))->toBeTrue();

    $this->actingAs($viewer)
        ->postJson(route('tasks.comments.store', $task), ['body' => 'Inherited access comment.'])
        ->assertOk();
});

it('inherits guest role from project and blocks commenting', function () {
    $owner = verifiedUser();
    $guest = verifiedUser();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $task = Task::factory()->create(['user_id' => $owner->id, 'project_id' => $project->id]);

    $service = app(InvitationService::class);
    $invitation = $service->send($owner, $project, $guest->email, role: 'guest');
    $service->accept($invitation->refresh(), $guest);

    expect($task->canComment($guest))->toBeFalse();

    $this->actingAs($guest)
        ->postJson(route('tasks.comments.store', $task), ['body' => 'Nope.'])
        ->assertForbidden();
});

it('renders the full task show page with comments', function () {
    $owner = verifiedUser();
    $task = Task::factory()->create(['user_id' => $owner->id, 'title' => 'Ship comments']);

    TaskComment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $owner->id,
        'body' => 'First note',
    ]);

    $this->actingAs($owner)
        ->get(route('tasks.show', $task))
        ->assertOk()
        ->assertSee('Ship comments')
        ->assertSee('First note')
        ->assertSee('Comments');
});

it('returns task json for ajax show requests', function () {
    $owner = verifiedUser();
    $task = Task::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)
        ->getJson(route('tasks.show', $task))
        ->assertOk()
        ->assertJsonPath('task.viewer.can_comment', true)
        ->assertJsonStructure(['task' => ['comments', 'show_url', 'comments_url']]);
});

it('lets comment authors delete their own comments', function () {
    $owner = verifiedUser();
    $assignee = verifiedUser();
    $task = Task::factory()->create(['user_id' => $owner->id]);
    addTaskCollaborator($task, $owner, $assignee, 'assignee');

    $comment = TaskComment::factory()->create([
        'task_id' => $task->id,
        'user_id' => $assignee->id,
        'body' => 'Temporary',
    ]);

    $this->actingAs($assignee)
        ->deleteJson(route('tasks.comments.destroy', [$task, $comment]))
        ->assertOk();

    expect(TaskComment::query()->whereKey($comment->id)->exists())->toBeFalse();
});
