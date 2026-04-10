<?php

use App\Models\Goal;
use App\Models\Objective;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->goal = Goal::factory()->for($this->user)->create();
});

it('lists objectives for a goal', function () {
    Objective::factory()->count(3)->for($this->goal)->create();

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/goals/{$this->goal->id}/objectives")
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates an objective', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/goals/{$this->goal->id}/objectives", [
            'title' => 'Week 1 milestones',
            'priority' => 1,
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Week 1 milestones');
});

it('updates an objective', function () {
    $objective = Objective::factory()->for($this->goal)->create();

    $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/objectives/{$objective->id}", ['title' => 'Updated'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Updated');
});

it('deletes an objective', function () {
    $objective = Objective::factory()->for($this->goal)->create();

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/objectives/{$objective->id}")
        ->assertSuccessful();

    $this->assertSoftDeleted('objectives', ['id' => $objective->id]);
});

it('completes an objective and awards points', function () {
    $objective = Objective::factory()->for($this->goal)->create();

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/objectives/{$objective->id}/complete")
        ->assertSuccessful()
        ->assertJsonStructure(['objective', 'points_awarded']);
});

it('prevents accessing another user objectives', function () {
    $otherGoal = Goal::factory()->create();

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/goals/{$otherGoal->id}/objectives")
        ->assertForbidden();
});
