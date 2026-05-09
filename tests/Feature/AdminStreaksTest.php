<?php

use App\Enums\StreakType;
use App\Models\Streak;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders streaks with summary counts', function () {
    Streak::factory()->create(['type' => StreakType::Daily]);
    Streak::factory()->create(['type' => StreakType::Challenge]);

    $this->get('/admin/streaks')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/streaks')
            ->where('filters.search', '')
            ->where('filters.type', 'all')
            ->where('summary.total', 2)
            ->where('summary.daily', 1)
            ->where('summary.challenge', 1)
            ->has('streaks.data', 2)
        );
});

it('filters streaks by type and search', function () {
    $user = User::factory()->create(['name' => 'Tala Streak']);
    Streak::factory()->create(['user_id' => $user->id, 'type' => StreakType::Daily]);
    Streak::factory()->create(['type' => StreakType::Challenge]);

    $this->get('/admin/streaks?type=daily&search=Tala')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.type', 'daily')
            ->where('filters.search', 'Tala')
            ->has('streaks.data', 1)
            ->where('streaks.data.0.user.name', 'Tala Streak')
        );
});

it('allows admins to break a streak', function () {
    $streak = Streak::factory()->create([
        'type' => StreakType::Daily,
        'current_count' => 5,
    ]);

    $this->post("/admin/streaks/{$streak->id}/break")
        ->assertRedirect()
        ->assertSessionHas('success', 'Streak broken.');

    expect($streak->fresh()->current_count)->toBe(0);
});
