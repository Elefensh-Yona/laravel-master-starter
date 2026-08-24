<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleDetailsRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Support\ActivityLogger;
use App\Support\SystemRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementController extends Controller
{
    /**
     * Show the role management page.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Role::class);

        $search = $request->string('search')->trim()->toString();

        return Inertia::render('admin/Roles/Index', [
            'roles' => Role::query()
                ->withCount('users')
                ->withCount('permissions')
                ->when($search !== '', fn ($query) => $query->searchLike(['name', 'description'], $search))
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Role $role): array => $this->roleSummary($role)),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the role creation page.
     */
    public function create(): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('admin/Roles/Create', [
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    /**
     * Persist a new role.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = Role::query()->create([
            'name' => $request->validated('name'),
            'guard_name' => 'web',
            'description' => $request->validated('description'),
        ]);

        $role->syncPermissions($request->validated('permissions', []));

        ActivityLogger::record(
            actor: $request->user(),
            event: 'roles.created',
            description: "Created role {$role->name}.",
            subject: $role,
            properties: [
                'permissions' => $request->validated('permissions', []),
            ],
            request: $request,
        );

        return to_route('roles.edit', $role)->with('success', 'Role created successfully.');
    }

    /**
     * Show the role edit page.
     */
    public function edit(Role $role): Response
    {
        $this->authorize('view', $role);

        $role->load('permissions');

        return Inertia::render('admin/Roles/Edit', [
            'role' => [
                ...$this->roleSummary($role),
                'permissions' => $role->permissions->pluck('name')->values()->all(),
            ],
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    /**
     * Update the selected role's metadata.
     */
    public function update(UpdateRoleDetailsRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        if ($this->isSystemRole($role) && $role->name !== $request->validated('name')) {
            return to_route('roles.edit', $role)->withErrors([
                'name' => 'System role names are fixed because other access rules depend on them.',
            ]);
        }

        $role->forceFill([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
        ])->save();

        ActivityLogger::record(
            actor: $request->user(),
            event: 'roles.updated',
            description: "Updated role {$role->name} details.",
            subject: $role,
            properties: [
                'description' => $role->description,
            ],
            request: $request,
        );

        return to_route('roles.edit', $role)->with('success', 'Role details updated successfully.');
    }

    /**
     * Update the selected role's permissions.
     */
    public function updatePermissions(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('updatePermissions', $role);

        if ($role->name === SystemRole::SUPER_ADMIN) {
            return to_route('roles.edit', $role)->withErrors([
                'permissions' => 'The Super Admin role is managed automatically and cannot be edited from this screen.',
            ]);
        }

        $role->syncPermissions($request->validated('permissions', []));

        ActivityLogger::record(
            actor: $request->user(),
            event: 'roles.permissions-updated',
            description: "Updated permissions for role {$role->name}.",
            subject: $role,
            properties: [
                'permissions' => $request->validated('permissions', []),
            ],
            request: $request,
        );

        return to_route('roles.edit', $role)->with('success', 'Role permissions updated successfully.');
    }

    /**
     * Delete the selected role.
     */
    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($this->isSystemRole($role)) {
            return to_route('roles.index')->with('error', 'System roles cannot be deleted.');
        }

        ActivityLogger::record(
            actor: $request->user(),
            event: 'roles.deleted',
            description: "Deleted role {$role->name}.",
            subject: $role,
            request: $request,
        );

        $role->delete();

        return to_route('roles.index')->with('success', 'Role deleted successfully.');
    }

    private function permissionLabel(string $permission): string
    {
        return match (str($permission)->after('.')->toString()) {
            'view' => 'View access',
            'create' => 'Create records',
            'update' => 'Update records',
            'delete' => 'Delete records',
            default => str($permission)->after('.')->replace('-', ' ')->title()->toString(),
        };
    }

    private function permissionDescription(string $permission): string
    {
        return match ($permission) {
            'dashboard.view' => 'Allows the role to enter the dashboard workspace.',
            'search.view' => 'Allows use of the shared global search page across enabled modules.',
            'exports.view' => 'Allows access to the shared export and print center.',
            'settings.view' => 'Shows the shared settings workspace and its read-only metadata.',
            'settings.update' => 'Allows updating shared application and organization settings.',
            'media.view' => 'Shows the shared media library and download-ready file listings.',
            'media.create' => 'Allows uploading files into the shared media library for future attachments.',
            'media.delete' => 'Allows deleting files from the shared media library.',
            'users.view' => 'Shows the users module and allows access to the users index page.',
            'users.create' => 'Allows creating new users when user creation is added later.',
            'users.update' => 'Allows editing user details, roles, and future user actions.',
            'users.delete' => 'Allows deleting users when destructive user actions are enabled.',
            'roles.view' => 'Shows the roles module and allows access to role management.',
            'roles.create' => 'Allows creating new roles when custom role creation is added.',
            'roles.update' => 'Allows changing role permissions and future role settings.',
            'roles.delete' => 'Allows deleting removable roles when enabled later.',
            'notifications.view' => 'Shows shared notification features for the signed-in user.',
            'activity-logs.view' => 'Allows access to future activity and audit log pages.',
            default => 'Controls access to this capability across routes, sidebar items, and UI actions.',
        };
    }

    /**
     * Get grouped permissions for the role editor UI.
     *
     * @return array<int, array{key: string, title: string, permissions: array<int, array{name: string, label: string, description: string}>}>
     */
    private function permissionGroups(): array
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission): string => str($permission->name)->before('.')->toString())
            ->map(fn (Collection $permissions, string $group): array => [
                'key' => $group,
                'title' => str($group)->replace('-', ' ')->title()->toString(),
                'permissions' => $permissions->map(fn (Permission $permission): array => [
                    'name' => $permission->name,
                    'label' => $this->permissionLabel($permission->name),
                    'description' => $this->permissionDescription($permission->name),
                ])->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * Normalize a role for table and page props.
     *
     * @return array{id: int, name: string, description: string|null, permissions: array<int, string>, usersCount: int, permissionsCount: int, isSystem: bool, canDelete: bool, isSuperAdmin: bool}
     */
    private function roleSummary(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->relationLoaded('permissions')
                ? $role->permissions->pluck('name')->values()->all()
                : [],
            'usersCount' => $role->users_count ?? $role->users()->count(),
            'permissionsCount' => $role->permissions_count ?? $role->permissions()->count(),
            'isSystem' => $this->isSystemRole($role),
            'canDelete' => ! $this->isSystemRole($role),
            'isSuperAdmin' => $role->name === SystemRole::SUPER_ADMIN,
        ];
    }

    private function isSystemRole(Role $role): bool
    {
        return in_array($role->name, SystemRole::names(), true);
    }
}
