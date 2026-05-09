<?php

namespace Database\Factories;

use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Models\Goal;
use App\Models\Task;
use App\Models\User;
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
            'user_id' => User::factory(),
            'goal_id' => null,
            'objective_id' => null,
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

    public function forGoal(?Goal $goal = null): static
    {
        return $this->state(function (array $attributes) use ($goal): array {
            $goalModel = $goal ?? Goal::factory()->create([
                'user_id' => $attributes['user_id'] ?? User::factory()->create()->id,
            ]);

            return [
                'user_id' => $goalModel->user_id,
                'goal_id' => $goalModel->id,
            ];
        });
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Task $task): void {
            if (! $task->objective_id) {
                return;
            }

            $task->goal_id = $task->objective?->goal_id;
            $task->user_id = $task->objective?->goal?->user_id ?? $task->user_id;
        })->afterCreating(function (Task $task): void {
            if (! $task->objective_id) {
                return;
            }

            $task->updateQuietly([
                'goal_id' => $task->objective?->goal_id,
                'user_id' => $task->objective?->goal?->user_id ?? $task->user_id,
            ]);
        });
    }
}
