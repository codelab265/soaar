<?php

use App\Enums\ObjectiveStatus;
use App\Models\Objective;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders objectives with summary counts', function () {
    Objective::factory()->create(['status' => ObjectiveStatus::Pending]);
    Objective::factory()->create(['status' => ObjectiveStatus::InProgress]);
    Objective::factory()->create(['status' => ObjectiveStatus::Completed]);
    Objective::factory()->create(['status' => ObjectiveStatus::Verified]);

    $this->get('/admin/objectives')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/objectives')
            ->where('filters.search', '')
            ->where('filters.status', 'all')
            ->where('summary.total', 4)
            ->where('summary.pending', 1)
            ->where('summary.in_progress', 1)
            ->where('summary.completed', 1)
            ->where('summary.verified', 1)
            ->has('objectives.data', 4)
        );
});

it('filters objectives by status and search', function () {
    $owner = User::factory()->create(['name' => 'Rae Owner']);
    $objective = Objective::factory()->create([
        'title' => 'Daily stretch',
        'status' => ObjectiveStatus::Completed,
    ]);
    $objective->goal->update(['user_id' => $owner->id]);

    Objective::factory()->create(['status' => ObjectiveStatus::Pending]);

    $this->get('/admin/objectives?status=completed&search=Rae')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', 'Rae')
            ->where('filters.status', 'completed')
            ->has('objectives.data', 1)
            ->where('objectives.data.0.title', 'Daily stretch')
            ->where('objectives.data.0.owner.name', 'Rae Owner')
        );
});

it('allows admins to verify completed objectives', function () {
    $objective = Objective::factory()->create(['status' => ObjectiveStatus::Completed]);

    $this->post("/admin/objectives/{$objective->id}/verify")
        ->assertRedirect()
        ->assertSessionHas('success', 'Objective verified.');

    expect($objective->fresh()->status)->toBe(ObjectiveStatus::Verified);
});
