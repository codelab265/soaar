<?php

use App\Enums\PointTransactionType;
use App\Models\User;

it('awards leaderboard bonus to top users once per day', function () {
    $users = User::factory()->count(3)->create();

    $users[0]->update(['total_points' => 300]);
    $users[1]->update(['total_points' => 200]);
    $users[2]->update(['total_points' => 100]);

    $this->artisan('app:award-leaderboard-bonus')
        ->expectsOutputToContain('Awarded leaderboard bonus to 3 user(s).')
        ->assertSuccessful();

    foreach ($users as $user) {
        $transactions = $user->pointTransactions()
            ->where('type', PointTransactionType::LeaderboardReward)
            ->count();

        expect($transactions)->toBe(1);
    }

    $this->artisan('app:award-leaderboard-bonus')
        ->expectsOutputToContain('Awarded leaderboard bonus to 0 user(s).')
        ->assertSuccessful();
});
