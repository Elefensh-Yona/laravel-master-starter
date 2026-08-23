<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('admin can update another users roles', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $targetUser = User::factory()->create();
    $targetUser->assignRole('Staff');

    $this->actingAs($admin)
        ->put(route('users.roles.update', $targetUser), [
            'roles' => ['Manager'],
        ])
        ->assertRedirect(route('users.edit', $targetUser));

    expect($targetUser->fresh()->hasRole('Manager'))->toBeTrue()
        ->and($targetUser->fresh()->hasRole('Staff'))->toBeFalse();
});

test('admin cannot remove their own admin role from this screen', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin)
        ->from(route('users.edit', $admin))
        ->put(route('users.roles.update', $admin), [
            'roles' => ['Staff'],
        ])
        ->assertRedirect(route('users.edit', $admin))
        ->assertSessionHasErrors('roles');
});

test('manager cannot update user roles without the update permission', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create();
    $manager->assignRole('Manager');

    $targetUser = User::factory()->create();
    $targetUser->assignRole('Staff');

    $this->actingAs($manager)
        ->put(route('users.roles.update', $targetUser), [
            'roles' => ['Guest'],
        ])
        ->assertForbidden();
});
