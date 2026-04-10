<?php

use App\Enums\ObjectiveStatus;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\User;
use App\Services\ObjectiveCompletionService;

beforeEach(function () {
    $this->service = app(ObjectiveCompletionService::class);
    $this->user = User::factory()->create(['total_points' => 0]);
    $this->goal = Goal::factory()->for($this->user)->create();
});

it('completes an objective and awards 40 base points', function () {
    $objective = Objective::factory()->for($this->goal)->create([
        'status' => ObjectiveStatus::Pending,
    ]);

    $result = $this->service->completeObjective($objective);

    expect($result['objective']->status)->toBe(ObjectiveStatus::Completed)
        ->and($result['points_awarded'])->toBe(40)
        ->and($this->user->fresh()->total_points)->toBe(40);
});

it('verifies a completed objective and awards 10 bonus points', function () {
    $objective = Objective::factory()->for($this->goal)->create([
        'status' => ObjectiveStatus::Completed,
    ]);

    $result = $this->service->verifyObjective($objective);

    expect($result['objective']->status)->toBe(ObjectiveStatus::Verified)
        ->and($result['points_awarded'])->toBe(10)
        ->and($this->user->fresh()->total_points)->toBe(10);
});

it('throws exception when completing an already completed objective', function () {
    $objective = Objective::factory()->for($this->goal)->create([
        'status' => ObjectiveStatus::Completed,
    ]);

    $this->service->completeObjective($objective);
})->throws(InvalidArgumentException::class, 'Objective is already completed or verified.');

it('throws exception when verifying a non-completed objective', function () {
    $objective = Objective::factory()->for($this->goal)->create([
        'status' => ObjectiveStatus::Pending,
    ]);

    $this->service->verifyObjective($objective);
})->throws(InvalidArgumentException::class, 'Only completed objectives can be verified.');
