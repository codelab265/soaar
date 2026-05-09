<?php

use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders subscriptions with summary counts', function () {
    Subscription::factory()->premium()->create(['status' => SubscriptionStatus::Active]);
    Subscription::factory()->cancelled()->create();
    Subscription::factory()->expired()->create();

    $this->get('/admin/subscriptions')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/subscriptions')
            ->where('filters.search', '')
            ->where('filters.tier', 'all')
            ->where('filters.status', 'all')
            ->where('summary.total', 3)
            ->where('summary.active', 1)
            ->where('summary.cancelled', 1)
            ->where('summary.expired', 1)
            ->where('summary.premium', 1)
            ->has('subscriptions.data', 3)
        );
});

it('filters subscriptions by status and search', function () {
    $user = User::factory()->create(['name' => 'Sami Subscriber']);
    Subscription::factory()->premium()->create(['user_id' => $user->id, 'status' => SubscriptionStatus::Active]);
    Subscription::factory()->expired()->create();

    $this->get('/admin/subscriptions?status=active&search=Sami')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.status', 'active')
            ->where('filters.search', 'Sami')
            ->has('subscriptions.data', 1)
            ->where('subscriptions.data.0.user.name', 'Sami Subscriber')
        );
});

it('allows admins to cancel an active subscription', function () {
    $subscription = Subscription::factory()->premium()->create(['status' => SubscriptionStatus::Active]);

    $this->post("/admin/subscriptions/{$subscription->id}/cancel")
        ->assertRedirect()
        ->assertSessionHas('success', 'Subscription cancelled.');

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Cancelled);
});
