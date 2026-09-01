<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\Program;
use App\Models\ProgramEligibilityRule;
use App\Models\ProgramMembership;
use App\Models\Rubric;
use App\Models\User;
use App\Support\SystemRole;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManualQaFixtureSeeder extends Seeder
{
    /**
     * Local development-only QA fixture for Manual QA Checkpoint #1.
     */
    public function run(): void
    {
        $qaPassword = 'DevelopmentQa123!';

        $superAdmin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $superAdmin->syncRoles([SystemRole::SUPER_ADMIN]);

        $programStaff = User::query()->firstOrCreate(
            ['email' => 'qa-program-staff@example.com'],
            [
                'name' => 'QA Program Staff',
                'password' => Hash::make($qaPassword),
                'email_verified_at' => now(),
            ],
        );

        $decisionMaker = User::query()->firstOrCreate(
            ['email' => 'qa-decision-maker@example.com'],
            [
                'name' => 'QA Decision Maker',
                'password' => Hash::make($qaPassword),
                'email_verified_at' => now(),
            ],
        );

        $judge = User::query()->firstOrCreate(
            ['email' => 'qa-judge@example.com'],
            [
                'name' => 'QA Judge',
                'password' => Hash::make($qaPassword),
                'email_verified_at' => now(),
            ],
        );

        $applicant = User::query()->firstOrCreate(
            ['email' => 'qa-applicant@example.com'],
            [
                'name' => 'QA Applicant',
                'password' => Hash::make($qaPassword),
                'email_verified_at' => now(),
            ],
        );

        $programStaff->givePermissionTo([
            'program.view',
            'program.create',
            'program.update',
            'program.publish',
            'application.view',
            'eligibility.view',
            'eligibility.validate',
            'eligibility.screen',
            'rubric.view',
            'rubric.create',
            'rubric.update',
        ]);

        $decisionMaker->syncRoles([]);
        $judge->syncRoles([]);
        $applicant->syncRoles([]);

        $programAOpensAt = CarbonImmutable::now()->addWeeks(2);
        $programAClosesAt = $programAOpensAt->clone()->addMonth();

        $programA = Program::query()->firstOrCreate(
            ['code' => 'EAIC-2026-01'],
            [
                'name' => 'EAIC Innovation Challenge 2026',
                'slug' => 'eaic-innovation-challenge-2026',
                'status' => 'draft',
                'timezone' => 'Africa/Addis_Ababa',
                'opens_at' => $programAOpensAt,
                'closes_at' => $programAClosesAt,
                'created_by' => $superAdmin->id,
                'description' => 'A development QA program to exercise Program administration, publication, and program-scoped actions.',
                'metadata' => ['source' => 'manual-qa-fixture'],
            ],
        );

        $programBOpensAt = CarbonImmutable::now()->addMonth();
        $programBClosesAt = $programBOpensAt->clone()->addMonth();

        $programB = Program::query()->firstOrCreate(
            ['code' => 'EAIC-2026-02'],
            [
                'name' => 'EAIC Regional Challenge 2026',
                'slug' => 'eaic-regional-challenge-2026',
                'status' => 'draft',
                'timezone' => 'Africa/Addis_Ababa',
                'opens_at' => $programBOpensAt,
                'closes_at' => $programBClosesAt,
                'created_by' => $superAdmin->id,
                'description' => 'A second QA program used to test cross-program separation and out-of-scope visibility.',
                'metadata' => ['source' => 'manual-qa-fixture'],
            ],
        );

        ProgramMembership::query()->firstOrCreate(
            [
                'program_id' => $programA->id,
                'user_id' => $programStaff->id,
                'capability' => 'program_staff',
            ],
            [
                'status' => 'active',
                'starts_at' => now()->subDay(),
                'granted_by' => $superAdmin->id,
                'metadata' => ['source' => 'manual-qa-fixture'],
            ],
        );

        ProgramEligibilityRule::query()->firstOrCreate(
            [
                'program_id' => $programA->id,
                'key' => 'age_18_plus',
            ],
            [
                'label' => 'Age 18 or older',
                'rule_type' => 'boolean',
                'configuration' => ['expected' => true],
                'position' => 1,
                'is_required' => true,
                'is_enabled' => true,
                'description' => 'Development QA rule for program screening.',
            ],
        );

        ProgramEligibilityRule::query()->firstOrCreate(
            [
                'program_id' => $programB->id,
                'key' => 'team_size_confirmation',
            ],
            [
                'label' => 'Team size confirmation',
                'rule_type' => 'boolean',
                'configuration' => ['expected' => true],
                'position' => 1,
                'is_required' => true,
                'is_enabled' => true,
                'description' => 'Second QA rule to test a separate program setup.',
            ],
        );

        Rubric::query()->firstOrCreate(
            [
                'program_id' => $programA->id,
                'name' => 'EAIC 2026 Innovation Rubric',
            ],
            [
                'status' => 'draft',
                'created_by' => $superAdmin->id,
                'description' => 'Development QA rubric for the Program A workflow.',
                'metadata' => ['source' => 'manual-qa-fixture'],
            ],
        );

        Rubric::query()->firstOrCreate(
            [
                'program_id' => $programB->id,
                'name' => 'EAIC 2026 Regional Rubric',
            ],
            [
                'status' => 'draft',
                'created_by' => $superAdmin->id,
                'description' => 'Development QA rubric for the Program B workflow.',
                'metadata' => ['source' => 'manual-qa-fixture'],
            ],
        );

        $draftApplication = Application::query()->firstOrCreate(
            [
                'program_id' => $programA->id,
                'reference' => 'QA-APPLICATION-A-DRAFT',
            ],
            [
                'primary_owner_id' => $applicant->id,
                'applicant_type' => 'INDIVIDUAL',
                'status' => 'draft',
                'metadata' => ['source' => 'manual-qa-fixture', 'scenario' => 'draft-application'],
            ],
        );

        $this->seedApplicationVersion(
            application: $draftApplication,
            owner: $applicant,
            status: 'draft',
            submittedAt: null,
            content: ['summary' => 'QA Application A - Draft'],
        );

        $submittedAt = CarbonImmutable::create(2026, 8, 15, 12, 0, 0, 'UTC');
        $submittedApplication = Application::query()->firstOrCreate(
            [
                'program_id' => $programA->id,
                'reference' => 'QA-APPLICATION-B-SUBMITTED',
            ],
            [
                'primary_owner_id' => $applicant->id,
                'applicant_type' => 'INDIVIDUAL',
                'status' => 'submitted',
                'submitted_at' => $submittedAt,
                'metadata' => ['source' => 'manual-qa-fixture', 'scenario' => 'submitted-application'],
            ],
        );

        $this->seedApplicationVersion(
            application: $submittedApplication,
            owner: $applicant,
            status: 'submitted',
            submittedAt: $submittedAt,
            content: ['summary' => 'QA Application B - Submitted'],
        );

        $programBApplication = Application::query()->firstOrCreate(
            [
                'program_id' => $programB->id,
                'reference' => 'QA-APPLICATION-C-PROGRAM-B-SCOPE',
            ],
            [
                'primary_owner_id' => $applicant->id,
                'applicant_type' => 'INDIVIDUAL',
                'status' => 'submitted',
                'submitted_at' => $submittedAt,
                'metadata' => ['source' => 'manual-qa-fixture', 'scenario' => 'program-b-scope'],
            ],
        );

        $this->seedApplicationVersion(
            application: $programBApplication,
            owner: $applicant,
            status: 'submitted',
            submittedAt: $submittedAt,
            content: ['summary' => 'QA Application C - Program B Scope'],
        );

        $this->command->info('Manual QA fixture created for Super Admin, Program Staff, Decision Maker, Judge, and Applicant.');
        $this->command->info('QA password: DevelopmentQa123! (development/testing only)');
    }

    /**
     * @param  array<string, string>  $content
     */
    private function seedApplicationVersion(
        Application $application,
        User $owner,
        string $status,
        ?CarbonImmutable $submittedAt,
        array $content,
    ): void {
        $version = ApplicationVersion::query()->firstOrCreate(
            [
                'application_id' => $application->id,
                'version_number' => 1,
            ],
            [
                'status' => $status,
                'content' => $content,
                'created_by' => $owner->id,
                'submitted_at' => $submittedAt,
                'submitted_by' => $submittedAt === null ? null : $owner->id,
                'metadata' => ['source' => 'manual-qa-fixture'],
            ],
        );

        if ($application->current_version_id === null) {
            $application->update(['current_version_id' => $version->id]);
        }
    }
}
