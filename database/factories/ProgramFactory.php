<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $opensAt = CarbonImmutable::now()->addDay();

        return [
            'name' => fake()->company().' Innovation Program',
            'code' => fake()->unique()->bothify('EAIC-####'),
            'slug' => fake()->unique()->slug(3),
            'status' => 'draft',
            'timezone' => 'Africa/Addis_Ababa',
            'opens_at' => $opensAt,
            'closes_at' => $opensAt->addMonth(),
            'created_by' => User::factory(),
            'description' => fake()->paragraph(),
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }
}
