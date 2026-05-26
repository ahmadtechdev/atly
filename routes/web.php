<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BlueprintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskCollaboratorController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TimeTrackerController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::post('verify-email', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('verify-email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('reset-password', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('tasks/search', [TaskController::class, 'search'])->name('tasks.search');
    Route::patch('tasks/{task}/start', [TaskController::class, 'start'])->name('tasks.start');
    Route::patch('tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete'])->name('tasks.toggle-complete');
    Route::patch('tasks/{task}/project', [TaskController::class, 'updateProject'])->name('tasks.update-project');
    Route::post('tasks/{task}/comments', [TaskCommentController::class, 'store'])->name('tasks.comments.store');
    Route::delete('tasks/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('tasks.comments.destroy');
    Route::patch('tasks/{task}/collaborators/{member}', [TaskCollaboratorController::class, 'update'])->name('tasks.collaborators.update');
    Route::delete('tasks/{task}/collaborators/{member}', [TaskCollaboratorController::class, 'destroy'])->name('tasks.collaborators.destroy');
    Route::resource('tasks', TaskController::class);

    Route::get('time-tracker', [TimeTrackerController::class, 'index'])->name('time-tracker.index');
    Route::post('time-tracker/start', [TimeTrackerController::class, 'start'])->name('time-tracker.start');
    Route::patch('time-tracker/{entry}/stop', [TimeTrackerController::class, 'stop'])->name('time-tracker.stop');
    Route::delete('time-tracker/{entry}', [TimeTrackerController::class, 'destroy'])->name('time-tracker.destroy');

    Route::get('projects/search', [ProjectController::class, 'search'])->name('projects.search');
    Route::patch('projects/{project}/workspace', [ProjectController::class, 'updateWorkspace'])->name('projects.update-workspace');
    Route::patch('projects/{project}/complete', [ProjectController::class, 'complete'])->name('projects.complete');
    Route::patch('projects/{project}/reopen', [ProjectController::class, 'reopen'])->name('projects.reopen');
    Route::patch('projects/{project}/members/{member}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
    Route::delete('projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');
    Route::resource('projects', ProjectController::class);

    Route::get('workspaces/search', [WorkspaceController::class, 'search'])->name('workspaces.search');
    Route::patch('workspaces/{workspace}/members/{member}', [WorkspaceMemberController::class, 'update'])->name('workspaces.members.update');
    Route::delete('workspaces/{workspace}/members/{member}', [WorkspaceMemberController::class, 'destroy'])->name('workspaces.members.destroy');
    Route::resource('workspaces', WorkspaceController::class);

    Route::get('blueprint', [BlueprintController::class, 'index'])->name('blueprint.index');
    Route::post('blueprint/generate', [BlueprintController::class, 'generate'])->name('blueprint.generate');
    Route::post('blueprint', [BlueprintController::class, 'store'])->name('blueprint.store');

    Route::get('invitations', [InvitationController::class, 'index'])->name('invitations.index');
    Route::post('invitations', [InvitationController::class, 'store'])->name('invitations.store');
    Route::patch('invitations/{invitation}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
    Route::patch('invitations/{invitation}/decline', [InvitationController::class, 'decline'])->name('invitations.decline');
    Route::delete('invitations/{invitation}', [InvitationController::class, 'destroy'])->name('invitations.destroy');
});
