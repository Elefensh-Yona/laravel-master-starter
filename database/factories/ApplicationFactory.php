<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
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
            'primary_owner_id' => User::factory(),
            'applicant_type' => 'INDIVIDUAL',
            'status' => 'draft',
            'reference' => fake()->unique()->bothify('APP-####'),
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }
}
