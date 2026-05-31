<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function makeSuperAdmin(array $attributes = []): User
{
    return User::factory()->superAdmin()->create(array_merge([
        'email' => 'admin@atly.com',
        'password' => Hash::make('12345678'),
    ], $attributes));
}

it('shows the super admin login page', function () {
    $this->get(route('admin.login'))
        ->assertOk()
        ->assertSee('Super Admin');
});

it('lets a super admin sign in via the admin login', function () {
    makeSuperAdmin();

    $this->post(route('admin.login'), [
        'email' => 'admin@atly.com',
        'password' => '12345678',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs(User::query()->where('email', 'admin@atly.com')->first());
});

it('rejects non super admin users on admin login', function () {
    $user = User::factory()->create([
        'email' => 'user@example.com',
        'password' => Hash::make('password'),
    ]);
    $user->markEmailAsVerified();

    $this->post(route('admin.login'), [
        'email' => 'user@example.com',
        'password' => 'password',
    ])->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('displays user stats on the super admin dashboard', function () {
    $admin = makeSuperAdmin();

    User::factory()->count(3)->create();
    User::factory()->unverified()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Total registered')
        ->assertSee('5')
        ->assertSee('Verification pending')
        ->assertSee('2');
});

it('blocks regular users from the admin dashboard', function () {
    $user = User::factory()->create();
    $user->markEmailAsVerified();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('redirects super admins away from the user dashboard', function () {
    $admin = makeSuperAdmin();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

it('redirects super admins from regular login to the admin dashboard', function () {
    makeSuperAdmin();

    $this->post(route('login'), [
        'email' => 'admin@atly.com',
        'password' => '12345678',
    ])->assertRedirect(route('admin.dashboard'));
});

it('lets a super admin update their password from settings', function () {
    $admin = makeSuperAdmin();

    $this->actingAs($admin)
        ->put(route('admin.settings.update'), [
            'current_password' => '12345678',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHas('status');

    expect(Hash::check('new-secure-password', $admin->fresh()->password))->toBeTrue();
});

it('logs out super admin to the admin login page', function () {
    $admin = makeSuperAdmin();

    $this->actingAs($admin)
        ->post(route('admin.logout'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});
