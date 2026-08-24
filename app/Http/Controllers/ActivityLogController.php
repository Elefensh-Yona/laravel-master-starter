<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    /**
     * Display the activity log index.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $event = $request->string('event')->trim()->toString();

        $logs = ActivityLog::query()
            ->with('actor')
            ->when($search !== '', fn ($query) => $query->searchLike(['description', 'event'], $search))
            ->when($event !== '', fn ($query) => $query->where('event', $event))
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (ActivityLog $log): array => [
                'id' => $log->id,
                'event' => $log->event,
                'description' => $log->description,
                'actor' => $log->actor?->name,
                'actorEmail' => $log->actor?->email,
                'subjectType' => $log->subject_type,
                'subjectId' => $log->subject_id,
                'ipAddress' => $log->ip_address,
                'createdAt' => $log->created_at?->toISOString(),
                'properties' => $log->properties ?? [],
            ]);

        return Inertia::render('activity-logs/Index', [
            'logs' => $logs,
            'filters' => [
                'search' => $search,
                'event' => $event,
            ],
            'eventOptions' => ActivityLog::query()
                ->select('event')
                ->distinct()
                ->orderBy('event')
                ->pluck('event')
                ->values()
                ->all(),
        ]);
    }

    /**
     * Display a single activity log entry.
     */
    public function show(ActivityLog $activityLog): Response
    {
        $activityLog->load('actor');

        return Inertia::render('activity-logs/Show', [
            'log' => [
                'id' => $activityLog->id,
                'event' => $activityLog->event,
                'description' => $activityLog->description,
                'actor' => $activityLog->actor?->name,
                'actorEmail' => $activityLog->actor?->email,
                'subjectType' => $activityLog->subject_type,
                'subjectId' => $activityLog->subject_id,
                'ipAddress' => $activityLog->ip_address,
                'createdAt' => $activityLog->created_at?->toISOString(),
                'properties' => $activityLog->properties ?? [],
            ],
        ]);
    }
}
