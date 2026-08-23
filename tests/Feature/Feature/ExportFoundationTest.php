<?php

use App\Models\ActivityLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('manager can access the export center and print summary', function () {
    $this->seed(RolePermissionSeeder::class);

    $manager = User::factory()->create();
    $manager->assignRole('Manager');

    $this->actingAs($manager)
        ->get(route('exports.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('exports/Index')
            ->has('resources', 2),
        );

    $this->actingAs($manager)
        ->get(route('exports.summary.print'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('exports/PrintSummary')
            ->where('summary.counts.users', User::query()->count()),
        );

    expect(ActivityLog::query()->where('event', 'exports.summary-print')->exists())->toBeTrue();
});

test('admin can download users csv export', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $exportedUser = User::factory()->create([
        'name' => 'CSV User',
        'email' => 'csv-user@example.com',
    ]);
    $exportedUser->assignRole('Staff');

    $response = $this->actingAs($admin)->get(route('exports.users.csv'));

    $response
        ->assertOk()
        ->assertDownload('users-export.csv')
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())->toContain('CSV User');
    expect(ActivityLog::query()->where('event', 'exports.users-csv')->exists())->toBeTrue();
});

test('member cannot access export routes', function () {
    $this->seed(RolePermissionSeeder::class);

    $member = User::factory()->create();
    $member->assignRole('Staff');

    $this->actingAs($member)
        ->get(route('exports.index'))
        ->assertForbidden();

    $this->actingAs($member)
        ->get(route('exports.users.csv'))
        ->assertForbidden();
});
