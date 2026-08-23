<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Media;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Dashboard', [
            'metrics' => [
                [
                    'key' => 'users',
                    'label' => 'Active users',
                    'value' => User::query()->count(),
                    'description' => 'Signed-in operators and role-managed users in the workspace.',
                    'tone' => 'violet',
                ],
                [
                    'key' => 'roles',
                    'label' => 'Roles',
                    'value' => Role::query()->count(),
                    'description' => 'Access bundles that group permissions for workspace members.',
                    'tone' => 'amber',
                ],
                [
                    'key' => 'media',
                    'label' => 'Media files',
                    'value' => Media::query()->count(),
                    'description' => 'Shared files stored in the reusable media library.',
                    'tone' => 'sky',
                ],
                [
                    'key' => 'activity',
                    'label' => 'Activity events',
                    'value' => ActivityLog::query()->count(),
                    'description' => 'Audited actions recorded across the workspace.',
                    'tone' => 'emerald',
                ],
            ],
            'recentActivity' => ActivityLog::query()
                ->latest('created_at')
                ->limit(6)
                ->get()
                ->map(fn (ActivityLog $log): array => [
                    'id' => $log->id,
                    'event' => $log->event,
                    'description' => $log->description,
                    'createdAt' => $log->created_at?->toDateTimeString(),
                ])
                ->values()
                ->all(),
        ]);
    }
}
