<?php

use App\Models\User;

it('updates discipline scores for all users', function () {
    User::factory(3)->create(['longest_streak' => 10]);

    $this->artisan('app:update-discipline-scores')
        ->expectsOutputToContain('Updated discipline scores for 3 user(s)')
        ->assertSuccessful();

    User::all()->each(function ($user) {
        expect((float) $user->discipline_score)->toBeGreaterThanOrEqual(0);
    });
});
