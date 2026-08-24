<?php

use App\Models\ImportRun;
use App\Models\User;
use App\Support\Import\CsvImportEngine;
use App\Support\Import\ImportRowError;

function csvPayload(string $body): string
{
    return $body;
}

test('preview validates rows and reports per-row errors without persisting', function () {
    $engine = new CsvImportEngine;

    $csv = csvPayload(<<<'CSV'
        name,email
        Ada,ada@example.com
        ,broken@example.com
        Grace,grace@example.com
        CSV);

    $preview = $engine->preview(
        $csv,
        'users',
        function (array $row, int $rowNumber): ?ImportRowError {
            if (($row['name'] ?? null) === null || $row['name'] === '') {
                return new ImportRowError('The name field is required.');
            }

            return null;
        },
        expectedHeader: ['name', 'email'],
        fileName: 'users.csv',
    );

    expect($preview->rowsCount)->toBe(3)
        ->and(count($preview->validEntries()))->toBe(2)
        ->and($preview->validEntries()[0]['data']['email'])->toBe('ada@example.com')
        ->and($preview->errorEntries()[0])->toBe(['row' => 3, 'message' => 'The name field is required.'])
        ->and($preview->isCommittable())->toBeTrue()
        ->and($preview->fileName)->toBe('users.csv')
        ->and(ImportRun::query()->count())->toBe(0);
});

test('preview rejects files whose header does not match expectations', function () {
    $engine = new CsvImportEngine;

    $csv = csvPayload("fullname,email\nAda,ada@example.com\n");

    $preview = $engine->preview(
        $csv,
        'users',
        fn (): ?ImportRowError => null,
        expectedHeader: ['name', 'email'],
    );

    expect($preview->isCommittable())->toBeFalse()
        ->and($preview->headerMismatch)->toBe([
            'expected' => ['name', 'email'],
            'found' => ['fullname', 'email'],
        ]);
});

test('preview reports an empty file', function () {
    $engine = new CsvImportEngine;

    $preview = $engine->preview('', 'users', fn (): ?ImportRowError => null, expectedHeader: ['name']);

    expect($preview->isCommittable())->toBeFalse()
        ->and($preview->rowsCount)->toBe(0);
});

test('commit persists every valid row and records a completed import run', function () {
    $engine = new CsvImportEngine;
    $actor = User::factory()->create();

    $csv = csvPayload("name,email\nAda,ada@example.com\nGrace,grace@example.com\n");

    $preview = $engine->preview(
        $csv,
        'users',
        fn (): ?ImportRowError => null,
        expectedHeader: ['name', 'email'],
        fileName: 'team.csv',
    );

    $persisted = [];

    $run = $engine->commit($preview, $actor, function (array $row) use (&$persisted): void {
        $persisted[] = $row['email'];
    });

    expect($persisted)->toBe(['ada@example.com', 'grace@example.com'])
        ->and($run->status)->toBe('completed')
        ->and($run->resource)->toBe('users')
        ->and($run->file_name)->toBe('team.csv')
        ->and($run->rows_count)->toBe(2)
        ->and($run->valid_rows_count)->toBe(2)
        ->and($run->imported_rows_count)->toBe(2)
        ->and($run->user_id)->toBe($actor->id)
        ->and($run->completed_at)->not->toBeNull();
});

test('commit marks runs with soft row errors as completed_with_errors', function () {
    $engine = new CsvImportEngine;
    $actor = User::factory()->create();

    $csv = csvPayload("name,email\nAda,ada@example.com\n,broken@example.com\n");

    $preview = $engine->preview(
        $csv,
        'users',
        function (array $row): ?ImportRowError {
            return $row['name'] === '' ? new ImportRowError('Name required.') : null;
        },
        expectedHeader: ['name', 'email'],
        fileName: 'team.csv',
    );

    $run = $engine->commit($preview, $actor, fn (): null => null);

    expect($run->status)->toBe('completed_with_errors')
        ->and($run->rows_count)->toBe(2)
        ->and($run->valid_rows_count)->toBe(1)
        ->and($run->imported_rows_count)->toBe(1)
        ->and($run->summary['errors'][0]['message'])->toBe('Name required.');
});

test('commit refuses previews that cannot be committed', function () {
    $engine = new CsvImportEngine;

    $emptyPreview = $engine->preview('', 'users', fn (): ?ImportRowError => null);

    $engine->commit($emptyPreview, null, fn (): null => null);
})->throws(LogicException::class);
