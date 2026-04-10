<?php

use App\Enums\PointTransactionType;
use App\Models\User;
use App\Services\LeaderboardService;

beforeEach(function () {
    $this->service = app(LeaderboardService::class);
});

it('returns users sorted by total points', function () {
    User::factory()->create(['total_points' => 50]);
    User::factory()->create(['total_points' => 200]);
    User::factory()->create(['total_points' => 100]);

    $leaderboard = $this->service->getLeaderboard();

    expect($leaderboard->first()->total_points)->toBe(200)
        ->and($leaderboard->last()->total_points)->toBe(50);
});

it('awards top 10 bonus to qualifying users', function () {
    $users = User::factory(3)->create(['total_points' => 100]);

    $count = $this->service->awardTopTenBonus();

    expect($count)->toBe(3);

    foreach ($users as $user) {
        expect($user->fresh()->total_points)->toBe(200);

        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'type' => PointTransactionType::LeaderboardReward->value,
            'points' => 100,
        ]);
    }
});

it('skips users with zero points for top 10 bonus', function () {
    User::factory()->create(['total_points' => 100]);
    User::factory()->create(['total_points' => 0]);

    $count = $this->service->awardTopTenBonus();

    expect($count)->toBe(1);
});

it('calculates user rank correctly', function () {
    User::factory()->create(['total_points' => 200]);
    $user = User::factory()->create(['total_points' => 100]);
    User::factory()->create(['total_points' => 50]);

    $rank = $this->service->getUserRank($user);

    expect($rank)->toBe(2);
});
