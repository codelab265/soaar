<?php

namespace Database\Factories;

use App\Enums\PartnerRequestStatus;
use App\Models\AccountabilityPartnerRequest;
use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccountabilityPartnerRequest>
 */
class AccountabilityPartnerRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'goal_id' => Goal::factory(),
            'requester_id' => User::factory(),
            'partner_id' => User::factory(),
            'status' => PartnerRequestStatus::Pending,
        ];
    }

    /**
     * Set the request as accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PartnerRequestStatus::Accepted,
            'responded_at' => now(),
        ]);
    }

    /**
     * Set the request as declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PartnerRequestStatus::Declined,
            'responded_at' => now(),
        ]);
    }
}
