<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('guests hitting the root route are redirected to login', function () {
    $this->get(route('home'))
        ->assertRedirect(route('login'));
});

test('authenticated users with dashboard access are redirected to the dashboard', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Staff');

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});

test('authenticated users without dashboard access are redirected to their profile', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Guest');

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('profile.edit'));
});
