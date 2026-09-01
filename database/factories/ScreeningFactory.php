<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationValidation;
use App\Models\ApplicationVersion;
use App\Models\Program;
use App\Models\Screening;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Screening>
 */
class ScreeningFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'application_id' => Application::factory(),
            'application_version_id' => ApplicationVersion::factory(),
            'validation_id' => ApplicationValidation::factory(),
            'status' => 'completed',
            'outcome' => 'ELIGIBLE',
            'screened_by' => User::factory(),
            'completed_at' => now(),
            'rationale' => fake()->sentence(),
            'reopened_at' => null,
            'reopened_by' => null,
            'reopen_reason' => null,
        ];
    }

    public function inReview(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'in_review',
            'outcome' => null,
            'completed_at' => null,
            'rationale' => null,
        ]);
    }
}
