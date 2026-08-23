<?php

namespace Database\Factories;

use App\Models\ImportRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportRun>
 */
class ImportRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'resource' => 'records',
            'status' => 'previewed',
            'file_name' => 'records.csv',
            'rows_count' => 2,
            'valid_rows_count' => 2,
            'imported_rows_count' => 0,
            'summary' => [
                'errors' => 0,
                'warnings' => 0,
            ],
            'preview_rows' => [],
            'completed_at' => null,
        ];
    }
}
