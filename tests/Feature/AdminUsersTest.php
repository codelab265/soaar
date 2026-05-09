<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders users with summary counts', function () {
    User::factory()->create(['is_admin' => false, 'suspended_at' => null]);
    User::factory()->create(['is_admin' => true, 'suspended_at' => null]);
    User::factory()->create(['is_admin' => false, 'suspended_at' => now()]);

    $this->get('/admin/users')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users')
            ->where('filters.search', '')
            ->where('filters.status', 'all')
            ->where('summary.total', 4) // includes the logged-in admin
            ->where('summary.admins', 2) // includes the logged-in admin
            ->has('users.data')
        );
});

it('filters users by suspended status and search', function () {
    $suspended = User::factory()->create(['name' => 'Zed Suspended', 'suspended_at' => now()]);
    User::factory()->create(['name' => 'Ava Active', 'suspended_at' => null]);

    $this->get('/admin/users?status=suspended&search=Zed')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.status', 'suspended')
            ->where('filters.search', 'Zed')
            ->has('users.data', 1)
            ->where('users.data.0.id', $suspended->id)
        );
});

it('allows admins to suspend and unsuspend users', function () {
    $user = User::factory()->create(['suspended_at' => null]);

    $this->post("/admin/users/{$user->id}/suspend")
        ->assertRedirect()
        ->assertSessionHas('success', 'User suspended.');

    expect($user->fresh()->suspended_at)->not->toBeNull();

    $this->post("/admin/users/{$user->id}/unsuspend")
        ->assertRedirect()
        ->assertSessionHas('success', 'User unsuspended.');

    expect($user->fresh()->suspended_at)->toBeNull();
});
