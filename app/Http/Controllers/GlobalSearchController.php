<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchIndexRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class GlobalSearchController extends Controller
{
    /**
     * Display grouped search results across enabled modules.
     */
    public function __invoke(SearchIndexRequest $request): Response
    {
        $query = $request->safe()->string('q')->trim()->toString();
        $user = $request->user();

        return Inertia::render('search/Index', [
            'filters' => [
                'q' => $query,
            ],
            'results' => $query === ''
                ? []
                : $this->buildResults($query, $user),
        ]);
    }

    /**
     * Build grouped search results based on the signed-in user's permissions.
     *
     * @return array<int, array{key: string, title: string, count: int, items: array<int, array{id: string, title: string, description: string, href: string, meta: string|null}>}>
     */
    private function buildResults(string $query, User $user): array
    {
        return collect([
            $user->can('users.view') ? $this->searchUsers($query) : null,
            $user->can('roles.view') ? $this->searchRoles($query) : null,
            $user->can('notifications.view') ? $this->searchNotifications($query, $user) : null,
            $user->can('activity-logs.view') ? $this->searchActivityLogs($query) : null,
        ])
            ->filter(fn (?array $group): bool => $group !== null && $group['count'] > 0)
            ->values()
            ->all();
    }

    /**
     * @return array{key: string, title: string, count: int, items: array<int, array{id: string, title: string, description: string, href: string, meta: string|null}>}
     */
    private function searchUsers(string $query): array
    {
        $items = User::query()
            ->with('roles')
            ->where(function ($userQuery) use ($query): void {
                $userQuery
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (User $user): array => [
                'id' => "user-{$user->id}",
                'title' => $user->name,
                'description' => $user->email,
                'href' => route('users.edit', $user),
                'meta' => $user->roles->pluck('name')->join(', ') ?: null,
            ])
            ->values()
            ->all();

        return [
            'key' => 'users',
            'title' => 'Users',
            'count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * @return array{key: string, title: string, count: int, items: array<int, array{id: string, title: string, description: string, href: string, meta: string|null}>}
     */
    private function searchRoles(string $query): array
    {
        $items = Role::query()
            ->where(function ($roleQuery) use ($query): void {
                $roleQuery
                    ->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(fn (Role $role): array => [
                'id' => "role-{$role->id}",
                'title' => $role->name,
                'description' => $role->description ?? 'Role record',
                'href' => route('roles.edit', $role),
                'meta' => null,
            ])
            ->values()
            ->all();

        return [
            'key' => 'roles',
            'title' => 'Roles',
            'count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * @return array{key: string, title: string, count: int, items: array<int, array{id: string, title: string, description: string, href: string, meta: string|null}>}
     */
    private function searchNotifications(string $query, User $user): array
    {
        $notificationsQuery = $user->notifications();

        $this->applyNotificationSearch($notificationsQuery, $query);

        $items = $notificationsQuery
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($notification): array => [
                'id' => "notification-{$notification->id}",
                'title' => $notification->data['title'] ?? 'Notification',
                'description' => $notification->data['message'] ?? '',
                'href' => route('notifications.index'),
                'meta' => $notification->read_at === null ? 'Unread' : 'Read',
            ])
            ->values()
            ->all();

        return [
            'key' => 'notifications',
            'title' => 'Notifications',
            'count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * @return array{key: string, title: string, count: int, items: array<int, array{id: string, title: string, description: string, href: string, meta: string|null}>}
     */
    private function searchActivityLogs(string $query): array
    {
        $items = ActivityLog::query()
            ->where(function ($logQuery) use ($query): void {
                $logQuery
                    ->where('event', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (ActivityLog $log): array => [
                'id' => "activity-log-{$log->id}",
                'title' => $log->event,
                'description' => $log->description,
                'href' => route('activity-logs.show', $log),
                'meta' => $log->created_at?->toDateTimeString(),
            ])
            ->values()
            ->all();

        return [
            'key' => 'activity-logs',
            'title' => 'Activity logs',
            'count' => count($items),
            'items' => $items,
        ];
    }

    /**
     * Apply a database-safe notification payload search.
     */
    private function applyNotificationSearch(Builder|MorphMany $query, string $term): void
    {
        $pattern = '%'.mb_strtolower($term).'%';

        if ($query->getConnection()->getDriverName() === 'pgsql') {
            $query->where(function (Builder $notificationQuery) use ($pattern): void {
                $notificationQuery
                    ->whereRaw("LOWER(COALESCE((data::jsonb->>'title'), '')) LIKE ?", [$pattern])
                    ->orWhereRaw("LOWER(COALESCE((data::jsonb->>'message'), '')) LIKE ?", [$pattern]);
            });

            return;
        }

        $query->where(function (Builder $notificationQuery) use ($term): void {
            $notificationQuery
                ->where('data->title', 'like', "%{$term}%")
                ->orWhere('data->message', 'like', "%{$term}%");
        });
    }
}
