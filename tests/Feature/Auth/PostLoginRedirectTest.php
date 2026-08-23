<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('guest role login is redirected to their profile instead of a forbidden dashboard', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Guest');

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticated();

    $this->get('/')
        ->assertRedirect(route('profile.edit'));

    $this->get(route('profile.edit'))->assertOk();
    $this->get(route('dashboard'))->assertForbidden();
});

test('staff login lands on the dashboard through the root route', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Staff');

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect('/');
    $this->assertAuthenticated();

    $this->get('/')->assertRedirect(route('dashboard'));
    $this->get(route('dashboard'))->assertOk();
});

test('manager and super admin logins land on the dashboard through the root route', function () {
    $this->seed(RolePermissionSeeder::class);

    foreach (['Manager', 'Super Admin'] as $role) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $this->get('/')->assertRedirect(route('dashboard'));
        $this->get(route('dashboard'))->assertOk();

        $this->post(route('logout'));
        $this->assertGuest();
    }
});
