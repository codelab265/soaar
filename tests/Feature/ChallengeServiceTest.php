<?php

use App\Enums\ChallengeStatus;
use App\Enums\PointTransactionType;
use App\Models\Challenge;
use App\Models\User;
use App\Services\ChallengeService;

beforeEach(function () {
    $this->service = app(ChallengeService::class);
    $this->user = User::factory()->create(['total_points' => 0]);
    $this->challenge = Challenge::factory()->create([
        'duration_days' => 30,
        'reward_points' => 200,
    ]);
});

it('allows a user to join an active challenge', function () {
    $pivot = $this->service->joinChallenge($this->user, $this->challenge);

    expect($pivot->user_id)->toBe($this->user->id)
        ->and($pivot->challenge_id)->toBe($this->challenge->id)
        ->and($pivot->status)->toBe(ChallengeStatus::Active);
});

it('prevents joining an inactive challenge', function () {
    $cancelled = Challenge::factory()->create(['status' => ChallengeStatus::Cancelled]);

    $this->service->joinChallenge($this->user, $cancelled);
})->throws(InvalidArgumentException::class, 'Only active challenges can be joined.');

it('prevents double-joining a challenge', function () {
    $this->service->joinChallenge($this->user, $this->challenge);

    $this->service->joinChallenge($this->user, $this->challenge);
})->throws(InvalidArgumentException::class, 'User has already joined this challenge.');

it('completes a challenge and awards reward points', function () {
    $this->service->joinChallenge($this->user, $this->challenge);

    $pivot = $this->service->completeChallenge($this->user, $this->challenge);

    expect($pivot->status)->toBe(ChallengeStatus::Completed)
        ->and($pivot->completed_at)->not->toBeNull()
        ->and($this->user->fresh()->total_points)->toBe(200);

    $this->assertDatabaseHas('point_transactions', [
        'user_id' => $this->user->id,
        'type' => PointTransactionType::ChallengeReward->value,
        'points' => 200,
    ]);
});

it('prevents completing an already completed challenge', function () {
    $this->service->joinChallenge($this->user, $this->challenge);
    $this->service->completeChallenge($this->user, $this->challenge);

    $this->service->completeChallenge($this->user, $this->challenge);
})->throws(InvalidArgumentException::class, 'User has already completed this challenge.');

it('checks progress for a joined user', function () {
    $this->service->joinChallenge($this->user, $this->challenge);

    $progress = $this->service->checkProgress($this->user, $this->challenge);

    expect($progress['joined'])->toBeTrue()
        ->and($progress['status'])->toBe('active')
        ->and($progress['days_remaining'])->toBe(30);
});

it('checks progress for a non-joined user', function () {
    $progress = $this->service->checkProgress($this->user, $this->challenge);

    expect($progress['joined'])->toBeFalse()
        ->and($progress['status'])->toBeNull();
});
