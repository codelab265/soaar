<?php

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create(['total_points' => 0]);
    $this->partner = User::factory()->create();
});

it('partner approves a goal', function () {
    $goal = Goal::factory()->for($this->owner)->create([
        'status' => GoalStatus::PendingVerification,
        'accountability_partner_id' => $this->partner->id,
        'deadline' => now()->subDay(),
    ]);

    $this->actingAs($this->partner, 'sanctum')
        ->postJson("/api/v1/goals/{$goal->id}/approve")
        ->assertSuccessful()
        ->assertJsonStructure(['message', 'total_points']);
});

it('partner rejects a goal', function () {
    $goal = Goal::factory()->for($this->owner)->create([
        'status' => GoalStatus::PendingVerification,
        'accountability_partner_id' => $this->partner->id,
    ]);

    $this->actingAs($this->partner, 'sanctum')
        ->postJson("/api/v1/goals/{$goal->id}/reject")
        ->assertSuccessful()
        ->assertJsonPath('message', 'Goal rejected.');
});

it('prevents non-partner from approving', function () {
    $goal = Goal::factory()->for($this->owner)->create([
        'status' => GoalStatus::PendingVerification,
        'accountability_partner_id' => $this->partner->id,
    ]);

    $random = User::factory()->create();

    $this->actingAs($random, 'sanctum')
        ->postJson("/api/v1/goals/{$goal->id}/approve")
        ->assertForbidden();
});
