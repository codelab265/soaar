<?php

namespace Database\Factories;

use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Models\Objective;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $difficulty = fake()->randomElement(TaskDifficulty::cases());

        return [
            'objective_id' => Objective::factory(),
            'title' => fake()->sentence(3),
            'difficulty' => $difficulty,
            'minimum_duration' => match ($difficulty) {
                TaskDifficulty::Simple => fake()->numberBetween(5, 30),
                TaskDifficulty::Medium => fake()->numberBetween(30, 90),
                TaskDifficulty::Hard => fake()->numberBetween(90, 180),
            },
            'points_value' => $difficulty->points(),
            'status' => TaskStatus::Pending,
            'repetition_count' => 0,
            'repetition_decay' => 1.00,
            'scheduled_date' => fake()->dateTimeBetween('now', '+1 week'),
        ];
    }
}
