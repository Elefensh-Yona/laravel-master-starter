<?php

use App\Models\Media;
use App\Models\User;
use App\Support\SystemRole;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;

test('manager policy abilities match the intended admin module permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create();
    $manager->assignRole(SystemRole::MANAGER);

    $media = Media::factory()->create();
    $role = Role::findByName(SystemRole::STAFF);
    $user = User::factory()->create();

    expect(Gate::forUser($manager)->allows('viewAny', Media::class))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('create', Media::class))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('delete', $media))->toBeFalse()
        ->and(Gate::forUser($manager)->allows('viewAny', User::class))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('updateRoles', $user))->toBeFalse()
        ->and(Gate::forUser($manager)->allows('viewAny', Role::class))->toBeFalse()
        ->and(Gate::forUser($manager)->allows('updatePermissions', $role))->toBeFalse();
});

test('super admin gate before hook still grants policy abilities across modules', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole(SystemRole::SUPER_ADMIN);

    $media = Media::factory()->create();
    $role = Role::findByName(SystemRole::STAFF);
    $user = User::factory()->create();

    expect(Gate::forUser($admin)->allows('viewAny', Media::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', Media::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('delete', $media))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('viewAny', User::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('updateRoles', $user))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('viewAny', Role::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('updatePermissions', $role))->toBeTrue();
});
