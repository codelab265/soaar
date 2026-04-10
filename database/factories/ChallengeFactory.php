<?php

namespace Database\Factories;

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $duration = fake()->randomElement([30, 100]);

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'duration_days' => $duration,
            'reward_points' => $duration === 30 ? 200 : 500,
            'status' => ChallengeStatus::Active,
            'start_date' => now(),
            'end_date' => now()->addDays($duration),
        ];
    }
}
