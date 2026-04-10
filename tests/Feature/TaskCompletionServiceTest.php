<?php

use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskCompletionService;

beforeEach(function () {
    $this->service = app(TaskCompletionService::class);
    $this->user = User::factory()->create(['total_points' => 0]);
    $this->goal = Goal::factory()->for($this->user)->create();
    $this->objective = Objective::factory()->for($this->goal)->create();
});

it('completes a task and awards correct points', function () {
    $task = Task::factory()->for($this->objective)->create([
        'difficulty' => TaskDifficulty::Medium,
        'points_value' => 10,
        'repetition_count' => 0,
        'repetition_decay' => 1.00,
    ]);

    $result = $this->service->completeTask($task, durationMinutes: 30);

    expect($result['points_awarded'])->toBe(10)
        ->and($result['task']->status)->toBe(TaskStatus::Completed)
        ->and($result['task']->completed_at)->not->toBeNull()
        ->and($this->user->fresh()->total_points)->toBe(10);
});

it('awards no points for tasks completed under 5 minutes', function () {
    $task = Task::factory()->for($this->objective)->create([
        'difficulty' => TaskDifficulty::Simple,
        'points_value' => 5,
    ]);

    $result = $this->service->completeTask($task, durationMinutes: 3);

    expect($result['points_awarded'])->toBe(0)
        ->and($result['task']->status)->toBe(TaskStatus::Completed)
        ->and($this->user->fresh()->total_points)->toBe(0);
});

it('throws an exception when completing an already completed task', function () {
    $task = Task::factory()->for($this->objective)->create([
        'status' => TaskStatus::Completed,
    ]);

    $this->service->completeTask($task, durationMinutes: 30);
})->throws(InvalidArgumentException::class, 'Task is already completed.');

it('applies repetition decay after 10 repetitions', function () {
    $task = Task::factory()->for($this->objective)->create([
        'difficulty' => TaskDifficulty::Hard,
        'points_value' => 20,
        'repetition_count' => 10,
        'repetition_decay' => 1.00,
    ]);

    // This is the 11th completion — should trigger decay
    $result = $this->service->completeTask($task, durationMinutes: 120);

    $freshTask = $task->fresh();
    expect($freshTask->repetition_count)->toBe(11)
        ->and((float) $freshTask->repetition_decay)->toBe(0.95)
        ->and($result['points_awarded'])->toBe(19); // 20 * 0.95 = 19
});

it('does not decay before the threshold', function () {
    $task = Task::factory()->for($this->objective)->create([
        'difficulty' => TaskDifficulty::Simple,
        'points_value' => 5,
        'repetition_count' => 5,
        'repetition_decay' => 1.00,
    ]);

    $result = $this->service->completeTask($task, durationMinutes: 15);

    $freshTask = $task->fresh();
    expect($freshTask->repetition_count)->toBe(6)
        ->and((float) $freshTask->repetition_decay)->toBe(1.00)
        ->and($result['points_awarded'])->toBe(5);
});

it('enforces minimum decay floor of 0.10', function () {
    $decay = $this->service->calculateDecay(30); // 20 excess → 1.00 - 1.00 = 0.00 → clamped to 0.10

    expect($decay)->toBe(0.10);
});

it('marks a task as missed and deducts points', function () {
    $this->user->update(['total_points' => 50]);

    $task = Task::factory()->for($this->objective)->create([
        'status' => TaskStatus::Pending,
    ]);

    $transaction = $this->service->missTask($task);

    expect($task->fresh()->status)->toBe(TaskStatus::Missed)
        ->and($transaction->points)->toBe(-5)
        ->and($this->user->fresh()->total_points)->toBe(45);
});

it('respects daily cap when completing multiple tasks', function () {
    // Complete tasks worth 55 points total
    for ($i = 0; $i < 11; $i++) {
        $task = Task::factory()->for($this->objective)->create([
            'difficulty' => TaskDifficulty::Simple,
            'points_value' => 5,
            'repetition_count' => 0,
            'repetition_decay' => 1.00,
        ]);
        $this->service->completeTask($task, durationMinutes: 10);
    }

    expect($this->user->fresh()->total_points)->toBe(55);

    // 12th task worth 20 — should be capped at 5
    $hardTask = Task::factory()->for($this->objective)->create([
        'difficulty' => TaskDifficulty::Hard,
        'points_value' => 20,
        'repetition_count' => 0,
        'repetition_decay' => 1.00,
    ]);

    $result = $this->service->completeTask($hardTask, durationMinutes: 120);

    expect($result['points_awarded'])->toBe(5)
        ->and($this->user->fresh()->total_points)->toBe(60);
});
