<?php

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders goals with summary counts', function () {
    Goal::factory()->create(['status' => GoalStatus::Active]);
    Goal::factory()->create(['status' => GoalStatus::PendingVerification]);
    Goal::factory()->create(['status' => GoalStatus::VerifiedCompleted]);
    Goal::factory()->create(['status' => GoalStatus::Cancelled]);
    Goal::factory()->create(['status' => GoalStatus::Expired]);

    $this->get('/admin/goals')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/goals')
            ->where('filters.search', '')
            ->where('filters.status', 'all')
            ->where('summary.total', 5)
            ->where('summary.active', 1)
            ->where('summary.pending_verification', 1)
            ->where('summary.verified_completed', 1)
            ->where('summary.cancelled', 1)
            ->where('summary.expired', 1)
            ->has('goals.data', 5)
        );
});

it('filters goals by status and search', function () {
    $owner = User::factory()->create(['name' => 'Nova Owner']);

    Goal::factory()->for($owner)->create([
        'title' => 'Run a marathon',
        'status' => GoalStatus::PendingVerification,
    ]);

    Goal::factory()->create(['status' => GoalStatus::Active]);

    $this->get('/admin/goals?status=pending_verification&search=Nova')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', 'Nova')
            ->where('filters.status', 'pending_verification')
            ->has('goals.data', 1)
            ->where('goals.data.0.user.name', 'Nova Owner')
            ->where('goals.data.0.title', 'Run a marathon')
        );
});

it('allows admins to approve a goal pending verification', function () {
    $user = User::factory()->create(['total_points' => 0]);
    $goal = Goal::factory()->for($user)->create([
        'status' => GoalStatus::PendingVerification,
        'deadline' => now()->subDay(),
    ]);

    $this->post("/admin/goals/{$goal->id}/approve")
        ->assertRedirect()
        ->assertSessionHas('success', 'Goal approved.');

    expect($goal->fresh()->status)->toBe(GoalStatus::VerifiedCompleted);
});

it('allows admins to reject a goal pending verification', function () {
    $user = User::factory()->create(['total_points' => 100]);
    $goal = Goal::factory()->for($user)->create([
        'status' => GoalStatus::PendingVerification,
    ]);

    $this->post("/admin/goals/{$goal->id}/reject")
        ->assertRedirect()
        ->assertSessionHas('success', 'Goal rejected.');

    expect($goal->fresh()->status)->toBe(GoalStatus::Active);
});
