<?php

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('activity log index displays audit entries and filters by event', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create([
        'name' => 'Audit Admin',
    ]);
    $admin->assignRole('Super Admin');

    ActivityLog::factory()->create([
        'actor_id' => $admin->id,
        'event' => 'users.created',
        'description' => 'Created a new user record.',
    ]);

    ActivityLog::factory()->create([
        'actor_id' => $admin->id,
        'event' => 'roles.updated',
        'description' => 'Updated role details.',
    ]);

    $this->actingAs($admin)
        ->get(route('activity-logs.index', ['event' => 'users.created']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('activity-logs/Index')
            ->where('filters.event', 'users.created')
            ->has('logs.data', 1)
            ->where('logs.data.0.event', 'users.created'),
        );
});

test('manager can access the activity log index but member cannot', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create();
    $manager->assignRole('Manager');

    $member = User::factory()->create();
    $member->assignRole('Staff');

    $this->actingAs($manager)
        ->get(route('activity-logs.index'))
        ->assertOk();

    $this->actingAs($member)
        ->get(route('activity-logs.index'))
        ->assertForbidden();
});

test('activity log detail page displays the recorded payload', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $log = ActivityLog::factory()->create([
        'actor_id' => $admin->id,
        'event' => 'settings.profile-updated',
        'description' => 'Updated profile settings.',
        'subject_type' => User::class,
        'subject_id' => $admin->id,
        'properties' => [
            'email_changed' => true,
        ],
    ]);

    $this->actingAs($admin)
        ->get(route('activity-logs.show', $log))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('activity-logs/Show')
            ->where('log.id', $log->id)
            ->where('log.event', 'settings.profile-updated')
            ->where('log.subjectType', User::class)
            ->where('log.properties.email_changed', true),
        );
});
