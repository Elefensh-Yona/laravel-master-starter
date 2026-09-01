<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\Program;
use App\Models\ProgramMembership;
use App\Models\Screening;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ScreeningController extends Controller
{
    /**
     * List screening history for an application.
     */
    public function index(Request $request, Application $application): Response
    {
        $this->authorize('view', $application);

        $actor = $request->user();
        $program = $application->program;

        // Get screenings for this application, ordered by most recent
        $screenings = $application->screenings()
            ->with(['program', 'screener', 'reopenedBy', 'validation'])
            ->orderBy('completed_at', 'desc')
            ->get()
            ->map(fn (Screening $s): array => $this->screeningSummary($s))
            ->values();

        // Determine if user can perform screening (must have permission and program staff scope)
        $canScreen = false;
        if ($actor?->can('eligibility.screen')) {
            $canScreen = ProgramMembership::query()
                ->where('program_id', $program->id)
                ->where('user_id', $actor->id)
                ->where('capability', 'program_staff')
                ->where('status', 'active')
                ->exists();
        }

        // Get submitted versions for screening context
        $submittedVersions = $application->versions()
            ->where('status', 'submitted')
            ->orderBy('version_number', 'desc')
            ->get()
            ->map(fn (ApplicationVersion $v): array => [
                'id' => $v->id,
                'versionNumber' => $v->version_number,
                'status' => $v->status,
                'submittedAt' => $v->submitted_at?->toIso8601String(),
            ])
            ->values();

        // Get latest validation for context (if available)
        $latestValidation = $application->validations()
            ->orderBy('executed_at', 'desc')
            ->first();

        return Inertia::render('applications/screening/Index', [
            'application' => [
                'id' => $application->id,
                'programId' => $application->program_id,
                'status' => $application->status,
            ],
            'screenings' => $screenings,
            'submittedVersions' => $submittedVersions,
            'latestValidation' => $latestValidation ? [
                'id' => $latestValidation->id,
                'status' => $latestValidation->status,
                'executedAt' => $latestValidation->executed_at->toIso8601String(),
            ] : null,
            'canScreen' => $canScreen,
        ]);
    }

    /**
     * Show a specific screening.
     */
    public function show(Request $request, Application $application, Screening $screening): Response
    {
        $this->authorize('view', $application);
        $this->authorize('view', $screening);

        return Inertia::render('applications/screening/Show', [
            'application' => [
                'id' => $application->id,
                'programId' => $application->program_id,
                'status' => $application->status,
            ],
            'screening' => $this->screeningSummary($screening->load(['program', 'applicationVersion', 'screener', 'validation'])),
        ]);
    }

    /**
     * Create a new screening record (initially in_review state).
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('view', $application);

        $actor = $request->user();
        $program = $application->program;

        // Authorization: must have eligibility.screen permission and be program staff
        if (! $actor->can('eligibility.screen')) {
            abort(403, 'You do not have permission to perform screening.');
        }

        // Check program staff scope
        $hasScope = ProgramMembership::query()
            ->where('program_id', $program->id)
            ->where('user_id', $actor->id)
            ->where('capability', 'program_staff')
            ->where('status', 'active')
            ->exists();

        if (! $hasScope) {
            abort(403, 'You are not authorized to perform screening in this program.');
        }

        // Validate input
        $validated = $request->validate([
            'application_version_id' => 'required|exists:application_versions,id',
            'validation_id' => 'nullable|exists:application_validations,id',
        ]);

        $version = ApplicationVersion::findOrFail($validated['application_version_id']);

        // Ensure version belongs to this application
        if ($version->application_id !== $application->id) {
            return back()->with('error', 'Invalid application version.');
        }

        // Ensure version is submitted
        if ($version->status !== 'submitted') {
            return back()->with('error', 'Only submitted versions can be screened.');
        }

        // Check if screening already exists for this version in completed state
        $existingCompleted = Screening::query()
            ->where('application_version_id', $version->id)
            ->where('status', 'completed')
            ->exists();

        if ($existingCompleted) {
            return back()->with('error', 'A completed screening already exists for this version.');
        }

        // Create screening in transaction
        $screening = DB::transaction(function () use ($actor, $application, $version, $validated): Screening {
            $screening = Screening::query()->create([
                'program_id' => $application->program_id,
                'application_id' => $application->id,
                'application_version_id' => $version->id,
                'validation_id' => $validated['validation_id'] ?? null,
                'status' => 'in_review',
                'screened_by' => $actor->id,
            ]);

            return $screening;
        });

        ActivityLogger::record(
            actor: $actor,
            event: 'screening.created',
            description: "Created screening for application {$application->id} version {$version->version_number}.",
            subject: $application,
            request: $request,
        );

        return to_route('screenings.show', [
            'application' => $application,
            'screening' => $screening,
        ])->with('success', 'Screening created.');
    }

    /**
     * Complete a screening with outcome and rationale.
     */
    public function update(Request $request, Application $application, Screening $screening): RedirectResponse
    {
        $this->authorize('view', $application);
        $this->authorize('update', $screening);

        $actor = $request->user();

        // Validate input
        $validated = $request->validate([
            'outcome' => 'required|in:ELIGIBLE,INELIGIBLE',
            'rationale' => 'required|string|max:2000',
        ]);

        // Ensure screening is in in_review state
        if ($screening->status !== 'in_review') {
            return back()->with('error', 'Only in-review screenings can be completed.');
        }

        // Update screening in transaction
        DB::transaction(function () use ($screening, $validated, $application): void {
            $screening->update([
                'status' => 'completed',
                'outcome' => $validated['outcome'],
                'rationale' => $validated['rationale'],
                'completed_at' => now(),
            ]);

            // Update application status to reflect screening outcome
            $application->update([
                'status' => strtolower($validated['outcome']),
            ]);
        });

        ActivityLogger::record(
            actor: $actor,
            event: 'screening.completed',
            description: "Completed screening for application {$screening->application_id} with outcome: {$validated['outcome']}.",
            subject: $screening->application,
            request: $request,
        );

        return to_route('screenings.show', [
            'application' => $application,
            'screening' => $screening,
        ])->with('success', 'Screening completed.');
    }

    /**
     * @return array{id: int, programId: int, applicationId: int, applicationVersionId: int, status: string, outcome: string|null, rationale: string|null, screenedBy: int, completedAt: string|null, reopenedAt: string|null, reopenedBy: int|null}
     */
    private function screeningSummary(Screening $screening): array
    {
        return [
            'id' => $screening->id,
            'programId' => $screening->program_id,
            'applicationId' => $screening->application_id,
            'applicationVersionId' => $screening->application_version_id,
            'validationId' => $screening->validation_id,
            'status' => $screening->status,
            'outcome' => $screening->outcome,
            'rationale' => $screening->rationale,
            'screenedBy' => $screening->screened_by,
            'completedAt' => $screening->completed_at?->toIso8601String(),
            'reopenedAt' => $screening->reopened_at?->toIso8601String(),
            'reopenedBy' => $screening->reopened_by,
        ];
    }
}
