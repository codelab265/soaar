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

it('partner can request proof on a pending goal', function () {
    $partner = User::factory()->create();
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::PendingVerification,
        'accountability_partner_id' => $partner->id,
    ]);

    $result = $this->service->requestProof($goal, 'Share screenshots of the completed work.');

    expect($result->proof_request_message)->toBe('Share screenshots of the completed work.')
        ->and($result->proof_requested_at)->not->toBeNull();
});

it('goal owner can submit proof after it is requested', function () {
    $partner = User::factory()->create();
    $goal = Goal::factory()->for($this->user)->create([
        'status' => GoalStatus::PendingVerification,
        'accountability_partner_id' => $partner->id,
        'proof_requested_at' => now(),
        'proof_request_message' => 'Need proof.',
    ]);

    $result = $this->service->submitProof($goal, 'Here is the proof payload.');

    expect($result->proof_submission)->toBe('Here is the proof payload.')
        ->and($result->proof_submitted_at)->not->toBeNull();
});
