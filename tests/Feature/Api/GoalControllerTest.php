<?php

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists authenticated user goals', function () {
    Goal::factory()->count(3)->for($this->user)->create();
    Goal::factory()->create(); // another user's goal

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/goals')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a goal', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/goals', [
            'title' => 'Learn Laravel',
            'description' => 'Master Laravel framework',
            'why' => 'Career growth',
            'category' => 'education',
            'deadline' => now()->addMonth()->toDateString(),
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Learn Laravel');

    $this->assertDatabaseHas('goals', ['title' => 'Learn Laravel', 'user_id' => $this->user->id]);
});

it('shows a goal with objectives count', function () {
    $goal = Goal::factory()->for($this->user)->create();

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/goals/{$goal->id}")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $goal->id);
});

it('prevents viewing another user goal', function () {
    $goal = Goal::factory()->create();

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/goals/{$goal->id}")
        ->assertForbidden();
});

it('updates a goal', function () {
    $goal = Goal::factory()->for($this->user)->create();

    $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/goals/{$goal->id}", ['title' => 'Updated Title'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Updated Title');
});

it('deletes a goal', function () {
    $goal = Goal::factory()->for($this->user)->create();

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/goals/{$goal->id}")
        ->assertSuccessful();

    $this->assertSoftDeleted('goals', ['id' => $goal->id]);
});

it('cancels an active goal', function () {
    $goal = Goal::factory()->for($this->user)->create(['status' => GoalStatus::Active]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/goals/{$goal->id}/cancel")
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'cancelled');
});

it('submits a goal for verification', function () {
    $goal = Goal::factory()->for($this->user)->create(['status' => GoalStatus::Active]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/goals/{$goal->id}/submit-verification")
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'pending_verification');
});

it('validates required fields on create', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/goals', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'category', 'deadline']);
});
