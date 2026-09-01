<?php

use App\Models\Application;
use App\Models\ApplicationMember;
use App\Models\ApplicationVersion;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('application aggregates the current program, owner, and version relationships', function () {
    $application = Application::factory()->create([
        'metadata' => ['source' => 'factory'],
    ]);
    $version = ApplicationVersion::factory()->create([
        'application_id' => $application->id,
        'version_number' => 1,
    ]);
    $member = ApplicationMember::factory()->create([
        'application_id' => $application->id,
    ]);

    $application->refresh();
    $application->current_version_id = $version->id;
    $application->save();

    expect($application->fresh())
        ->program->toBeInstanceOf(Program::class)
        ->primaryOwner->toBeInstanceOf(User::class)
        ->currentVersion->id->toBe($version->id)
        ->metadata->toBe(['source' => 'factory'])
        ->status->toBe('draft')
        ->members->sole()->id->toBe($member->id)
        ->versions->contains(fn ($record) => $record->id === $version->id)->toBeTrue();
});

test('application members enforce a single active membership per user', function () {
    $application = Application::factory()->create();
    $user = User::factory()->create();

    ApplicationMember::factory()->create([
        'application_id' => $application->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]);

    $ended = ApplicationMember::factory()->create([
        'application_id' => $application->id,
        'user_id' => User::factory()->create()->id,
        'status' => 'ended',
    ]);

    expect($ended->status)->toBe('ended');

    expect(fn () => ApplicationMember::factory()->create([
        'application_id' => $application->id,
        'user_id' => $user->id,
        'status' => 'active',
    ]))->toThrow(QueryException::class);
});

test('application versions require unique version numbers within an application', function () {
    $application = Application::factory()->create();

    ApplicationVersion::factory()->create([
        'application_id' => $application->id,
        'version_number' => 1,
    ]);

    expect(fn () => ApplicationVersion::factory()->create([
        'application_id' => $application->id,
        'version_number' => 1,
    ]))->toThrow(QueryException::class);
});
