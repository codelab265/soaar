<?php

use App\Enums\TaskStatus;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['total_points' => 0]);
    $this->goal = Goal::factory()->for($this->user)->create();
    $this->objective = Objective::factory()->for($this->goal)->create();
});

it('lists tasks for an objective', function () {
    Task::factory()->count(3)->for($this->objective)->create();

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/objectives/{$this->objective->id}/tasks")
        ->assertSuccessful()
        ->assertJsonCount(3, 'data');
});

it('creates a task', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/objectives/{$this->objective->id}/tasks", [
            'title' => 'Read chapter 1',
            'difficulty' => 'simple',
            'scheduled_date' => now()->addDay()->toDateString(),
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Read chapter 1')
        ->assertJsonPath('data.points_value', 5);
});

it('creates an independent task', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/tasks', [
            'title' => 'Standalone task',
            'difficulty' => 'simple',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Standalone task')
        ->assertJsonPath('data.goal_id', null)
        ->assertJsonPath('data.objective_id', null);
});

it('creates a task linked to a goal without objective', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/tasks', [
            'title' => 'Goal-level task',
            'difficulty' => 'medium',
            'goal_id' => $this->goal->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.goal_id', $this->goal->id)
        ->assertJsonPath('data.objective_id', null);
});

it('lists today tasks for the authenticated user', function () {
    Task::factory()->for($this->objective)->create([
        'scheduled_date' => now()->toDateString(),
    ]);

    $otherUser = User::factory()->create();
    $otherGoal = Goal::factory()->for($otherUser)->create();
    $otherObjective = Objective::factory()->for($otherGoal)->create();
    Task::factory()->for($otherObjective)->create([
        'scheduled_date' => now()->toDateString(),
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/tasks/today')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('updates a task', function () {
    $task = Task::factory()->for($this->objective)->create();

    $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/tasks/{$task->id}", ['title' => 'Updated task'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Updated task');
});

it('deletes a task', function () {
    $task = Task::factory()->for($this->objective)->create();

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/tasks/{$task->id}")
        ->assertSuccessful();

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});

it('completes a task and awards points', function () {
    $task = Task::factory()->for($this->objective)->create([
        'difficulty' => 'medium',
        'points_value' => 10,
        'status' => TaskStatus::Pending,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/tasks/{$task->id}/complete", [
            'duration_minutes' => 15,
        ])
        ->assertSuccessful()
        ->assertJsonPath('points_awarded', 10);
});

it('awards zero points for tasks under 5 minutes', function () {
    $task = Task::factory()->for($this->objective)->create([
        'status' => TaskStatus::Pending,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/tasks/{$task->id}/complete", [
            'duration_minutes' => 3,
        ])
        ->assertSuccessful()
        ->assertJsonPath('points_awarded', 0);
});

it('validates duration_minutes is required', function () {
    $task = Task::factory()->for($this->objective)->create();

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/tasks/{$task->id}/complete", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['duration_minutes']);
});
