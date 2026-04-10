<?php

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['total_points' => 0]);
});

it('lists active challenges', function () {
    Challenge::factory()->count(3)->create(['status' => ChallengeStatus::Active]);
    Challenge::factory()->create(['status' => ChallengeStatus::Completed]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/challenges')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('shows a challenge', function () {
    $challenge = Challenge::factory()->create();

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/challenges/{$challenge->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $challenge->id);
});

it('joins a challenge', function () {
    $challenge = Challenge::factory()->create(['status' => ChallengeStatus::Active]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/challenges/{$challenge->id}/join")
        ->assertSuccessful()
        ->assertJsonPath('message', 'Joined challenge successfully.');
});

it('checks challenge progress', function () {
    $challenge = Challenge::factory()->create(['status' => ChallengeStatus::Active]);
    $this->user->challenges()->attach($challenge->id, [
        'joined_at' => now(),
        'status' => ChallengeStatus::Active->value,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/challenges/{$challenge->id}/progress")
        ->assertSuccessful()
        ->assertJsonStructure(['status', 'days_elapsed', 'days_remaining']);
});
