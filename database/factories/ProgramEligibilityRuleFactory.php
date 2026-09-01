<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\ProgramEligibilityRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramEligibilityRule>
 */
class ProgramEligibilityRuleFactory extends Factory
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
            'key' => fake()->unique()->slug(2, '_'),
            'label' => fake()->sentence(3),
            'rule_type' => 'boolean',
            'configuration' => [
                'expected' => true,
            ],
            'position' => 1,
            'is_required' => true,
            'is_enabled' => true,
            'description' => fake()->sentence(),
        ];
    }
}
