<?php

use App\Enums\InvitationStatus;
use App\Enums\MembershipRole;
use App\Models\Invitation;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\InvitationReceivedNotification;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    Notification::fake();
});

it('sends an invitation for a project and notifies the existing user', function () {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create(['email' => 'teammate@example.com']);
    $project = Project::factory()->create(['user_id' => $inviter->id]);

    $invitation = app(InvitationService::class)
        ->send($inviter, $project, 'TeamMate@example.com', 'Join us');

    expect($invitation)
        ->status->toBe(InvitationStatus::Pending)
        ->and($invitation->invitee_id)->toBe($invitee->id)
        ->and($invitation->invitable_id)->toBe($project->id);

    Notification::assertSentTo($invitee, InvitationReceivedNotification::class);
});

it('sends an invitation to an unknown email address via mail route', function () {
    $inviter = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $inviter->id]);

    $invitation = app(InvitationService::class)
        ->send($inviter, $workspace, 'newperson@example.com');

    expect($invitation->invitee_id)->toBeNull();

    Notification::assertSentOnDemand(InvitationReceivedNotification::class);
});

it('rejects duplicate pending invitations for the same target and email', function () {
    $inviter = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $inviter->id]);

    app(InvitationService::class)->send($inviter, $project, 'a@b.test');

    expect(fn () => app(InvitationService::class)->send($inviter, $project, 'A@B.test'))
        ->toThrow(ValidationException::class);
});

it('refuses self-invites', function () {
    $inviter = User::factory()->create(['email' => 'me@example.com']);
    $project = Project::factory()->create(['user_id' => $inviter->id]);

    expect(fn () => app(InvitationService::class)->send($inviter, $project, 'me@example.com'))
        ->toThrow(ValidationException::class);
});

it('accepts an invitation and creates the right membership row', function () {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create(['email' => 'invitee@example.com']);

    $workspace = Workspace::factory()->create(['user_id' => $inviter->id]);
    $project = Project::factory()->create(['user_id' => $inviter->id, 'workspace_id' => $workspace->id]);
    $task = Task::factory()->create(['user_id' => $inviter->id, 'project_id' => $project->id]);

    $service = app(InvitationService::class);

    $workspaceInv = $service->send($inviter, $workspace, $invitee->email, role: 'admin');
    $projectInv = $service->send($inviter, $project, $invitee->email, role: 'assignee');
    $taskInv = $service->send($inviter, $task, $invitee->email, role: 'guest');

    $service->accept($workspaceInv->refresh(), $invitee);
    $service->accept($projectInv->refresh(), $invitee);
    $service->accept($taskInv->refresh(), $invitee);

    expect($workspace->members()->whereKey($invitee->id)->exists())->toBeTrue()
        ->and($project->members()->whereKey($invitee->id)->exists())->toBeTrue()
        ->and($task->collaborators()->whereKey($invitee->id)->exists())->toBeTrue();

    expect($workspace->roleFor($invitee))->toBe(MembershipRole::Admin)
        ->and($project->roleFor($invitee))->toBe(MembershipRole::Assignee)
        ->and($task->roleFor($invitee))->toBe(MembershipRole::Guest);
});

it('makes accepted entities visible in the recipient index queries', function () {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create();

    $workspace = Workspace::factory()->create(['user_id' => $inviter->id]);
    $project = Project::factory()->create(['user_id' => $inviter->id, 'workspace_id' => $workspace->id]);
    $task = Task::factory()->create(['user_id' => $inviter->id, 'project_id' => $project->id]);

    $service = app(InvitationService::class);
    $service->accept($service->send($inviter, $workspace, $invitee->email, role: 'admin')->refresh(), $invitee);

    expect(Workspace::query()->accessibleFor($invitee)->whereKey($workspace->id)->exists())->toBeTrue()
        ->and(Project::query()->accessibleFor($invitee)->whereKey($project->id)->exists())->toBeTrue()
        ->and(Task::query()->accessibleFor($invitee)->whereKey($task->id)->exists())->toBeTrue();
});

it('defaults invitation role to guest when none is provided', function () {
    $inviter = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $inviter->id]);

    $invitation = app(InvitationService::class)->send($inviter, $project, 'pal@example.com');

    expect($invitation->role)->toBe(MembershipRole::Guest->value);
});

