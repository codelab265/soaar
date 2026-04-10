<?php

use App\Enums\StreakType;
use App\Models\Streak;
use App\Models\User;

it('returns user streaks', function () {
    $user = User::factory()->create();
    Streak::create([
        'user_id' => $user->id,
        'type' => StreakType::Daily,
        'current_count' => 5,
        'longest_count' => 10,
        'last_activity_date' => now()->toDateString(),
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/streaks')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.current_count', 5);
});
