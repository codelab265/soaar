<?php

use App\Filament\Resources\Challenges\Pages\CreateChallenge;
use App\Filament\Resources\Challenges\Pages\ListChallenges;
use App\Models\Challenge;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can list challenges', function () {
    $challenges = Challenge::factory()->count(3)->create();

    Livewire::test(ListChallenges::class)
        ->assertCanSeeTableRecords($challenges);
});

it('can create a challenge', function () {
    Livewire::test(CreateChallenge::class)
        ->fillForm([
            'title' => '30-day discipline',
            'duration_days' => 30,
            'reward_points' => 200,
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Challenge::where('title', '30-day discipline')->exists())->toBeTrue();
});
