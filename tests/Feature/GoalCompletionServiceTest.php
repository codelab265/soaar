<?php

use App\Enums\GoalStatus;
use App\Enums\TaskStatus;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\Task;
use App\Models\User;
use App\Services\GoalCompletionService;
use App\Services\PointsService;

beforeEach(function () {
    $this->service = app(GoalCompletionService::class);
    $this->user = User::factory()->create(['total_points' => 0]);
});

it('awards base 100 points for goal completion', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->subDay(),
    ]);

    $result = $this->service->completeGoal($goal);

    expect($result['goal']->status)->toBe(GoalStatus::VerifiedCompleted)
        ->and($result['total_points'])->toBe(100);
});

it('awards early completion bonus when completed before deadline', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->addDays(7),
    ]);

    $result = $this->service->completeGoal($goal);

    expect($result['total_points'])->toBe(130);
});

it('awards 100% task completion bonus', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->subDay(),
    ]);

    $objective = Objective::factory()->for($goal)->create();
    Task::factory()->for($objective)->create([
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
    ]);

    $result = $this->service->completeGoal($goal);

    expect($result['total_points'])->toBe(125);
});

it('awards partner verification bonus', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->subDay(),
    ]);

    $result = $this->service->completeGoal($goal, isPartnerVerified: true);

    expect($result['total_points'])->toBe(120);
});

it('caps total goal points at 175', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->addDays(7),
    ]);

    $objective = Objective::factory()->for($goal)->create();
    Task::factory()->for($objective)->create([
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
    ]);

    $result = $this->service->completeGoal($goal, isPartnerVerified: true);

    expect($result['total_points'])->toBe(PointsService::MAX_POINTS_PER_GOAL);
});

it('throws exception for invalid goal status', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Cancelled,
    ]);

    $this->service->completeGoal($goal);
})->throws(InvalidArgumentException::class);
