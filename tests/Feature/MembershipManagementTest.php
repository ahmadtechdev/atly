<?php

use App\Enums\MembershipRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeVerifiedUser(): User
{
    $user = User::factory()->create();
    $user->markEmailAsVerified();

    return $user;
}

function inviteAndAccept($entity, User $owner, User $member, string $role): void
{
    $service = app(InvitationService::class);
    $service->accept(
        $service->send($owner, $entity, $member->email, role: $role)->refresh(),
        $member,
    );
}

it('lets the task owner change a collaborator role', function () {
    $owner = makeVerifiedUser();
    $member = makeVerifiedUser();
    $task = Task::factory()->create(['user_id' => $owner->id]);
    inviteAndAccept($task, $owner, $member, 'guest');

    $this->actingAs($owner)
        ->patchJson(route('tasks.collaborators.update', [$task, $member]), ['role' => 'admin'])
        ->assertOk();

    expect($task->fresh()->roleFor($member))->toBe(MembershipRole::Admin);
});

it('does not let admins change roles, only the owner', function () {
    $owner = makeVerifiedUser();
    $admin = makeVerifiedUser();
    $target = makeVerifiedUser();
    $task = Task::factory()->create(['user_id' => $owner->id]);
    inviteAndAccept($task, $owner, $admin, 'admin');
    inviteAndAccept($task, $owner, $target, 'guest');

    $this->actingAs($admin)
        ->patchJson(route('tasks.collaborators.update', [$task, $target]), ['role' => 'viewer'])
        ->assertForbidden();

    expect($task->fresh()->roleFor($target))->toBe(MembershipRole::Guest);
});

it('refuses to change the owner role', function () {
    $owner = makeVerifiedUser();
    $task = Task::factory()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)
        ->patchJson(route('tasks.collaborators.update', [$task, $owner]), ['role' => 'viewer'])
        ->assertStatus(422);
});

it('lets the task owner remove a collaborator', function () {
    $owner = makeVerifiedUser();
    $member = makeVerifiedUser();
    $task = Task::factory()->create(['user_id' => $owner->id]);
    inviteAndAccept($task, $owner, $member, 'assignee');

    $this->actingAs($owner)
        ->deleteJson(route('tasks.collaborators.destroy', [$task, $member]))
        ->assertOk();

    expect($task->fresh()->collaborators()->whereKey($member->id)->exists())->toBeFalse();
});

it('lets the project owner update a member role and admins cannot', function () {
    $owner = makeVerifiedUser();
    $admin = makeVerifiedUser();
    $target = makeVerifiedUser();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    inviteAndAccept($project, $owner, $admin, 'admin');
    inviteAndAccept($project, $owner, $target, 'assignee');

    $this->actingAs($admin)
        ->patchJson(route('projects.members.update', [$project, $target]), ['role' => 'guest'])
        ->assertForbidden();

    $this->actingAs($owner)
        ->patchJson(route('projects.members.update', [$project, $target]), ['role' => 'viewer'])
        ->assertOk();

    expect($project->fresh()->roleFor($target))->toBe(MembershipRole::Viewer);
});

it('lets the workspace owner remove a member', function () {
    $owner = makeVerifiedUser();
    $member = makeVerifiedUser();
    $workspace = Workspace::factory()->create(['user_id' => $owner->id]);
    inviteAndAccept($workspace, $owner, $member, 'admin');

    $this->actingAs($owner)
        ->deleteJson(route('workspaces.members.destroy', [$workspace, $member]))
        ->assertOk();

    expect($workspace->fresh()->members()->whereKey($member->id)->exists())->toBeFalse();
});
