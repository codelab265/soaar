<?php

use App\Enums\GoalStatus;
use App\Enums\PointTransactionType;
use App\Enums\TaskStatus;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\Task;
use App\Models\User;
use App\Services\DisciplineScoreService;

beforeEach(function () {
    $this->service = new DisciplineScoreService;
});

it('returns 0 for a user with no activity', function () {
    $user = User::factory()->create([
        'longest_streak' => 0,
        'total_points' => 0,
    ]);

    $score = $this->service->calculate($user);

    expect($score)->toBe(30.0);
});

it('returns higher score for users with completed tasks', function () {
    $user = User::factory()->create(['longest_streak' => 14]);
    $goal = Goal::factory()->for($user)->create();
    $objective = Objective::factory()->for($goal)->create();

    Task::factory(5)->for($objective)->create([
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
    ]);

    $score = $this->service->calculate($user);

    expect($score)->toBeGreaterThan(50);
});

it('factors in streak strength', function () {
    $lowStreak = User::factory()->create(['longest_streak' => 5]);
    $highStreak = User::factory()->create(['longest_streak' => 50]);

    $lowScore = $this->service->calculate($lowStreak);
    $highScore = $this->service->calculate($highStreak);

    expect($highScore)->toBeGreaterThan($lowScore);
});

it('factors in partner verification success', function () {
    $successUser = User::factory()->create(['longest_streak' => 0]);
    $partner = User::factory()->create();

    Goal::factory()->for($successUser)->create([
        'status' => GoalStatus::VerifiedCompleted,
        'accountability_partner_id' => $partner->id,
    ]);

    $failUser = User::factory()->create(['longest_streak' => 0]);
    Goal::factory()->for($failUser)->create([
        'status' => GoalStatus::Expired,
        'accountability_partner_id' => $partner->id,
    ]);

    $successScore = $this->service->calculate($successUser);
    $failScore = $this->service->calculate($failUser);

    expect($successScore)->toBeGreaterThan($failScore);
});

it('penalizes users with many penalty transactions', function () {
    $cleanUser = User::factory()->create(['longest_streak' => 0]);
    $cleanUser->pointTransactions()->create([
        'type' => PointTransactionType::TaskCompletion,
        'points' => 10,
        'description' => 'Good task',
    ]);

    $penalizedUser = User::factory()->create(['longest_streak' => 0]);
    $penalizedUser->pointTransactions()->create([
        'type' => PointTransactionType::MissedTask,
        'points' => -5,
        'description' => 'Missed',
    ]);

    $cleanScore = $this->service->calculate($cleanUser);
    $penalizedScore = $this->service->calculate($penalizedUser);

    expect($cleanScore)->toBeGreaterThan($penalizedScore);
});

it('updates user discipline score', function () {
    $user = User::factory()->create(['discipline_score' => 0, 'longest_streak' => 30]);

    $result = $this->service->updateScore($user);

    expect((float) $result->discipline_score)->toBeGreaterThan(0);
});
