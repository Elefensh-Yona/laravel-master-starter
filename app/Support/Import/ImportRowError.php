<?php

namespace App\Support\Import;

/**
 * A single row-level validation failure produced during preview.
 */
class ImportRowError
{
    public function __construct(
        public readonly string $message,
    ) {}
}
