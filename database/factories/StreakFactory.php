<?php

namespace Database\Factories;

use App\Enums\StreakType;
use App\Models\Streak;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Streak>
 */
class StreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => StreakType::Daily,
            'current_count' => fake()->numberBetween(0, 30),
            'longest_count' => fake()->numberBetween(0, 100),
            'last_activity_date' => now(),
            'started_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
