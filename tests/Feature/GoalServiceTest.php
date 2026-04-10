<?php

use App\Enums\GoalStatus;
use App\Enums\PointTransactionType;
use App\Models\Goal;
use App\Models\User;
use App\Services\GoalService;

beforeEach(function () {
    $this->service = app(GoalService::class);
    $this->user = User::factory()->create(['total_points' => 100]);
});

it('can expire an active goal and apply penalty', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->subDays(1),
    ]);

    $result = $this->service->expireGoal($goal);

    expect($result->status)->toBe(GoalStatus::Expired)
        ->and($this->user->fresh()->total_points)->toBe(25);

    $this->assertDatabaseHas('point_transactions', [
        'user_id' => $this->user->id,
        'type' => PointTransactionType::GoalExpired->value,
        'points' => -75,
    ]);
});

it('can cancel an active goal without penalty', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Active,
    ]);

    $result = $this->service->cancelGoal($goal);

    expect($result->status)->toBe(GoalStatus::Cancelled)
        ->and($this->user->fresh()->total_points)->toBe(100);
});

it('can submit a goal for verification', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Active,
    ]);

    $result = $this->service->submitForVerification($goal);

    expect($result->status)->toBe(GoalStatus::PendingVerification);
});

it('can mark a pending verification goal as verified completed', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::PendingVerification,
    ]);

    $result = $this->service->markVerifiedCompleted($goal);

    expect($result->status)->toBe(GoalStatus::VerifiedCompleted);
});

it('rejects invalid transition from cancelled to active', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Cancelled,
    ]);

    $this->service->submitForVerification($goal);
})->throws(InvalidArgumentException::class);

it('rejects invalid transition from expired to verification', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Expired,
    ]);

    $this->service->submitForVerification($goal);
})->throws(InvalidArgumentException::class);

it('rejects invalid transition from verified completed', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::VerifiedCompleted,
    ]);

    $this->service->cancelGoal($goal);
})->throws(InvalidArgumentException::class);

it('checks deletion cooldown correctly', function () {
    $goal = Goal::factory()->for($this->user)->create();

    expect($this->service->canDeleteGoal($goal))->toBeTrue();

    Goal::factory()->for($this->user)->create();
    Goal::where('user_id', $this->user->id)->latest('id')->first()->delete();

    expect($this->service->canDeleteGoal($goal))->toBeFalse();
});
