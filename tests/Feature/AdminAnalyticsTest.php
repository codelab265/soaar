<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders analytics page for admins', function () {
    $this->get('/admin/analytics')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/analytics')
            ->where('filters.days', 30)
            ->has('series')
            ->has('summary')
        );
});
