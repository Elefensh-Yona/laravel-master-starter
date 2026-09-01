<?php

use App\Models\Application;
use App\Models\ApplicationValidation;
use App\Models\Program;
use App\Models\ProgramEligibilityRule;
use App\Models\ProgramMembership;
use App\Models\Rubric;
use App\Models\Screening;
use App\Models\User;
use App\Policies\ApplicationValidationPolicy;
use App\Policies\ProgramEligibilityRulePolicy;
use App\Policies\ProgramMembershipPolicy;
use App\Policies\ProgramPolicy;
use App\Policies\RubricPolicy;
use App\Policies\ScreeningPolicy;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function userWithPermissions(string ...$permissions): User
{
    $user = User::factory()->create();

    $user->givePermissionTo($permissions);

    return $user;
}

function activeProgramMembership(User $user, Program $program, string $capability = 'program_staff'): ProgramMembership
{
    return ProgramMembership::factory()->create([
        'program_id' => $program->id,
        'user_id' => $user->id,
        'capability' => $capability,
        'granted_by' => User::factory(),
    ]);
}

test('an authorized program-scoped staff member can view an in-scope program', function () {
    $program = Program::factory()->create();
    $staff = userWithPermissions('program.view');
    activeProgramMembership($staff, $program);

    expect(app(ProgramPolicy::class)->view($staff, $program))->toBeTrue();
});

test('an out-of-scope staff member cannot update another program', function () {
    $inScopeProgram = Program::factory()->create();
    $otherProgram = Program::factory()->create();
    $staff = userWithPermissions('program.update');
    activeProgramMembership($staff, $inScopeProgram);

    expect(app(ProgramPolicy::class)->update($staff, $otherProgram))->toBeFalse();
});

test('a program permission without required scope is denied', function () {
    $program = Program::factory()->create();
    $staff = userWithPermissions('program.update');

    expect(app(ProgramPolicy::class)->update($staff, $program))->toBeFalse();
});

test('an authorized staff member can create a program', function () {
    $existingProgram = Program::factory()->create();
    $staff = userWithPermissions('program.create');
    activeProgramMembership($staff, $existingProgram);

    expect(app(ProgramPolicy::class)->create($staff))->toBeTrue();
});

test('an unauthorized actor cannot create a program', function () {
    $existingProgram = Program::factory()->create();
    $actor = User::factory()->create();
    activeProgramMembership($actor, $existingProgram);

    expect(app(ProgramPolicy::class)->create($actor))->toBeFalse();
});

test('an authorized staff member can publish a draft program with a valid window', function () {
    $program = Program::factory()->create(['status' => 'draft']);
    $staff = userWithPermissions('program.publish');
    activeProgramMembership($staff, $program);

    expect(app(ProgramPolicy::class)->publish($staff, $program))->toBeTrue();
});

test('an unauthorized actor cannot publish a program', function () {
    $program = Program::factory()->create(['status' => 'draft']);
    $actor = User::factory()->create();
    activeProgramMembership($actor, $program);

    expect(app(ProgramPolicy::class)->publish($actor, $program))->toBeFalse();
});

test('a program-scoped staff member can update an active membership in their program', function () {
    $program = Program::factory()->create();
    $staff = userWithPermissions('program.update');
    activeProgramMembership($staff, $program);
    $membership = ProgramMembership::factory()->create(['program_id' => $program->id]);

    expect(app(ProgramMembershipPolicy::class)->update($staff, $membership))->toBeTrue();
});

test('a staff member cannot update a membership in another program', function () {
    $program = Program::factory()->create();
    $otherProgram = Program::factory()->create();
    $staff = userWithPermissions('program.update');
    activeProgramMembership($staff, $program);
    $membership = ProgramMembership::factory()->create(['program_id' => $otherProgram->id]);

    expect(app(ProgramMembershipPolicy::class)->update($staff, $membership))->toBeFalse();
});

test('an inactive membership cannot satisfy active program scope', function () {
    $program = Program::factory()->create();
    $staff = userWithPermissions('program.update');
    ProgramMembership::factory()->create([
        'program_id' => $program->id,
        'user_id' => $staff->id,
        'capability' => 'program_staff',
        'status' => 'ended',
        'granted_by' => User::factory(),
    ]);
    $membership = ProgramMembership::factory()->create(['program_id' => $program->id]);

    expect(app(ProgramMembershipPolicy::class)->update($staff, $membership))->toBeFalse();
});

test('a protected historical membership cannot be updated', function () {
    $program = Program::factory()->create();
    $staff = userWithPermissions('program.update');
    activeProgramMembership($staff, $program);
    $membership = ProgramMembership::factory()->create([
        'program_id' => $program->id,
        'status' => 'ended',
    ]);

    expect(app(ProgramMembershipPolicy::class)->update($staff, $membership))->toBeFalse();
});

test('an authorized staff member can view an in-scope eligibility rule', function () {
    $rule = ProgramEligibilityRule::factory()->create();
    $staff = userWithPermissions('eligibility.view');
    activeProgramMembership($staff, $rule->program);

    expect(app(ProgramEligibilityRulePolicy::class)->view($staff, $rule))->toBeTrue();
});

test('an authorized staff member can validate an enabled eligibility rule', function () {
    $rule = ProgramEligibilityRule::factory()->create();
    $staff = userWithPermissions('eligibility.validate');
    activeProgramMembership($staff, $rule->program);

    expect(app(ProgramEligibilityRulePolicy::class)->validate($staff, $rule))->toBeTrue();
});

