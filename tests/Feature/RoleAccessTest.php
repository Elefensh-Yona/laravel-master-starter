<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('super admin can access all administrative modules', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Super Admin');

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertOk();
    $this->get(route('users.index'))->assertOk();
    $this->get(route('roles.index'))->assertOk();
    $this->get(route('admin-settings.edit'))->assertOk();
    $this->get(route('media.index'))->assertOk();
    $this->get(route('notifications.index'))->assertOk();
    $this->get(route('activity-logs.index'))->assertOk();
    $this->get(route('exports.index'))->assertOk();
});

test('manager can access shared operational modules but not role administration', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Manager');

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertOk();
    $this->get(route('users.index'))->assertOk();
    $this->get(route('admin-settings.edit'))->assertOk();
    $this->get(route('media.index'))->assertOk();
    $this->get(route('notifications.index'))->assertOk();
    $this->get(route('activity-logs.index'))->assertOk();
    $this->get(route('roles.index'))->assertForbidden();
    $this->put(route('admin-settings.update'), [])->assertForbidden();
});

test('staff sees the dashboard and notifications but not administrative modules', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Staff');

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertOk();
    $this->get(route('notifications.index'))->assertOk();
    $this->get(route('users.index'))->assertForbidden();
    $this->get(route('roles.index'))->assertForbidden();
    $this->get(route('activity-logs.index'))->assertForbidden();
    $this->get(route('media.index'))->assertForbidden();
});

test('guest has no dashboard or administrative access but keeps account security', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('Guest');

    $this->actingAs($user);

    $this->get(route('dashboard'))->assertForbidden();
    $this->get(route('notifications.index'))->assertForbidden();
    $this->get(route('users.index'))->assertForbidden();
    $this->get(route('roles.index'))->assertForbidden();
    $this->get(route('activity-logs.index'))->assertForbidden();
    $this->get(route('media.index'))->assertForbidden();
    $this->get(route('admin-settings.edit'))->assertForbidden();
    $this->get(route('search.index'))->assertForbidden();
    $this->get(route('profile.edit'))->assertOk();
});
