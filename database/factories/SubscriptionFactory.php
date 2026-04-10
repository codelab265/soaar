<?php

namespace Database\Factories;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionTier;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tier' => SubscriptionTier::Free,
            'status' => SubscriptionStatus::Active,
            'price_mwk' => 0,
            'starts_at' => now(),
            'ends_at' => null,
        ];
    }

    /**
     * A premium subscription at MWK 5,000/month.
     */
    public function premium(): static
    {
        return $this->state(fn () => [
            'tier' => SubscriptionTier::Premium,
            'price_mwk' => 5000,
            'ends_at' => now()->addMonth(),
        ]);
    }

    /**
     * A cancelled subscription.
     */
    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * An expired subscription.
     */
    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => SubscriptionStatus::Expired,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subMonth(),
        ]);
    }
}
