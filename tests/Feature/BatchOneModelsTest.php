<?php

use App\Models\Application;
use App\Models\ApplicationValidation;
use App\Models\Program;
use App\Models\ProgramEligibilityRule;
use App\Models\ProgramMembership;
use App\Models\Rubric;
use App\Models\Screening;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('program persists lifecycle fields and its Batch 1 relationships', function () {
    $program = Program::factory()->create();
    $membership = ProgramMembership::factory()->create(['program_id' => $program->id]);
    $rule = ProgramEligibilityRule::factory()->create(['program_id' => $program->id]);
    $rubric = Rubric::factory()->create(['program_id' => $program->id]);

    expect($program->fresh())
        ->status->toBe('draft')
        ->timezone->toBe('Africa/Addis_Ababa')
        ->metadata->toBe(['source' => 'factory'])
        ->opens_at->toBeInstanceOf(DateTimeInterface::class)
        ->creator->toBeInstanceOf(User::class)
        ->memberships->sole()->id->toBe($membership->id)
        ->eligibilityRules->sole()->id->toBe($rule->id)
        ->rubrics->sole()->id->toBe($rubric->id);
});

test('program code and slug are unique', function () {
    $program = Program::factory()->create();

    expect(fn () => Program::factory()->create([
        'code' => $program->code,
    ]))->toThrow(QueryException::class);

    expect(fn () => Program::factory()->create([
        'slug' => $program->slug,
    ]))->toThrow(QueryException::class);
});

test('program membership preserves its users and lifecycle data', function () {
    $membership = ProgramMembership::factory()->create([
        'stage_scope' => ['screening'],
    ]);

    expect($membership->fresh())
        ->status->toBe('active')
        ->stage_scope->toBe(['screening'])
        ->program->toBeInstanceOf(Program::class)
        ->user->toBeInstanceOf(User::class)
        ->grantedBy->toBeInstanceOf(User::class)
        ->starts_at->toBeInstanceOf(DateTimeInterface::class);
});

test('active program membership is unique per capability', function () {
    $membership = ProgramMembership::factory()->create();

    expect(fn () => DB::transaction(function () use ($membership): void {
        ProgramMembership::factory()->create([
            'program_id' => $membership->program_id,
            'user_id' => $membership->user_id,
            'capability' => $membership->capability,
        ]);
    }))->toThrow(QueryException::class);

    ProgramMembership::factory()->create([
        'program_id' => $membership->program_id,
        'user_id' => $membership->user_id,
        'capability' => $membership->capability,
        'status' => 'ended',
    ]);

    expect(ProgramMembership::query()
        ->where('program_id', $membership->program_id)
        ->where('user_id', $membership->user_id)
        ->count())->toBe(2);
});

test('program eligibility rules persist JSON configuration and are unique per program key and position', function () {
    $rule = ProgramEligibilityRule::factory()->create([
        'configuration' => ['minimum_age' => 18],
    ]);

    expect($rule->fresh())
        ->configuration->toBe(['minimum_age' => 18])
        ->is_required->toBeTrue()
        ->is_enabled->toBeTrue()
        ->program->toBeInstanceOf(Program::class);

    expect(fn () => ProgramEligibilityRule::factory()->create([
        'program_id' => $rule->program_id,
        'key' => $rule->key,
        'position' => 2,
    ]))->toThrow(QueryException::class);

    expect(fn () => ProgramEligibilityRule::factory()->create([
        'program_id' => $rule->program_id,
        'key' => 'another-rule',
        'position' => $rule->position,
    ]))->toThrow(QueryException::class);
});

test('rubrics belong to a program and are unique by program name', function () {
    $rubric = Rubric::factory()->create();

    expect($rubric->fresh())
        ->status->toBe('draft')
        ->metadata->toBe(['source' => 'factory'])
        ->program->toBeInstanceOf(Program::class)
        ->creator->toBeInstanceOf(User::class);

    expect(fn () => Rubric::factory()->create([
        'program_id' => $rubric->program_id,
        'name' => $rubric->name,
    ]))->toThrow(QueryException::class);
});

test('application validation and human screening records persist with the expected program and application relationships', function () {
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

    $screeningUser = User::factory()->create();
    $validation = ApplicationValidation::query()->create([
        'program_id' => $program->id,
        'application_id' => $application->id,
        'application_version_id' => $version->id,
        'status' => 'passed',
        'result' => ['eligibility' => ['passed' => true]],
        'executed_at' => now(),
        'executed_by' => $owner->id,
    ]);
    $screening = Screening::query()->create([
        'program_id' => $program->id,
        'application_id' => $application->id,
        'application_version_id' => $version->id,
        'status' => 'completed',
        'outcome' => 'ELIGIBLE',
        'screened_by' => $screeningUser->id,
        'completed_at' => now(),
        'rationale' => 'Meets the published rule set.',
        'validation_id' => $validation->id,
    ]);

    expect($validation->fresh())
        ->program->toBeInstanceOf(Program::class)
        ->application->toBeInstanceOf(Application::class)
        ->status->toBe('passed')
        ->result->toBe(['eligibility' => ['passed' => true]]);

    expect($screening->fresh())
        ->program->toBeInstanceOf(Program::class)
        ->application->toBeInstanceOf(Application::class)
        ->status->toBe('completed')
        ->outcome->toBe('ELIGIBLE')
        ->screener->toBeInstanceOf(User::class)
        ->validation->toBeInstanceOf(ApplicationValidation::class);
});
