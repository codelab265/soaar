<?php

use App\Enums\PointTransactionType;
use App\Models\User;
use App\Services\PointsService;

beforeEach(function () {
    $this->service = new PointsService;
    $this->user = User::factory()->create(['total_points' => 0]);
});

it('awards 15 points at 7-day milestone', function () {
    $transaction = $this->service->awardStreakMilestone($this->user, 7);

    expect($transaction)->not->toBeNull()
        ->and($transaction->points)->toBe(15)
        ->and($transaction->type)->toBe(PointTransactionType::StreakBonus)
        ->and($this->user->fresh()->total_points)->toBe(15);
});

it('awards 30 points at 14-day milestone', function () {
    $transaction = $this->service->awardStreakMilestone($this->user, 14);

    expect($transaction->points)->toBe(30);
});

it('awards 75 points at 30-day milestone', function () {
    $transaction = $this->service->awardStreakMilestone($this->user, 30);

    expect($transaction->points)->toBe(75);
});

it('awards 150 points at 60-day milestone', function () {
    $transaction = $this->service->awardStreakMilestone($this->user, 60);

    expect($transaction->points)->toBe(150);
});

it('awards 300 points at 100-day milestone', function () {
    $transaction = $this->service->awardStreakMilestone($this->user, 100);

    expect($transaction->points)->toBe(300);
});

it('returns null for non-milestone streak days', function () {
    $result = $this->service->awardStreakMilestone($this->user, 5);

    expect($result)->toBeNull()
        ->and($this->user->fresh()->total_points)->toBe(0);
});
