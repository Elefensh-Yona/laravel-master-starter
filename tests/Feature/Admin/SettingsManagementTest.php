<?php

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

test('super admin can view the settings editor', function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin)
        ->get(route('admin-settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Settings/Edit')
            ->has('settingGroups', 2),
        );
});

test('super admin can update shared application and organization settings', function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);

    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $this->actingAs($admin)
        ->put(route('admin-settings.update'), [
            'app_display_name' => 'Acme Operations',
            'app_tagline' => 'A stronger operations workspace.',
            'support_email' => 'support@acme.test',
            'organization_name' => 'Acme Group',
            'organization_legal_name' => 'Acme Group PLC',
            'organization_email' => 'hello@acme.test',
            'organization_phone' => '+251911000000',
        ])
        ->assertRedirect(route('admin-settings.edit'));

    expect(Setting::query()->where('key', 'app_display_name')->value('value'))->toBe('Acme Operations')
        ->and(Setting::query()->where('key', 'organization_name')->value('value'))->toBe('Acme Group');

    $this->get(route('admin-settings.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('name', 'Acme Operations')
            ->where('settings.organizationName', 'Acme Group'),
        );
});

test('manager can view but not update settings', function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);

    $manager = User::factory()->create();
    $manager->assignRole('Manager');

    $this->actingAs($manager)
        ->get(route('admin-settings.edit'))
        ->assertOk();

    $this->actingAs($manager)
        ->put(route('admin-settings.update'), [
            'app_display_name' => 'Should fail',
            'app_tagline' => '',
            'support_email' => '',
            'organization_name' => '',
            'organization_legal_name' => '',
            'organization_email' => '',
            'organization_phone' => '',
        ])
        ->assertForbidden();
});

test('staff cannot access settings administration', function () {
    $this->seed([RolePermissionSeeder::class, SettingsSeeder::class]);

    $member = User::factory()->create();
    $member->assignRole('Staff');

    $this->actingAs($member)
        ->get(route('admin-settings.edit'))
        ->assertForbidden();
});
