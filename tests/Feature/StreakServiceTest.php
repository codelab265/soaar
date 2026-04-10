<?php

use App\Enums\PointTransactionType;
use App\Enums\StreakType;
use App\Models\Streak;
use App\Models\User;
use App\Services\StreakService;

beforeEach(function () {
    $this->service = app(StreakService::class);
    $this->user = User::factory()->create(['total_points' => 100, 'current_streak' => 0, 'longest_streak' => 0]);
});

it('creates a streak on first activity', function () {
    $streak = $this->service->recordActivity($this->user);

    expect($streak->current_count)->toBe(1)
        ->and($streak->longest_count)->toBe(1)
        ->and($streak->last_activity_date->toDateString())->toBe(now()->toDateString())
        ->and($this->user->fresh()->current_streak)->toBe(1)
        ->and($this->user->fresh()->longest_streak)->toBe(1);
});

it('increments streak on consecutive days', function () {
    Streak::factory()->create([
        'user_id' => $this->user->id,
        'type' => StreakType::Daily,
        'current_count' => 5,
        'longest_count' => 10,
        'last_activity_date' => now()->subDay()->toDateString(),
        'started_at' => now()->subDays(5)->toDateString(),
    ]);

    $streak = $this->service->recordActivity($this->user);

    expect($streak->current_count)->toBe(6)
        ->and($streak->longest_count)->toBe(10)
        ->and($this->user->fresh()->current_streak)->toBe(6);
});

it('resets streak when days are skipped', function () {
    Streak::factory()->create([
        'user_id' => $this->user->id,
        'type' => StreakType::Daily,
        'current_count' => 5,
        'longest_count' => 10,
        'last_activity_date' => now()->subDays(3)->toDateString(),
    ]);

    $streak = $this->service->recordActivity($this->user);

    expect($streak->current_count)->toBe(1)
        ->and($streak->longest_count)->toBe(10);
});

it('updates longest count when current exceeds it', function () {
    Streak::factory()->create([
        'user_id' => $this->user->id,
        'type' => StreakType::Daily,
        'current_count' => 9,
        'longest_count' => 9,
        'last_activity_date' => now()->subDay()->toDateString(),
    ]);

    $streak = $this->service->recordActivity($this->user);

    expect($streak->current_count)->toBe(10)
        ->and($streak->longest_count)->toBe(10)
        ->and($this->user->fresh()->longest_streak)->toBe(10);
});

it('does not double-count same-day activity', function () {
    Streak::factory()->create([
        'user_id' => $this->user->id,
        'type' => StreakType::Daily,
        'current_count' => 3,
        'longest_count' => 5,
        'last_activity_date' => now()->toDateString(),
    ]);

    $streak = $this->service->recordActivity($this->user);

    expect($streak->current_count)->toBe(3);
});

it('awards milestone bonus at 7 days', function () {
    Streak::factory()->create([
        'user_id' => $this->user->id,
        'type' => StreakType::Daily,
        'current_count' => 6,
        'longest_count' => 6,
        'last_activity_date' => now()->subDay()->toDateString(),
    ]);

    $this->service->recordActivity($this->user);

    $this->assertDatabaseHas('point_transactions', [
        'user_id' => $this->user->id,
        'type' => PointTransactionType::StreakBonus->value,
        'points' => 15,
    ]);
});

it('breaks a streak and applies penalty', function () {
    Streak::factory()->create([
        'user_id' => $this->user->id,
        'type' => StreakType::Daily,
        'current_count' => 10,
        'longest_count' => 10,
        'last_activity_date' => now()->subDays(2)->toDateString(),
    ]);

    $streak = $this->service->breakStreak($this->user);

    expect($streak->current_count)->toBe(0)
        ->and($streak->longest_count)->toBe(10)
        ->and($this->user->fresh()->current_streak)->toBe(0)
        ->and($this->user->fresh()->total_points)->toBe(75);

    $this->assertDatabaseHas('point_transactions', [
        'user_id' => $this->user->id,
        'type' => PointTransactionType::StreakBroken->value,
        'points' => -25,
    ]);
});

it('does not apply penalty when streak is already zero', function () {
    Streak::factory()->create([
        'user_id' => $this->user->id,
        'type' => StreakType::Daily,
        'current_count' => 0,
        'longest_count' => 5,
    ]);

    $this->service->breakStreak($this->user);

    expect($this->user->fresh()->total_points)->toBe(100);
});
