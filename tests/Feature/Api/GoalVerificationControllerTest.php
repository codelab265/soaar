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

it('partner can request proof', function () {
    $goal = Goal::factory()->for($this->owner)->create([
        'status' => GoalStatus::PendingVerification,
        'accountability_partner_id' => $this->partner->id,
    ]);

    $this->actingAs($this->partner, 'sanctum')
        ->postJson("/api/v1/goals/{$goal->id}/request-proof", [
            'message' => 'Please share proof links.',
        ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'Proof requested successfully.');
});

it('owner can submit proof after partner request', function () {
    $goal = Goal::factory()->for($this->owner)->create([
        'status' => GoalStatus::PendingVerification,
        'accountability_partner_id' => $this->partner->id,
        'proof_requested_at' => now(),
        'proof_request_message' => 'Need proof',
    ]);

    $this->actingAs($this->owner, 'sanctum')
        ->postJson("/api/v1/goals/{$goal->id}/submit-proof", [
            'submission' => 'Shared proof URL',
        ])
        ->assertSuccessful()
        ->assertJsonPath('message', 'Proof submitted successfully.');
});
