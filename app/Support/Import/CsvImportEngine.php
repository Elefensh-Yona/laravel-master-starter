<?php

namespace App\Support\Import;

use App\Models\ImportRun;
use App\Models\User;

/**
 * Domain-neutral CSV import engine.
 *
 * Two-phase design: `preview` validates a CSV against a row handler without
 * persisting anything; `commit` executes the stored persister for every valid
 * row and records the run in `import_runs`.
 */
class CsvImportEngine
{
    /**
     * @param  resource|string  $csv  An open stream or raw CSV contents.
     * @param  string  $resource  Logical name recorded on the import run (e.g. 'users').
     * @param  callable(array<string, scalar|null>, int): ImportRowError|null  $rowHandler  Receives the row keyed by header and the 1-based data row number. Return an ImportRowError for a soft validation failure; throw to surface a hard failure.
     * @param  list<string>|null  $expectedHeader  When provided, the CSV header must match exactly (order included).
     * @param  int  $previewLimit  Maximum number of preview rows kept on the import run.
     */
    public function preview(
        $csv,
        string $resource,
        callable $rowHandler,
        ?array $expectedHeader = null,
        int $previewLimit = 25,
        string $fileName = 'upload.csv',
    ): ImportPreview {
        $stream = $this->toStream($csv);

        $header = fgetcsv($stream);

        if ($header === false || $header === [null] || trim(implode('', array_map('strval', (array) $header))) === '') {
            return ImportPreview::empty($resource, $expectedHeader ?? []);
        }

        $header = array_map(fn ($column): string => trim((string) $column), $header);

        if ($expectedHeader !== null && $header !== $expectedHeader) {
            return ImportPreview::headerMismatch($resource, $expectedHeader, $header);
        }

        $validRows = [];
        $errors = [];
        $rowsCount = 0;
        $rowNumber = 1;

        while (($rawRow = fgetcsv($stream)) !== false) {
            $rowNumber++;

            if ($rawRow === [null] || implode('', array_map('strval', $rawRow)) === '') {
                continue;
            }

            $rowsCount++;

            $row = $this->associate($header, $rawRow);

            try {
                $result = $rowHandler($row, $rowNumber);

                if ($result instanceof ImportRowError) {
                    $errors[] = ['row' => $rowNumber, 'message' => $result->message];

                    continue;
                }

                $validRows[] = ['row' => $rowNumber, 'data' => $row];
            } catch (\Throwable $exception) {
                $errors[] = ['row' => $rowNumber, 'message' => $exception->getMessage()];
            }
        }

        fclose($stream);

        return new ImportPreview(
            resource: $resource,
            header: $header,
            validRows: $validRows,
            errors: $errors,
            rowsCount: $rowsCount,
            fileName: $fileName,
            previewLimit: $previewLimit,
        );
    }

    /**
     * Execute a validated preview and record the outcome as an ImportRun.
     *
     * @param  callable(array<string, scalar|null>, int): mixed  $persister  Persist one validated row. Throw to abort the whole run.
     */
    public function commit(ImportPreview $preview, ?User $user, callable $persister): ImportRun
    {
        if (! $preview->isCommittable()) {
            throw new \LogicException('Only previews with at least one valid row can be committed.');
        }

        $importedRowsCount = 0;

        foreach ($preview->validEntries() as $entry) {
            $persister($entry['data'], (int) $entry['row']);
            $importedRowsCount++;
        }

        return ImportRun::query()->create([
            'user_id' => $user?->id,
            'resource' => $preview->resource,
            'status' => empty($preview->errors) ? 'completed' : 'completed_with_errors',
            'file_name' => $preview->fileName,
            'rows_count' => $preview->rowsCount,
            'valid_rows_count' => count($preview->validEntries()),
            'imported_rows_count' => $importedRowsCount,
            'summary' => [
                'errors' => $preview->errorEntries(),
                'header' => $preview->header,
            ],
            'preview_rows' => $preview->previewEntries(),
            'completed_at' => now(),
        ]);
    }

    /**
     * @param  resource  $csv
     * @return resource
     */
    private function toStream($csv)
    {
        if (is_resource($csv)) {
            rewind($csv);

            return $csv;
        }

        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new \RuntimeException('Unable to open a temporary stream for the CSV payload.');
        }

        fwrite($stream, (string) $csv);
        rewind($stream);

        return $stream;
    }

    /**
     * Combine the header with a raw row, padding/truncating as needed.
     *
     * @param  list<string>  $header
     * @param  list<string|null>  $rawRow
     * @return array<string, scalar|null>
     */
    private function associate(array $header, array $rawRow): array
    {
        $row = [];

        foreach ($header as $index => $key) {
            $row[$key] = isset($rawRow[$index]) ? trim((string) $rawRow[$index]) : null;
        }

        return $row;
    }
}
