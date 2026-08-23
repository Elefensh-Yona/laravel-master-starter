<?php

use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\SystemMessageNotification;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

test('admin global search returns grouped results across accessible modules', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    User::factory()->create([
        'name' => 'Searchable Person',
        'email' => 'searchable@example.com',
    ])->assignRole('Staff');

    Role::query()->create([
        'name' => 'SearchableRole',
        'guard_name' => 'web',
        'description' => 'Role used for global search coverage.',
    ]);

    $admin->notify(new SystemMessageNotification(
        title: 'Searchable notice',
        message: 'Notification message for search tests.',
    ));

    ActivityLog::factory()->create([
        'actor_id' => $admin->id,
        'event' => 'search.searchable',
        'description' => 'Searchable activity entry.',
    ]);

    $this->actingAs($admin)
        ->get(route('search.index', ['q' => 'Searchable']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('search/Index')
            ->where('filters.q', 'Searchable')
            ->has('results', 4)
            ->where('results.0.key', 'users')
            ->where('results.1.key', 'roles')
            ->where('results.2.key', 'notifications')
            ->where('results.3.key', 'activity-logs'),
        );
});

test('member global search only returns modules allowed to that role', function () {
    $this->seed(RolePermissionSeeder::class);

    $member = User::factory()->create();
    $member->assignRole('Staff');

    User::factory()->create([
        'name' => 'Visible User',
        'email' => 'visible-user@example.com',
    ])->assignRole('Staff');

    $member->notify(new SystemMessageNotification(
        title: 'Visible notice',
        message: 'Only notifications should show for this member search.',
    ));

    ActivityLog::factory()->create([
        'event' => 'visible.member-log',
        'description' => 'This should not be visible to member search.',
    ]);

    $this->actingAs($member)
        ->get(route('search.index', ['q' => 'Visible']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('search/Index')
            ->where('filters.q', 'Visible')
            ->has('results', 1)
            ->where('results.0.key', 'notifications'),
        );
});
