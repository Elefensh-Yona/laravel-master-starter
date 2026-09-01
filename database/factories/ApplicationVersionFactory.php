<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationVersion>
 */
class ApplicationVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'version_number' => 1,
            'status' => 'draft',
            'content' => [
                'summary' => fake()->sentence(),
                'category' => 'innovation',
            ],
            'created_by' => User::factory(),
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }
}
