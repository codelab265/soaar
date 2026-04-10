<?php

use App\Enums\GoalStatus;
use App\Enums\PointTransactionType;
use App\Models\Goal;
use App\Models\User;
use App\Services\GoalVerificationService;

beforeEach(function () {
    $this->service = app(GoalVerificationService::class);
    $this->user = User::factory()->create(['total_points' => 0]);
});

it('submits a goal for verification', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::Active,
    ]);

    $result = $this->service->submitForVerification($goal);

    expect($result->status)->toBe(GoalStatus::PendingVerification);
});

it('approves a goal with partner verification and awards full points', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::PendingVerification,
        'deadline' => now()->subDay(),
    ]);

    $result = $this->service->approveGoal($goal);

    expect($result['goal']->status)->toBe(GoalStatus::VerifiedCompleted)
        ->and($result['total_points'])->toBe(120);
});

it('rejects a goal and applies partner rejection penalty', function () {
    $this->user->update(['total_points' => 100]);

    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::PendingVerification,
    ]);

    $result = $this->service->rejectGoal($goal);

    expect($result->status)->toBe(GoalStatus::Active)
        ->and($this->user->fresh()->total_points)->toBe(85);

    $this->assertDatabaseHas('point_transactions', [
        'user_id' => $this->user->id,
        'type' => PointTransactionType::PartnerRejection->value,
        'points' => -15,
    ]);
});

it('auto-approves a goal at 80% reward multiplier', function () {
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::PendingVerification,
        'deadline' => now()->subDay(),
    ]);

    $result = $this->service->autoApproveGoal($goal);

    expect($result['goal']->status)->toBe(GoalStatus::VerifiedCompleted)
        ->and($result['total_points'])->toBe(80);
});

it('auto-approve command processes old pending goals', function () {
    $oldGoal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::PendingVerification,
        'deadline' => now()->subDay(),
        'updated_at' => now()->subHours(49),
    ]);

    $recentGoal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::PendingVerification,
        'deadline' => now()->subDay(),
        'updated_at' => now()->subHour(),
    ]);

    $this->artisan('app:auto-approve-goals')
        ->expectsOutputToContain('Auto-approved 1 goal(s)')
        ->assertSuccessful();

    expect($oldGoal->fresh()->status)->toBe(GoalStatus::VerifiedCompleted)
        ->and($recentGoal->fresh()->status)->toBe(GoalStatus::PendingVerification);
});
