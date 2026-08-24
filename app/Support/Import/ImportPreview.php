<?php

namespace App\Support\Import;

/**
 * Immutable result of a CSV preview pass.
 */
class ImportPreview
{
    /**
     * @param  string  $resource  Logical name recorded on the import run.
     * @param  list<string>  $header
     * @param  list<array{row: int, data: array<string, scalar|null>}>  $validRows
     * @param  list<array{row: int, message: string}>  $errors
     */
    public function __construct(
        public readonly string $resource,
        public readonly array $header,
        public array $validRows,
        public readonly array $errors,
        public readonly int $rowsCount,
        public string $fileName = 'upload.csv',
        public readonly ?array $headerMismatch = null,
        private readonly int $previewLimit = 25,
    ) {}

    /**
     * A preview representing a file with no usable header row.
     */
    public static function empty(string $resource, array $expectedHeader): self
    {
        return new self(
            resource: $resource,
            header: [],
            validRows: [],
            errors: [['row' => 1, 'message' => 'The file contains no header row.']],
            rowsCount: 0,
            headerMismatch: ['expected' => $expectedHeader, 'found' => []],
        );
    }

    /**
     * A preview representing a header that does not match expectations.
     *
     * @param  list<string>  $expectedHeader
     * @param  list<string>  $foundHeader
     */
    public static function headerMismatch(string $resource, array $expectedHeader, array $foundHeader): self
    {
        return new self(
            resource: $resource,
            header: $foundHeader,
            validRows: [],
            errors: [
                [
                    'row' => 1,
                    'message' => 'The file header does not match the expected columns.',
                ],
            ],
            rowsCount: 0,
            headerMismatch: ['expected' => $expectedHeader, 'found' => $foundHeader],
        );
    }

    /**
     * Whether this preview can be committed.
     */
    public function isCommittable(): bool
    {
        return $this->headerMismatch === null && $this->validRows !== [];
    }

    /**
     * @return list<array{row: int, data: array<string, scalar|null>}>
     */
    public function validEntries(): array
    {
        return array_values($this->validRows);
    }

    /**
     * @return list<array{row: int, data: array<string, scalar|null>}>
     */
    public function previewEntries(): array
    {
        return array_slice($this->validEntries(), 0, $this->previewLimit);
    }

    /**
     * @return array<int, array{row: int, message: string}>
     */
    public function errorEntries(): array
    {
        return array_values($this->errors);
    }
}
