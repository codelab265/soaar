<?php

namespace Database\Factories;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'why' => fake()->sentence(8),
            'category' => fake()->randomElement(['health', 'career', 'finance', 'education', 'personal']),
            'deadline' => fake()->dateTimeBetween('+1 week', '+6 months'),
            'status' => GoalStatus::Active,
        ];
    }

    /**
     * Set the goal as expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GoalStatus::Expired,
            'deadline' => fake()->dateTimeBetween('-2 months', '-1 day'),
        ]);
    }

    /**
     * Set the goal as pending verification.
     */
    public function pendingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GoalStatus::PendingVerification,
        ]);
    }
}
