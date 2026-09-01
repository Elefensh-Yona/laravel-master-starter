<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationValidation;
use App\Models\ApplicationVersion;
use App\Models\Program;
use App\Models\ProgramMembership;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class EligibilityValidationController extends Controller
{
    /**
     * List validation history for an application.
     */
    public function index(Request $request, Application $application): Response
    {
        $this->authorize('view', $application);

        $actor = $request->user();
        $program = $application->program;

        // Get validations for this application, ordered by execution time
        $validations = $application->validations()
            ->with(['program', 'executor'])
            ->orderBy('executed_at', 'desc')
            ->get()
            ->map(fn (ApplicationValidation $v): array => $this->validationSummary($v))
            ->values();

        // Determine if user can trigger validation (must have permission and program staff scope)
        $canValidate = false;
        if ($actor?->can('eligibility.validate')) {
            $canValidate = ProgramMembership::query()
                ->where('program_id', $program->id)
                ->where('user_id', $actor->id)
                ->where('capability', 'program_staff')
                ->where('status', 'active')
                ->exists();
        }

        // Get submitted versions for validation context
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

        return Inertia::render('applications/eligibility/Index', [
            'application' => [
                'id' => $application->id,
                'programId' => $application->program_id,
                'status' => $application->status,
            ],
            'validations' => $validations,
            'submittedVersions' => $submittedVersions,
            'canValidate' => $canValidate,
        ]);
    }

    /**
     * Show a specific validation result.
     */
    public function show(Request $request, Application $application, ApplicationValidation $validation): Response
    {
        $this->authorize('view', $application);
        $this->authorize('view', $validation);

        return Inertia::render('applications/eligibility/Show', [
            'application' => [
                'id' => $application->id,
                'programId' => $application->program_id,
                'status' => $application->status,
            ],
            'validation' => $this->validationSummary($validation->load(['program', 'applicationVersion', 'executor'])),
        ]);
    }

    /**
     * Trigger an eligibility validation for an application version.
     */
    public function store(Request $request, Application $application): RedirectResponse
    {
        $this->authorize('view', $application);

        $actor = $request->user();
        $program = $application->program;

        // Authorization: must have eligibility.validate permission and be program staff
        if (! $actor->can('eligibility.validate')) {
            abort(403, 'You do not have permission to validate applications.');
        }

        // Check program staff scope
        $hasScope = ProgramMembership::query()
            ->where('program_id', $program->id)
            ->where('user_id', $actor->id)
            ->where('capability', 'program_staff')
            ->where('status', 'active')
            ->exists();

        if (! $hasScope) {
            abort(403, 'You are not authorized to validate applications in this program.');
        }

        // Validate input
        $validated = $request->validate([
            'application_version_id' => 'required|exists:application_versions,id',
        ]);

        $version = ApplicationVersion::findOrFail($validated['application_version_id']);

        // Ensure version belongs to this application
        if ($version->application_id !== $application->id) {
            return back()->with('error', 'Invalid application version.');
        }

        // Ensure version is submitted
        if ($version->status !== 'submitted') {
            return back()->with('error', 'Only submitted versions can be validated.');
        }

        // Perform validation in a transaction
        $validation = DB::transaction(function () use ($actor, $application, $version): ApplicationValidation {
            // Run objective validation (simplified for now)
            $result = $this->runEligibilityValidation($application, $version);

            $validation = ApplicationValidation::query()->create([
                'program_id' => $application->program_id,
                'application_id' => $application->id,
                'application_version_id' => $version->id,
                'status' => $result['status'],
                'result' => $result['result'] ?? null,
                'executed_at' => now(),
                'executed_by' => $actor->id,
                'failure_reason' => $result['failure_reason'] ?? null,
            ]);

            return $validation;
        });

        ActivityLogger::record(
            actor: $actor,
            event: 'eligibility.validated',
            description: "Ran eligibility validation for application {$application->id} version {$version->version_number}.",
            subject: $application,
            request: $request,
        );

        return to_route('eligibility-validations.show', [
            'application' => $application,
            'validation' => $validation,
        ])->with('success', 'Eligibility validation completed.');
    }

    /**
     * Run the objective eligibility validation logic.
     *
     * @return array{status: string, result?: array, failure_reason?: string}
     */
    private function runEligibilityValidation(Application $application, ApplicationVersion $version): array
    {
        try {
            $program = $application->program;
            $rules = $program->eligibilityRules;

            $result = [];
            $passed = true;

            foreach ($rules as $rule) {
                // Each rule should have a configuration and logic to validate
                $ruleResult = $this->evaluateRule($rule, $version->content);
                $result[$rule->key] = $ruleResult;

                if (! $ruleResult['passed']) {
                    $passed = false;
                }
            }

            if ($passed) {
                return [
                    'status' => 'passed',
                    'result' => $result,
                ];
            } else {
                return [
                    'status' => 'failed',
                    'result' => $result,
                    'failure_reason' => 'One or more eligibility rules failed.',
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'failure_reason' => 'Validation error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Evaluate a single eligibility rule.
     *
     * @param  mixed  $rule
     * @param  array  $content
     * @return array{passed: bool, message: string}
     */
    private function evaluateRule($rule, $content): array
    {
        // This is a placeholder implementation.
        // In production, this would apply actual rule logic from the rule configuration.
        // For now, we assume all rules pass.
        return [
            'passed' => true,
            'message' => 'Rule passed.',
        ];
    }

    /**
     * @return array{id: int, programId: int, applicationId: int, applicationVersionId: int, status: string, result: array|null, executedAt: string, executedBy: int|null}
     */
    private function validationSummary(ApplicationValidation $validation): array
    {
        return [
            'id' => $validation->id,
            'programId' => $validation->program_id,
            'applicationId' => $validation->application_id,
            'applicationVersionId' => $validation->application_version_id,
            'status' => $validation->status,
            'result' => $validation->result,
            'executedAt' => $validation->executed_at->toIso8601String(),
            'executedBy' => $validation->executed_by,
            'failureReason' => $validation->failure_reason,
        ];
    }
}
