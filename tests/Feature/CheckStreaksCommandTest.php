<?php

use App\Enums\StreakType;
use App\Models\Streak;
use App\Models\User;

it('breaks streaks for users with no activity yesterday', function () {
    $inactiveUser = User::factory()->create(['total_points' => 100, 'current_streak' => 5]);
    Streak::factory()->create([
        'user_id' => $inactiveUser->id,
        'type' => StreakType::Daily,
        'current_count' => 5,
        'longest_count' => 5,
        'last_activity_date' => now()->subDays(2)->toDateString(),
    ]);

    $activeUser = User::factory()->create(['total_points' => 100, 'current_streak' => 3]);
    Streak::factory()->create([
        'user_id' => $activeUser->id,
        'type' => StreakType::Daily,
        'current_count' => 3,
        'longest_count' => 3,
        'last_activity_date' => now()->subDay()->toDateString(),
    ]);

    $this->artisan('app:check-streaks')
        ->expectsOutputToContain('Broke 1 streak(s)')
        ->assertSuccessful();

    expect($inactiveUser->fresh()->current_streak)->toBe(0)
        ->and($inactiveUser->fresh()->total_points)->toBe(75)
        ->and($activeUser->fresh()->current_streak)->toBe(3);
});

it('does nothing when all streaks are active', function () {
    $user = User::factory()->create(['total_points' => 100]);
    Streak::factory()->create([
        'user_id' => $user->id,
        'type' => StreakType::Daily,
        'current_count' => 5,
        'last_activity_date' => now()->subDay()->toDateString(),
    ]);

    $this->artisan('app:check-streaks')
        ->expectsOutputToContain('Broke 0 streak(s)')
        ->assertSuccessful();
});
