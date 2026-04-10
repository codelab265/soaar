<?php

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;

it('expires active goals past their deadline', function () {
    $user = User::factory()->create(['total_points' => 200]);

    $expiredGoal = Goal::factory()->for($user)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->subDays(2),
    ]);

    $activeGoal = Goal::factory()->for($user)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->addDays(7),
    ]);

    $this->artisan('app:expire-goals')
        ->expectsOutputToContain('Expired 1 goal(s)')
        ->assertSuccessful();

    expect($expiredGoal->fresh()->status)->toBe(GoalStatus::Expired)
        ->and($activeGoal->fresh()->status)->toBe(GoalStatus::Active)
        ->and($user->fresh()->total_points)->toBe(125);
});

it('does nothing when no goals are past deadline', function () {
    $user = User::factory()->create();

    Goal::factory()->for($user)->create([
        'status' => GoalStatus::Active,
        'deadline' => now()->addDays(30),
    ]);

    $this->artisan('app:expire-goals')
        ->expectsOutputToContain('Expired 0 goal(s)')
        ->assertSuccessful();
});
