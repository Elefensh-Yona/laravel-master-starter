<?php

namespace Database\Seeders;

use App\Support\SystemRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Default boilerplate roles that should always exist.
     *
     * @var array<string, array{description: string, permissions: array<int, string>>}
     */
    private const DEFAULT_ROLES = [
        SystemRole::SUPER_ADMIN => [
            'description' => 'Full-access recovery role for managing the entire boilerplate.',
            'permissions' => [
                'dashboard.view',
                'search.view',
                'exports.view',
                'settings.view',
                'settings.update',
                'media.view',
                'media.create',
                'media.delete',
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',
                'notifications.view',
                'activity-logs.view',
            ],
        ],
        SystemRole::MANAGER => [
            'description' => 'Operational role with visibility into shared workspace activity and user administration.',
            'permissions' => [
                'dashboard.view',
                'search.view',
                'exports.view',
                'settings.view',
                'media.view',
                'media.create',
                'users.view',
                'notifications.view',
                'activity-logs.view',
            ],
        ],
        SystemRole::STAFF => [
            'description' => 'Standard internal user with dashboard and notification access.',
            'permissions' => [
                'dashboard.view',
                'search.view',
                'notifications.view',
            ],
        ],
        SystemRole::GUEST => [
            'description' => 'Authenticated account with no dashboard or administrative access by default.',
            'permissions' => [],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'search.view',
            'exports.view',
            'settings.view',
            'settings.update',
            'media.view',
            'media.create',
            'media.delete',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'notifications.view',
            'activity-logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::DEFAULT_ROLES as $name => $definition) {
            $role = Role::query()->updateOrCreate(
                [
                    'name' => $name,
                    'guard_name' => 'web',
                ],
                [
                    'description' => $definition['description'],
                ],
            );

            $role->syncPermissions($definition['permissions']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
