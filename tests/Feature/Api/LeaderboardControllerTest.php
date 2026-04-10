<?php

use App\Models\User;

it('returns leaderboard', function () {
    User::factory()->count(5)->create(['total_points' => fake()->numberBetween(10, 500)]);
    $user = User::factory()->create(['total_points' => 100]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/leaderboard')
        ->assertSuccessful()
        ->assertJsonStructure(['leaderboard']);
});

it('returns user rank', function () {
    User::factory()->count(3)->create(['total_points' => 500]);
    $user = User::factory()->create(['total_points' => 100]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/leaderboard/me')
        ->assertSuccessful()
        ->assertJsonStructure(['rank', 'user']);
});
