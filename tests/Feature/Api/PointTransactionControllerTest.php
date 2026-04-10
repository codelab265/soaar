<?php

use App\Enums\PointTransactionType;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['total_points' => 100]);
});

it('lists user point transactions', function () {
    $this->user->pointTransactions()->createMany([
        ['type' => PointTransactionType::TaskCompletion, 'points' => 10, 'description' => 'Task 1'],
        ['type' => PointTransactionType::MissedTask, 'points' => -5, 'description' => 'Missed'],
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/points')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('returns points summary', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/points/summary')
        ->assertSuccessful()
        ->assertJsonStructure(['total_points', 'daily_earned', 'daily_remaining', 'daily_cap'])
        ->assertJsonPath('total_points', 100);
});
