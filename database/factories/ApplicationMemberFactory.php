<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\ApplicationMember;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationMember>
 */
class ApplicationMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $joinedAt = CarbonImmutable::now();

        return [
            'application_id' => Application::factory(),
            'user_id' => User::factory(),
            'status' => 'active',
            'joined_at' => $joinedAt,
            'approved_by' => User::factory(),
        ];
    }
}
