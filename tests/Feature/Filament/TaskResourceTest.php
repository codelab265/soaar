<?php

use App\Filament\Resources\Tasks\Pages\CreateTask;
use App\Filament\Resources\Tasks\Pages\ListTasks;
use App\Models\Objective;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can list tasks', function () {
    $tasks = Task::factory()->count(3)->create();

    Livewire::test(ListTasks::class)
        ->assertCanSeeTableRecords($tasks);
});

it('can create a task', function () {
    $objective = Objective::factory()->create();

    Livewire::test(CreateTask::class)
        ->fillForm([
            'objective_id' => $objective->id,
            'title' => 'Run 5km',
            'difficulty' => 'simple',
            'minimum_duration' => 20,
            'points_value' => 5,
            'status' => 'pending',
            'repetition_count' => 0,
            'repetition_decay' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Task::where('title', 'Run 5km')->exists())->toBeTrue();
});
