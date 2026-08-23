<?php

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

test('activity log api lists and shows audit entries', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $log = ActivityLog::factory()->create([
        'actor_id' => $admin->id,
        'event' => 'users.created',
        'description' => 'Created a user from the mobile admin client.',
    ]);

    $token = $admin->createToken('tablet')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/activity-logs?event=users.created')
        ->assertOk()
        ->assertJsonPath('meta.filters.event', 'users.created')
        ->assertJsonPath('meta.pagination.current_page', 1)
        ->assertJsonPath('data.0.id', $log->id);

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson("/api/v1/activity-logs/{$log->id}")
        ->assertOk()
        ->assertJsonPath('data.event', 'users.created')
        ->assertJsonPath('data.description', 'Created a user from the mobile admin client.');
});

test('activity log api enforces audit permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    $member = User::factory()->create();
    $member->assignRole('Staff');

    $token = $member->createToken('phone')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/activity-logs')
        ->assertForbidden();
});
