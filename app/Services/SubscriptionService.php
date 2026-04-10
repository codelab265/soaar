<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionTier;
use App\Models\Subscription;
use App\Models\User;
use InvalidArgumentException;

class SubscriptionService
{
    /** Monthly prices in MWK by tier. */
    public const TIER_PRICES = [
        'free' => 0,
        'premium' => 5000,
    ];

    /**
     * Subscribe a user to a tier.
     *
     * @throws InvalidArgumentException
     */
    public function subscribe(User $user, SubscriptionTier $tier): Subscription
    {
        $activeSubscription = $this->getActiveSubscription($user);

        if ($activeSubscription?->tier === $tier) {
            throw new InvalidArgumentException("User already has an active {$tier->value} subscription.");
        }

        if ($activeSubscription) {
            $this->cancel($activeSubscription);
        }

        return Subscription::create([
            'user_id' => $user->id,
            'tier' => $tier,
            'status' => SubscriptionStatus::Active,
            'price_mwk' => self::TIER_PRICES[$tier->value] ?? 0,
            'starts_at' => now(),
            'ends_at' => $tier === SubscriptionTier::Free ? null : now()->addMonth(),
        ]);
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription): Subscription
    {
        if ($subscription->status === SubscriptionStatus::Cancelled) {
            throw new InvalidArgumentException('Subscription is already cancelled.');
        }

        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        return $subscription->fresh();
    }

    /**
     * Renew a premium subscription for another month.
     */
    public function renew(Subscription $subscription): Subscription
    {
        if ($subscription->tier === SubscriptionTier::Free) {
            throw new InvalidArgumentException('Free subscriptions do not need renewal.');
        }

        if ($subscription->status === SubscriptionStatus::Cancelled) {
            throw new InvalidArgumentException('Cannot renew a cancelled subscription. Subscribe again instead.');
        }

        $newEnd = ($subscription->ends_at && $subscription->ends_at->isFuture())
            ? $subscription->ends_at->addMonth()
            : now()->addMonth();

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'ends_at' => $newEnd,
        ]);

        return $subscription->fresh();
    }

    /**
     * Expire subscriptions that have passed their end date.
     */
    public function expireOverdue(): int
    {
        return Subscription::where('status', SubscriptionStatus::Active)
            ->where('tier', SubscriptionTier::Premium)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update(['status' => SubscriptionStatus::Expired]);
    }

    /**
     * Get the user's current active subscription.
     */
    public function getActiveSubscription(User $user): ?Subscription
    {
        return $user->subscriptions()
            ->where('status', SubscriptionStatus::Active)
            ->latest('starts_at')
            ->first();
    }

    /**
     * Check whether a user has an active premium subscription.
     */
    public function isPremium(User $user): bool
    {
        $subscription = $this->getActiveSubscription($user);

        return $subscription?->isPremium() ?? false;
    }
}
