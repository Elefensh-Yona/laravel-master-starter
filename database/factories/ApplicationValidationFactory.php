<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationValidation;
use App\Models\ApplicationVersion;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationValidation>
 */
class ApplicationValidationFactory extends Factory
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
            'status' => 'passed',
            'result' => [
                'eligibility' => ['passed' => true],
            ],
            'executed_at' => now(),
            'executed_by' => User::factory(),
            'failure_reason' => null,
        ];
    }
}
