<?php

use App\Enums\PointTransactionType;
use App\Models\PointTransaction;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders point transactions with summary counts', function () {
    PointTransaction::factory()->create(['points' => 10]);
    PointTransaction::factory()->create(['points' => -5]);

    $this->get('/admin/point-transactions')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/point-transactions')
            ->where('filters.search', '')
            ->where('filters.type', 'all')
            ->where('summary.total', 2)
            ->where('summary.awarded', 1)
            ->where('summary.deducted', 1)
            ->has('transactions.data', 2)
        );
});

it('filters point transactions by type and search', function () {
    $user = User::factory()->create(['name' => 'Pia Points']);

    PointTransaction::factory()->create([
        'user_id' => $user->id,
        'type' => PointTransactionType::TaskCompletion,
        'description' => 'Completed task: X',
        'points' => 5,
    ]);

    PointTransaction::factory()->create([
        'type' => PointTransactionType::GoalExpired,
        'description' => 'Goal expired: Y',
        'points' => -75,
    ]);

    $this->get('/admin/point-transactions?type=task_completion&search=Pia')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.type', 'task_completion')
            ->where('filters.search', 'Pia')
            ->has('transactions.data', 1)
            ->where('transactions.data.0.user.name', 'Pia Points')
        );
});
