<?php

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionTier;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionService;

beforeEach(function () {
    $this->service = app(SubscriptionService::class);
    $this->user = User::factory()->create();
});

it('subscribes a user to the free tier', function () {
    $subscription = $this->service->subscribe($this->user, SubscriptionTier::Free);

    expect($subscription->tier)->toBe(SubscriptionTier::Free)
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->price_mwk)->toBe(0)
        ->and($subscription->ends_at)->toBeNull();
});

it('subscribes a user to the premium tier', function () {
    $subscription = $this->service->subscribe($this->user, SubscriptionTier::Premium);

    expect($subscription->tier)->toBe(SubscriptionTier::Premium)
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->price_mwk)->toBe(5000)
        ->and($subscription->ends_at)->not->toBeNull();
});

it('prevents duplicate active subscription of the same tier', function () {
    $this->service->subscribe($this->user, SubscriptionTier::Premium);

    $this->service->subscribe($this->user, SubscriptionTier::Premium);
})->throws(InvalidArgumentException::class, 'already has an active premium subscription');

it('cancels the current subscription when upgrading tiers', function () {
    $free = $this->service->subscribe($this->user, SubscriptionTier::Free);

    $premium = $this->service->subscribe($this->user, SubscriptionTier::Premium);

    expect($free->fresh()->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($premium->tier)->toBe(SubscriptionTier::Premium)
        ->and($premium->status)->toBe(SubscriptionStatus::Active);
});

it('cancels a subscription', function () {
    $subscription = $this->service->subscribe($this->user, SubscriptionTier::Premium);

    $cancelled = $this->service->cancel($subscription);

    expect($cancelled->status)->toBe(SubscriptionStatus::Cancelled)
        ->and($cancelled->cancelled_at)->not->toBeNull();
});

it('throws when cancelling an already cancelled subscription', function () {
    $subscription = Subscription::factory()->for($this->user)->cancelled()->create();

    $this->service->cancel($subscription);
})->throws(InvalidArgumentException::class, 'already cancelled');

it('renews a premium subscription', function () {
    $subscription = $this->service->subscribe($this->user, SubscriptionTier::Premium);
    $originalEnd = $subscription->ends_at->copy();

    $renewed = $this->service->renew($subscription);

    expect($renewed->status)->toBe(SubscriptionStatus::Active)
        ->and($renewed->ends_at->gt($originalEnd))->toBeTrue();
});

it('throws when renewing a free subscription', function () {
    $subscription = $this->service->subscribe($this->user, SubscriptionTier::Free);

    $this->service->renew($subscription);
})->throws(InvalidArgumentException::class, 'do not need renewal');

it('expires overdue subscriptions', function () {
    Subscription::factory()->for($this->user)->premium()->expired()->create([
        'status' => SubscriptionStatus::Active,
        'ends_at' => now()->subDay(),
    ]);

    $count = $this->service->expireOverdue();

    expect($count)->toBe(1)
        ->and(Subscription::where('status', SubscriptionStatus::Expired)->count())->toBe(1);
});

it('correctly identifies a premium user', function () {
    expect($this->service->isPremium($this->user))->toBeFalse();

    $this->service->subscribe($this->user, SubscriptionTier::Premium);

    expect($this->service->isPremium($this->user))->toBeTrue();
});

it('does not identify expired premium as premium', function () {
    Subscription::factory()->for($this->user)->premium()->expired()->create();

    expect($this->service->isPremium($this->user))->toBeFalse();
});
