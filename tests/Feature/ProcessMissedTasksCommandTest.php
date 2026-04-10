<?php

use App\Enums\TaskStatus;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['total_points' => 100]);
    $this->goal = Goal::factory()->for($this->user)->create();
    $this->objective = Objective::factory()->for($this->goal)->create();
});

it('marks pending tasks with past scheduled dates as missed', function () {
    $missedTask = Task::factory()->for($this->objective)->create([
        'status' => TaskStatus::Pending,
        'scheduled_date' => now()->subDays(2),
    ]);

    $futureTask = Task::factory()->for($this->objective)->create([
        'status' => TaskStatus::Pending,
        'scheduled_date' => now()->addDays(1),
    ]);

    $this->artisan('app:process-missed-tasks')
        ->expectsOutputToContain('Processed 1 missed task(s)')
        ->assertSuccessful();

    expect($missedTask->fresh()->status)->toBe(TaskStatus::Missed)
        ->and($futureTask->fresh()->status)->toBe(TaskStatus::Pending)
        ->and($this->user->fresh()->total_points)->toBe(95);
});

it('does not process already completed tasks', function () {
    Task::factory()->for($this->objective)->create([
        'status' => TaskStatus::Completed,
        'scheduled_date' => now()->subDays(2),
        'completed_at' => now()->subDays(2),
    ]);

    $this->artisan('app:process-missed-tasks')
        ->expectsOutputToContain('Processed 0 missed task(s)')
        ->assertSuccessful();

    expect($this->user->fresh()->total_points)->toBe(100);
});

it('does nothing when no tasks are past due', function () {
    Task::factory()->for($this->objective)->create([
        'status' => TaskStatus::Pending,
        'scheduled_date' => now()->addDays(3),
    ]);

    $this->artisan('app:process-missed-tasks')
        ->expectsOutputToContain('Processed 0 missed task(s)')
        ->assertSuccessful();
});
