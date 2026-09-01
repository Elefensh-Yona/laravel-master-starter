<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\ProgramMembership;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramMembership>
 */
class ProgramMembershipFactory extends Factory
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
            'user_id' => User::factory(),
            'capability' => 'program_staff',
            'status' => 'active',
            'starts_at' => CarbonImmutable::now(),
            'granted_by' => User::factory(),
            'stage_scope' => null,
            'metadata' => [
                'source' => 'factory',
            ],
        ];
    }
}
