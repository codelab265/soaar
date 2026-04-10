<?php

use App\Enums\PointTransactionType;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\Task;
use App\Models\User;
use App\Services\PointsService;

beforeEach(function () {
    $this->pointsService = new PointsService;
    $this->user = User::factory()->create(['total_points' => 0]);
    $this->goal = Goal::factory()->for($this->user)->create();
    $this->objective = Objective::factory()->for($this->goal)->create();
});

it('awards points to a user and updates total_points', function () {
    $task = Task::factory()->for($this->objective)->create(['points_value' => 10]);

    $transaction = $this->pointsService->awardPoints(
        user: $this->user,
        type: PointTransactionType::TaskCompletion,
        points: 10,
        description: 'Test task',
        transactionable: $task,
    );

    expect($transaction)->not->toBeNull()
        ->and($transaction->points)->toBe(10)
        ->and($this->user->fresh()->total_points)->toBe(10);
});

it('does not award zero or negative points', function () {
    $result = $this->pointsService->awardPoints(
        user: $this->user,
        type: PointTransactionType::TaskCompletion,
        points: 0,
        description: 'Zero points',
    );

    expect($result)->toBeNull()
        ->and($this->user->fresh()->total_points)->toBe(0);
});

it('respects the daily 60-point task cap', function () {
    // Award 55 points first
    $this->pointsService->awardPoints(
        user: $this->user,
        type: PointTransactionType::TaskCompletion,
        points: 55,
        description: 'First batch',
    );

    // Try to award 20 more — should be capped at 5
    $transaction = $this->pointsService->awardPoints(
        user: $this->user,
        type: PointTransactionType::TaskCompletion,
        points: 20,
        description: 'Second batch',
    );

    expect($transaction->points)->toBe(5)
        ->and($this->user->fresh()->total_points)->toBe(60);
});

it('blocks task points entirely when daily cap is already reached', function () {
    $this->pointsService->awardPoints(
        user: $this->user,
        type: PointTransactionType::TaskCompletion,
        points: 60,
        description: 'Fill cap',
    );

    $result = $this->pointsService->awardPoints(
        user: $this->user,
        type: PointTransactionType::TaskCompletion,
        points: 10,
        description: 'Over cap',
    );

    expect($result)->toBeNull()
        ->and($this->user->fresh()->total_points)->toBe(60);
});

it('does not apply daily cap to non-task-completion point types', function () {
    // Fill the task cap
    $this->pointsService->awardPoints(
        user: $this->user,
        type: PointTransactionType::TaskCompletion,
        points: 60,
        description: 'Fill cap',
    );

    // Objective completion should still work (not capped)
    $transaction = $this->pointsService->awardPoints(
        user: $this->user,
        type: PointTransactionType::ObjectiveCompletion,
        points: 40,
        description: 'Objective done',
    );

    expect($transaction->points)->toBe(40)
        ->and($this->user->fresh()->total_points)->toBe(100);
});

it('deducts points from a user', function () {
    $this->user->update(['total_points' => 50]);

    $transaction = $this->pointsService->deductPoints(
        user: $this->user,
        type: PointTransactionType::MissedTask,
        points: 5,
        description: 'Missed a task',
    );

    expect($transaction->points)->toBe(-5)
        ->and($this->user->fresh()->total_points)->toBe(45);
});

it('calculates remaining daily task points correctly', function () {
    $this->pointsService->awardPoints(
        user: $this->user,
        type: PointTransactionType::TaskCompletion,
        points: 35,
        description: 'Some tasks',
    );

    $remaining = $this->pointsService->remainingDailyTaskPoints($this->user);

    expect($remaining)->toBe(25);
});
