<?php

use App\Filament\Resources\Objectives\Pages\CreateObjective;
use App\Filament\Resources\Objectives\Pages\EditObjective;
use App\Filament\Resources\Objectives\Pages\ListObjectives;
use App\Filament\Resources\Objectives\RelationManagers\TasksRelationManager;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\Task;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can list objectives', function () {
    $objectives = Objective::factory()->count(3)->create();

    Livewire::test(ListObjectives::class)
        ->assertCanSeeTableRecords($objectives);
});

it('can create an objective', function () {
    $goal = Goal::factory()->create();

    Livewire::test(CreateObjective::class)
        ->fillForm([
            'goal_id' => $goal->id,
            'title' => 'Train 3x per week',
            'status' => 'pending',
            'priority' => 1,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Objective::where('title', 'Train 3x per week')->exists())->toBeTrue();
});

it('shows tasks relation manager on objective edit', function () {
    $objective = Objective::factory()->create();
    $tasks = Task::factory()->count(2)->for($objective)->create();

    Livewire::test(TasksRelationManager::class, [
        'ownerRecord' => $objective,
        'pageClass' => EditObjective::class,
    ])->assertCanSeeTableRecords($tasks);
});
