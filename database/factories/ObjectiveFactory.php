<?php

namespace Database\Factories;

use App\Enums\ObjectiveStatus;
use App\Models\Goal;
use App\Models\Objective;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Objective>
 */
class ObjectiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'title' => fake()->sentence(3),
            'status' => ObjectiveStatus::Pending,
            'priority' => fake()->numberBetween(0, 10),
        ];
    }
}
