<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateApplicationRequest;
use App\Http\Requests\StoreApplicationVersionRequest;
use App\Http\Requests\SubmitApplicationVersionRequest;
use App\Models\Application;
use App\Models\ApplicationMember;
use App\Models\ApplicationVersion;
use App\Models\Program;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Application::class);

        $actor = $request->user();

        $applications = Application::query()
            ->when(
                $actor?->can('application.view'),
                fn (Builder $query): Builder => $query,
                fn (Builder $query): Builder => $query->where('primary_owner_id', $actor->id),
            )
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Application $app): array => $this->applicationSummary($app, $actor))
            ->values();

        return Inertia::render('applications/Index', [
            'applications' => $applications,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Application::class);

        $actor = $request->user();

        $programs = Program::query()
            ->where('status', 'published')
            ->get()
            ->map(fn (Program $p): array => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
            ])
            ->values();

        return Inertia::render('applications/Create', [
            'programs' => $programs,
        ]);
    }

    public function store(CreateApplicationRequest $request): RedirectResponse
    {
        $this->authorize('create', Application::class);

        $validated = $request->validated();
        $actor = $request->user();
        $program = Program::findOrFail($validated['program_id']);

        $application = DB::transaction(function () use ($actor, $validated, $program): Application {
            $application = Application::query()->create([
                'program_id' => $program->id,
                'primary_owner_id' => $actor->id,
                'applicant_type' => $validated['applicant_type'],
                'status' => 'draft',
                'reference' => $validated['reference'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
            ]);

            $version = ApplicationVersion::query()->create([
                'application_id' => $application->id,
                'version_number' => 1,
                'status' => 'draft',
                'content' => [],
                'created_by' => $actor->id,
                'metadata' => ['source' => 'initial_draft'],
            ]);

            $application->update(['current_version_id' => $version->id]);

            return $application;
        });

        ActivityLogger::record(
            actor: $actor,
            event: 'applications.created',
            description: "Created application for program {$program->name}.",
            subject: $application,
            request: $request,
        );

        return to_route('applications.show', $application)->with('success', 'Application created successfully.');
    }

    public function show(Request $request, Application $application): Response
    {
        $this->authorize('view', $application);

        $actor = $request->user();
        $currentVersion = $application->currentVersion;
        $canManageMembers = $actor?->can('update', $application) ?? false;

        $members = $application->members()
            ->with(['user'])
            ->orderBy('joined_at')
            ->get()
            ->map(fn (ApplicationMember $member): array => [
                'id' => $member->id,
                'applicationId' => $member->application_id,
                'userId' => $member->user_id,
                'userName' => $member->user?->name,
                'userEmail' => $member->user?->email,
                'status' => $member->status,
                'joinedAt' => $member->joined_at?->toIso8601String(),
                'endedAt' => $member->ended_at?->toIso8601String(),
                'endReason' => $member->end_reason,
            ])
            ->values()
            ->all();

        $memberUsers = $canManageMembers
            ? User::query()
                ->select(['id', 'name', 'email'])
                ->orderBy('name')
                ->get()
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ])
                ->values()
                ->all()
            : [];

        return Inertia::render('applications/Show', [
            'application' => $this->applicationSummary($application, $actor),
            'currentVersion' => $currentVersion ? $this->versionSummary($currentVersion) : null,
            'members' => $members,
            'memberUsers' => $memberUsers,
            'canManageMembers' => $canManageMembers,
            'canEdit' => $actor?->can('update', $application) ?? false,
            'canSubmit' => $actor?->can('submit', $application) ?? false && $currentVersion?->status === 'draft',
            'canRevise' => $actor?->can('update', $application) ?? false && $currentVersion?->status === 'submitted',
        ]);
    }

    public function edit(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $currentVersion = $application->currentVersion;

        if ($currentVersion?->status !== 'draft') {
            return to_route('applications.show', $application)->with('error', 'Only draft versions can be edited.');
        }

        return Inertia::render('applications/Edit', [
            'application' => $this->applicationSummary($application, $request->user()),
            'currentVersion' => $this->versionSummary($currentVersion),
        ]);
    }

    public function update(StoreApplicationVersionRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $validated = $request->validated();

        $currentVersion = $application->currentVersion;
        if ($currentVersion?->status !== 'draft') {
            return to_route('applications.show', $application)->with('error', 'Only draft versions can be edited.');
        }

        $currentVersion->update([
            'content' => $validated['content'],
            'metadata' => $validated['metadata'] ?? $currentVersion->metadata,
        ]);

        ActivityLogger::record(
            actor: $request->user(),
            event: 'applications.version_updated',
            description: "Updated application version {$currentVersion->version_number}.",
            subject: $application,
            request: $request,
        );

        return to_route('applications.show', $application)->with('success', 'Application version updated successfully.');
    }

    public function submit(SubmitApplicationVersionRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('submit', $application);

        $validated = $request->validated();

        $currentVersion = $application->currentVersion;
        if ($currentVersion?->status !== 'draft') {
            return to_route('applications.show', $application)->with('error', 'Only draft versions can be submitted.');
        }

        DB::transaction(function () use ($request, $application, $currentVersion): void {
            $currentVersion->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'submitted_by' => $request->user()->id,
            ]);

            $application->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        });

        ActivityLogger::record(
            actor: $request->user(),
            event: 'applications.submitted',
            description: "Submitted application version {$currentVersion->version_number}.",
            subject: $application,
            request: $request,
        );

        return to_route('applications.show', $application)->with('success', 'Application submitted successfully.');
    }

    public function revise(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $currentVersion = $application->currentVersion;
        if ($currentVersion?->status !== 'submitted') {
            return to_route('applications.show', $application)->with('error', 'Only submitted versions can be revised.');
        }

        $newVersion = DB::transaction(function () use ($request, $application, $currentVersion): ApplicationVersion {
            $nextVersionNumber = $application->versions()->max('version_number') + 1;

            $newVersion = ApplicationVersion::query()->create([
                'application_id' => $application->id,
                'version_number' => $nextVersionNumber,
                'status' => 'draft',
                'content' => $currentVersion->content,
                'created_by' => $request->user()->id,
                'supersedes_version_id' => $currentVersion->id,
                'metadata' => ['source' => 'revision'],
            ]);

            $application->update(['current_version_id' => $newVersion->id]);

            return $newVersion;
        });

        ActivityLogger::record(
            actor: $request->user(),
            event: 'applications.revision_created',
            description: "Created revision version {$newVersion->version_number}.",
            subject: $application,
            request: $request,
        );

        return to_route('applications.show', $application)->with('success', 'Revision created successfully.');
    }

    /**
     * @return array{id: int, programId: int, primaryOwnerId: int, applicantType: string, status: string, reference: string|null, submittedAt: string|null, createdAt: string}
     */
    private function applicationSummary(Application $application, ?User $actor): array
    {
        return [
            'id' => $application->id,
            'programId' => $application->program_id,
            'primaryOwnerId' => $application->primary_owner_id,
            'applicantType' => $application->applicant_type,
            'status' => $application->status,
            'reference' => $application->reference,
            'submittedAt' => $application->submitted_at?->toIso8601String(),
            'createdAt' => $application->created_at->toIso8601String(),
        ];
    }

    /**
     * @return array{id: int, applicationId: int, versionNumber: int, status: string, submittedAt: string|null}
     */
    private function versionSummary(ApplicationVersion $version): array
    {
        return [
            'id' => $version->id,
            'applicationId' => $version->application_id,
            'versionNumber' => $version->version_number,
            'status' => $version->status,
            'submittedAt' => $version->submitted_at?->toIso8601String(),
        ];
    }
}
