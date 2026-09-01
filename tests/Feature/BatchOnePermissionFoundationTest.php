<?php

use App\Support\SystemRole;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

const BATCH_ONE_EAIC_PERMISSIONS = [
    'program.view',
    'program.create',
    'program.update',
    'program.publish',
    'eligibility.view',
    'eligibility.validate',
    'eligibility.screen',
    'rubric.view',
    'rubric.create',
    'rubric.update',
];

test('Batch 1 EAIC permissions are seeded with their exact canonical names', function () {
    $this->seed(RolePermissionSeeder::class);

    expect(Permission::query()
        ->whereIn('name', BATCH_ONE_EAIC_PERMISSIONS)
        ->where('guard_name', 'web')
        ->orderBy('name')
        ->pluck('name')
        ->all())->toBe(collect(BATCH_ONE_EAIC_PERMISSIONS)->sort()->values()->all());
});

test('Batch 1 EAIC permission seeding is idempotent', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);

    expect(Permission::query()
        ->whereIn('name', BATCH_ONE_EAIC_PERMISSIONS)
        ->count())->toBe(10);
});

test('starter roles remain intact without implicit EAIC domain authority', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = Role::findByName(SystemRole::MANAGER);
    $staff = Role::findByName(SystemRole::STAFF);

    expect(Role::query()->pluck('name')->all())->toEqualCanonicalizing(SystemRole::names())
        ->and($manager->permissions()->pluck('name')->all())->not->toContain(...BATCH_ONE_EAIC_PERMISSIONS)
        ->and($staff->permissions()->pluck('name')->all())->not->toContain(...BATCH_ONE_EAIC_PERMISSIONS)
        ->and($manager->hasPermissionTo('users.view'))->toBeTrue()
        ->and($staff->hasPermissionTo('dashboard.view'))->toBeTrue();
});

test('future EAIC permissions are not seeded in the Batch 1 foundation', function () {
    $this->seed(RolePermissionSeeder::class);

    expect(Permission::query()
        ->whereIn('name', [
            'application.view',
            'assignment.create',
            'conflict.determine',
            'evaluation.finalize',
            'deliberation.manage',
            'decision.finalize',
        ])
        ->exists())->toBeFalse();
});
