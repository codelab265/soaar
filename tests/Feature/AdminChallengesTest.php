<?php

use App\Enums\ChallengeStatus;
use App\Models\Challenge;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders challenges with summary counts', function () {
    Challenge::factory()->create(['status' => ChallengeStatus::Active]);
    Challenge::factory()->create(['status' => ChallengeStatus::Completed]);
    Challenge::factory()->create(['status' => ChallengeStatus::Cancelled]);

    $this->get('/admin/challenges')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/challenges')
            ->where('filters.search', '')
            ->where('filters.status', 'all')
            ->where('summary.total', 3)
            ->where('summary.active', 1)
            ->where('summary.completed', 1)
            ->where('summary.cancelled', 1)
            ->has('challenges.data', 3)
        );
});

it('filters challenges by status and search', function () {
    Challenge::factory()->create(['status' => ChallengeStatus::Active, 'title' => 'Do 100 pushups']);
    Challenge::factory()->create(['status' => ChallengeStatus::Completed, 'title' => 'Read daily']);

    $this->get('/admin/challenges?status=active&search=pushups')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.status', 'active')
            ->where('filters.search', 'pushups')
            ->has('challenges.data', 1)
            ->where('challenges.data.0.title', 'Do 100 pushups')
        );
});

it('allows admins to create challenges', function () {
    $this->get('/admin/challenges/create')->assertOk();

    $this->post('/admin/challenges', [
        'title' => 'Ship something',
        'description' => 'A test challenge',
        'duration_days' => 7,
        'reward_points' => 50,
        'status' => ChallengeStatus::Active->value,
        'start_date' => now()->toDateString(),
        'end_date' => now()->addDays(6)->toDateString(),
    ])->assertRedirect('/admin/challenges');

    expect(Challenge::query()->where('title', 'Ship something')->exists())->toBeTrue();
});

it('allows admins to edit challenges', function () {
    $challenge = Challenge::factory()->create([
        'title' => 'Old title',
        'status' => ChallengeStatus::Active,
    ]);

    $this->get("/admin/challenges/{$challenge->id}/edit")->assertOk();

    $this->put("/admin/challenges/{$challenge->id}", [
        'title' => 'New title',
        'description' => $challenge->description,
        'duration_days' => $challenge->duration_days,
        'reward_points' => $challenge->reward_points,
        'status' => ChallengeStatus::Completed->value,
        'start_date' => $challenge->start_date->toDateString(),
        'end_date' => $challenge->end_date->toDateString(),
    ])->assertRedirect('/admin/challenges');

    expect($challenge->fresh()->title)->toBe('New title');
    expect($challenge->fresh()->status)->toBe(ChallengeStatus::Completed);
});

it('allows admins to update challenge status quickly', function () {
    $challenge = Challenge::factory()->create(['status' => ChallengeStatus::Active]);

    $this->post("/admin/challenges/{$challenge->id}/status", [
        'status' => ChallengeStatus::Cancelled->value,
    ])->assertRedirect();

    expect($challenge->fresh()->status)->toBe(ChallengeStatus::Cancelled);
});

it('allows admins to delete challenges', function () {
    $challenge = Challenge::factory()->create();

    $this->delete("/admin/challenges/{$challenge->id}")
        ->assertRedirect()
        ->assertSessionHas('success', 'Challenge deleted.');

    expect(Challenge::query()->whereKey($challenge->id)->exists())->toBeFalse();
});