it('blocks non-admin members from inviting others', function () {
    $owner = User::factory()->create();
    $guest = User::factory()->create();
    $owner->markEmailAsVerified();
    $guest->markEmailAsVerified();

    $project = Project::factory()->create(['user_id' => $owner->id]);

    $service = app(InvitationService::class);
    $service->accept(
        $service->send($owner, $project, $guest->email, role: 'guest')->refresh(),
        $guest
    );

    expect($project->canManage($guest))->toBeFalse();

    $this->actingAs($guest)
        ->postJson(route('invitations.store'), [
            'invitable_type' => 'project',
            'invitable_id' => $project->id,
            'email' => 'someone@example.com',
        ])
        ->assertForbidden();
});

it('allows an admin member to invite others', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $owner->markEmailAsVerified();
    $admin->markEmailAsVerified();

    $project = Project::factory()->create(['user_id' => $owner->id]);

    $service = app(InvitationService::class);
    $service->accept(
        $service->send($owner, $project, $admin->email, role: 'admin')->refresh(),
        $admin
    );

    $this->actingAs($admin)
        ->postJson(route('invitations.store'), [
            'invitable_type' => 'project',
            'invitable_id' => $project->id,
            'email' => 'newcomer@example.com',
            'role' => 'assignee',
        ])
        ->assertOk();
});

it('does not auto-promote lower memberships upward', function () {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $inviter->id]);
    $project = Project::factory()->create(['user_id' => $inviter->id, 'workspace_id' => $workspace->id]);
    $task = Task::factory()->create(['user_id' => $inviter->id, 'project_id' => $project->id]);

    $taskInv = app(InvitationService::class)->send($inviter, $task, $invitee->email);
    app(InvitationService::class)->accept($taskInv->refresh(), $invitee);

    expect($task->collaborators()->whereKey($invitee->id)->exists())->toBeTrue()
        ->and($project->members()->whereKey($invitee->id)->exists())->toBeFalse()
        ->and($workspace->members()->whereKey($invitee->id)->exists())->toBeFalse();
});

it('inherits access top-down from workspace down to a task', function () {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $workspace = Workspace::factory()->create(['user_id' => $inviter->id]);
    $project = Project::factory()->create(['user_id' => $inviter->id, 'workspace_id' => $workspace->id]);
    $task = Task::factory()->create(['user_id' => $inviter->id, 'project_id' => $project->id]);

    $wsInv = app(InvitationService::class)->send($inviter, $workspace, $invitee->email);
    app(InvitationService::class)->accept($wsInv->refresh(), $invitee);

    expect($workspace->hasAccessFor($invitee))->toBeTrue()
        ->and($project->hasAccessFor($invitee))->toBeTrue()
        ->and($task->hasAccessFor($invitee))->toBeTrue();
});

it('declines and cancels invitations correctly', function () {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $inviter->id]);

    $service = app(InvitationService::class);

    $declined = $service->send($inviter, $project, $invitee->email);
    $service->decline($declined->refresh(), $invitee);
    expect($declined->refresh()->status)->toBe(InvitationStatus::Declined);

    $cancelled = $service->send($inviter, Project::factory()->create(['user_id' => $inviter->id]), 'other@example.com');
    $service->cancel($cancelled->refresh(), $inviter);
    expect($cancelled->refresh()->status)->toBe(InvitationStatus::Cancelled);
});

it('exposes incoming invitations via http endpoint and filters by tab', function () {
    $inviter = User::factory()->create();
    $invitee = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $inviter->id]);
    $invitee->markEmailAsVerified();

    app(InvitationService::class)->send($inviter, $project, $invitee->email, 'Welcome');

    $this->actingAs($invitee)
        ->get(route('invitations.index'))
        ->assertOk()
        ->assertSee('Welcome');
});

it('stores invitations via http for the entity owner only', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $owner->markEmailAsVerified();
    $stranger->markEmailAsVerified();

    $project = Project::factory()->create(['user_id' => $owner->id]);

    $payload = [
        'invitable_type' => 'project',
        'invitable_id' => $project->id,
        'email' => 'someone@example.com',
    ];

    $this->actingAs($stranger)
        ->postJson(route('invitations.store'), $payload)
        ->assertForbidden();

    $this->actingAs($owner)
        ->postJson(route('invitations.store'), $payload)
        ->assertOk();

    expect(Invitation::query()->count())->toBe(1);
});
