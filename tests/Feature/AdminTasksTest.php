<?php

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders tasks with summary counts', function () {
    Task::factory()->create(['status' => TaskStatus::Pending]);
    Task::factory()->create(['status' => TaskStatus::InProgress]);
    Task::factory()->create(['status' => TaskStatus::Completed]);
    Task::factory()->create(['status' => TaskStatus::Missed]);

    $this->get('/admin/tasks')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/tasks')
            ->where('filters.search', '')
            ->where('filters.status', 'all')
            ->where('filters.difficulty', 'all')
            ->where('summary.total', 4)
            ->where('summary.pending', 1)
            ->where('summary.in_progress', 1)
            ->where('summary.completed', 1)
            ->where('summary.missed', 1)
            ->has('tasks.data', 4)
        );
});

it('filters tasks by status', function () {
    Task::factory()->create(['status' => TaskStatus::Pending, 'title' => 'A']);
    Task::factory()->create(['status' => TaskStatus::Completed, 'title' => 'B']);

    $this->get('/admin/tasks?status=completed')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.status', 'completed')
            ->has('tasks.data', 1)
            ->where('tasks.data.0.title', 'B')
        );
});

it('allows admins to mark a task as missed', function () {
    $task = Task::factory()->create(['status' => TaskStatus::Pending]);

    $this->post("/admin/tasks/{$task->id}/miss")
        ->assertRedirect()
        ->assertSessionHas('success', 'Task marked as missed.');

    expect($task->fresh()->status)->toBe(TaskStatus::Missed);
});