test('an authorized staff member can screen within their program', function () {
    $rule = ProgramEligibilityRule::factory()->create();
    $staff = userWithPermissions('eligibility.screen');
    activeProgramMembership($staff, $rule->program);

    expect(app(ProgramEligibilityRulePolicy::class)->screen($staff, $rule))->toBeTrue();
});

test('an applicant cannot screen merely by holding the screening permission', function () {
    $rule = ProgramEligibilityRule::factory()->create();
    $applicant = userWithPermissions('eligibility.screen');
    activeProgramMembership($applicant, $rule->program, 'applicant');

    expect(app(ProgramEligibilityRulePolicy::class)->screen($applicant, $rule))->toBeFalse();
});

test('an out-of-scope staff member cannot screen another program', function () {
    $rule = ProgramEligibilityRule::factory()->create();
    $otherProgram = Program::factory()->create();
    $staff = userWithPermissions('eligibility.screen');
    activeProgramMembership($staff, $otherProgram);

    expect(app(ProgramEligibilityRulePolicy::class)->screen($staff, $rule))->toBeFalse();
});

test('an authorized staff member can view an in-scope rubric', function () {
    $rubric = Rubric::factory()->create();
    $staff = userWithPermissions('rubric.view');
    activeProgramMembership($staff, $rubric->program);

    expect(app(RubricPolicy::class)->view($staff, $rubric))->toBeTrue();
});

test('an authorized staff member can create a rubric in their program', function () {
    $program = Program::factory()->create();
    $staff = userWithPermissions('rubric.create');
    activeProgramMembership($staff, $program);

    expect(app(RubricPolicy::class)->create($staff, $program))->toBeTrue();
});

test('an authorized staff member can update a mutable rubric', function () {
    $rubric = Rubric::factory()->create(['status' => 'draft']);
    $staff = userWithPermissions('rubric.update');
    activeProgramMembership($staff, $rubric->program);

    expect(app(RubricPolicy::class)->update($staff, $rubric))->toBeTrue();
});

test('an out-of-scope staff member cannot update a rubric', function () {
    $rubric = Rubric::factory()->create(['status' => 'draft']);
    $otherProgram = Program::factory()->create();
    $staff = userWithPermissions('rubric.update');
    activeProgramMembership($staff, $otherProgram);

    expect(app(RubricPolicy::class)->update($staff, $rubric))->toBeFalse();
});

test('a frozen rubric cannot be updated', function () {
    $rubric = Rubric::factory()->create(['status' => 'frozen']);
    $staff = userWithPermissions('rubric.update');
    activeProgramMembership($staff, $rubric->program);

    expect(app(RubricPolicy::class)->update($staff, $rubric))->toBeFalse();
});

test('program staff can validate and screen within their program scope', function () {
    $program = Program::factory()->create();
    $owner = User::factory()->create();
    $application = Application::factory()->create([
        'program_id' => $program->id,
        'primary_owner_id' => $owner->id,
    ]);
    $version = $application->versions()->create([
        'version_number' => 1,
        'status' => 'submitted',
        'content' => ['summary' => 'Qualified application'],
        'created_by' => $owner->id,
        'submitted_at' => now(),
        'submitted_by' => $owner->id,
        'metadata' => ['source' => 'test'],
    ]);
    $application->update(['current_version_id' => $version->id]);
    $validation = ApplicationValidation::factory()->create([
        'program_id' => $program->id,
        'application_id' => $application->id,
        'application_version_id' => $version->id,
    ]);
    $screening = Screening::factory()->create([
        'program_id' => $program->id,
        'application_id' => $application->id,
        'application_version_id' => $version->id,
        'validation_id' => $validation->id,
    ]);
    $staff = userWithPermissions('eligibility.validate', 'eligibility.screen');
    activeProgramMembership($staff, $program);

    expect(app(ApplicationValidationPolicy::class)->view($staff, $validation))->toBeTrue();
    expect(app(ApplicationValidationPolicy::class)->update($staff, $validation))->toBeTrue();
    expect(app(ScreeningPolicy::class)->view($staff, $screening))->toBeTrue();
    expect(app(ScreeningPolicy::class)->update($staff, $screening))->toBeTrue();
});

test('out-of-scope actors cannot validate or screen another program', function () {
    $program = Program::factory()->create();
    $otherProgram = Program::factory()->create();
    $owner = User::factory()->create();
    $application = Application::factory()->create([
        'program_id' => $program->id,
        'primary_owner_id' => $owner->id,
    ]);
    $version = $application->versions()->create([
        'version_number' => 1,
        'status' => 'submitted',
        'content' => ['summary' => 'Qualified application'],
        'created_by' => $owner->id,
        'submitted_at' => now(),
        'submitted_by' => $owner->id,
        'metadata' => ['source' => 'test'],
    ]);
    $application->update(['current_version_id' => $version->id]);
    $validation = ApplicationValidation::factory()->create([
        'program_id' => $program->id,
        'application_id' => $application->id,
        'application_version_id' => $version->id,
    ]);
    $screening = Screening::factory()->create([
        'program_id' => $program->id,
        'application_id' => $application->id,
        'application_version_id' => $version->id,
        'validation_id' => $validation->id,
    ]);
    $staff = userWithPermissions('eligibility.validate', 'eligibility.screen');
    activeProgramMembership($staff, $otherProgram);

    expect(app(ApplicationValidationPolicy::class)->view($staff, $validation))->toBeFalse();
    expect(app(ApplicationValidationPolicy::class)->update($staff, $validation))->toBeFalse();
    expect(app(ScreeningPolicy::class)->view($staff, $screening))->toBeFalse();
    expect(app(ScreeningPolicy::class)->update($staff, $screening))->toBeFalse();
});
