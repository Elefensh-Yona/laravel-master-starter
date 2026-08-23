<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

test('admin can create a custom role with permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin)
        ->post(route('roles.store'), [
            'name' => 'SupportLead',
            'description' => 'Support queue owner',
            'permissions' => ['dashboard.view', 'notifications.view'],
        ])
        ->assertRedirect();

    $role = Role::findByName('SupportLead');

    expect($role->description)->toBe('Support queue owner')
        ->and($role->hasPermissionTo('notifications.view'))->toBeTrue();
});

test('admin can update custom role metadata', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $role = Role::query()->create([
        'name' => 'SupportLead',
        'guard_name' => 'web',
        'description' => 'Initial description',
    ]);

    $this->actingAs($admin)
        ->put(route('roles.update', $role), [
            'name' => 'SupportLead',
            'description' => 'Updated description',
        ])
        ->assertRedirect(route('roles.edit', $role));

    expect($role->fresh()->description)->toBe('Updated description');
});

test('admin can delete a custom role but not a system role', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $role = Role::query()->create([
        'name' => 'SupportLead',
        'guard_name' => 'web',
        'description' => 'Temporary role',
    ]);

    $this->actingAs($admin)
        ->delete(route('roles.destroy', $role))
        ->assertRedirect(route('roles.index'));

    expect(Role::query()->where('name', 'SupportLead')->exists())->toBeFalse();

    $systemRole = Role::findByName('Manager');

    $this->actingAs($admin)
        ->delete(route('roles.destroy', $systemRole))
        ->assertRedirect(route('roles.index'))
        ->assertSessionHas('error');
});
