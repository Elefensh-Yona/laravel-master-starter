<?php

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

test('admin summary api returns core admin counts and breakdowns', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('Super Admin');

    $member = User::factory()->create();
    $member->assignRole('Staff');

    ActivityLog::factory()->count(2)->create([
        'actor_id' => $admin->id,
    ]);

    $token = $admin->createToken('dashboard')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/summary')
        ->assertOk()
        ->assertJsonPath('data.counts.users', User::query()->count())
        ->assertJsonPath('data.counts.roles', Role::query()->count())
        ->assertJsonPath('data.counts.activity_logs', ActivityLog::query()->count())
        ->assertJsonCount(Role::query()->count(), 'data.role_breakdown')
        ->assertJsonCount(2, 'data.recent_users');
});

test('member cannot access the admin summary api endpoint', function () {
    $this->seed(RolePermissionSeeder::class);

    $member = User::factory()->create();
    $member->assignRole('Staff');

    $token = $member->createToken('android')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/admin/summary')
        ->assertForbidden();
});
