<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\Rubric;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rubric>
 */
class RubricFactory extends Factory
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
            'name' => fake()->unique()->sentence(3),
            'status' => 'draft',
            'created_by' => User::factory(),
            'description' => fake()->paragraph(),
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }
}
