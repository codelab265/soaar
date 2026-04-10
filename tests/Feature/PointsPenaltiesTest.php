<?php

use App\Enums\PointTransactionType;
use App\Models\User;
use App\Services\PointsService;

beforeEach(function () {
    $this->service = new PointsService;
    $this->user = User::factory()->create(['total_points' => 200]);
});

it('applies goal expired penalty of -75', function () {
    $transaction = $this->service->applyGoalExpiredPenalty($this->user);

    expect($transaction->points)->toBe(-75)
        ->and($transaction->type)->toBe(PointTransactionType::GoalExpired)
        ->and($this->user->fresh()->total_points)->toBe(125);
});

it('applies missed deadline penalty of -50', function () {
    $transaction = $this->service->applyMissedDeadlinePenalty($this->user);

    expect($transaction->points)->toBe(-50)
        ->and($transaction->type)->toBe(PointTransactionType::MissedDeadline)
        ->and($this->user->fresh()->total_points)->toBe(150);
});

it('applies partner rejection penalty of -15', function () {
    $transaction = $this->service->applyPartnerRejectionPenalty($this->user);

    expect($transaction->points)->toBe(-15)
        ->and($transaction->type)->toBe(PointTransactionType::PartnerRejection)
        ->and($this->user->fresh()->total_points)->toBe(185);
});

it('applies streak broken penalty of -25', function () {
    $transaction = $this->service->applyStreakBrokenPenalty($this->user);

    expect($transaction->points)->toBe(-25)
        ->and($transaction->type)->toBe(PointTransactionType::StreakBroken)
        ->and($this->user->fresh()->total_points)->toBe(175);
});
