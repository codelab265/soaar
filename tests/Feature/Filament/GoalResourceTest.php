<?php

use App\Filament\Resources\Goals\Pages\CreateGoal;
use App\Filament\Resources\Goals\Pages\EditGoal;
use App\Filament\Resources\Goals\Pages\ListGoals;
use App\Filament\Resources\Goals\RelationManagers\ObjectivesRelationManager;
use App\Models\Goal;
use App\Models\Objective;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can list goals', function () {
    $goals = Goal::factory()->count(3)->create();

    Livewire::test(ListGoals::class)
        ->assertCanSeeTableRecords($goals);
});

it('can create a goal', function () {
    $user = User::factory()->create();

    Livewire::test(CreateGoal::class)
        ->fillForm([
            'user_id' => $user->id,
            'title' => 'Run a marathon',
            'why' => 'To get fitter',
            'category' => 'health',
            'deadline' => now()->addMonth()->toDateString(),
            'status' => 'active',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Goal::where('title', 'Run a marathon')->exists())->toBeTrue();
});

it('can edit a goal', function () {
    $goal = Goal::factory()->create();

    Livewire::test(EditGoal::class, ['record' => $goal->getRouteKey()])
        ->fillForm([
            'title' => 'Updated goal title',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($goal->fresh()->title)->toBe('Updated goal title');
});

it('shows objectives relation manager on goal edit', function () {
    $goal = Goal::factory()->create();
    $objectives = Objective::factory()->count(2)->for($goal)->create();

    Livewire::test(ObjectivesRelationManager::class, [
        'ownerRecord' => $goal,
        'pageClass' => EditGoal::class,
    ])->assertCanSeeTableRecords($objectives);
});
