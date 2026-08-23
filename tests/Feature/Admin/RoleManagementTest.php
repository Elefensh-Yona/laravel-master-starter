<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

test('admin can update a role permission set', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $managerRole = Role::findByName('Manager');

    expect($managerRole->hasPermissionTo('roles.view'))->toBeFalse();

    $this->actingAs($admin)
        ->put(route('roles.permissions.update', $managerRole), [
            'permissions' => [
                'dashboard.view',
                'notifications.view',
                'roles.view',
            ],
        ])
        ->assertRedirect(route('roles.edit', $managerRole));

    expect($managerRole->fresh()->hasPermissionTo('roles.view'))->toBeTrue();
});

test('admin role cannot be edited from the role management screen', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $role = Role::findByName('Super Admin');

    $this->actingAs($admin)
        ->from(route('roles.edit', $role))
        ->put(route('roles.permissions.update', $role), [
            'permissions' => ['dashboard.view'],
        ])
        ->assertRedirect(route('roles.edit', $role))
        ->assertSessionHasErrors('permissions');
});

test('manager cannot update roles without the update permission', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create();
    $manager->assignRole('Manager');

    $role = Role::findByName('Staff');

    $this->actingAs($manager)
        ->put(route('roles.permissions.update', $role), [
            'permissions' => ['dashboard.view'],
        ])
        ->assertForbidden();
});
