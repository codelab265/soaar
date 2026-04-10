<?php

namespace App\Services;

use App\Enums\PartnerRequestStatus;
use App\Models\AccountabilityPartnerRequest;
use App\Models\Goal;
use App\Models\User;
use InvalidArgumentException;

class AccountabilityPartnerService
{
    /**
     * Send a partner request for a goal.
     */
    public function sendRequest(Goal $goal, User $partner): AccountabilityPartnerRequest
    {
        if ($goal->user_id === $partner->id) {
            throw new InvalidArgumentException('A user cannot be their own accountability partner.');
        }

        if ($goal->accountability_partner_id !== null) {
            throw new InvalidArgumentException('This goal already has an accountability partner.');
        }

        return AccountabilityPartnerRequest::create([
            'goal_id' => $goal->id,
            'requester_id' => $goal->user_id,
            'partner_id' => $partner->id,
            'status' => PartnerRequestStatus::Pending,
        ]);
    }

    /**
     * Accept a partner request and link the partner to the goal.
     */
    public function acceptRequest(AccountabilityPartnerRequest $request): AccountabilityPartnerRequest
    {
        if ($request->status !== PartnerRequestStatus::Pending) {
            throw new InvalidArgumentException('Only pending requests can be accepted.');
        }

        $request->update([
            'status' => PartnerRequestStatus::Accepted,
            'responded_at' => now(),
        ]);

        $request->goal->update([
            'accountability_partner_id' => $request->partner_id,
        ]);

        return $request->fresh();
    }

    /**
     * Decline a partner request.
     */
    public function declineRequest(AccountabilityPartnerRequest $request): AccountabilityPartnerRequest
    {
        if ($request->status !== PartnerRequestStatus::Pending) {
            throw new InvalidArgumentException('Only pending requests can be declined.');
        }

        $request->update([
            'status' => PartnerRequestStatus::Declined,
            'responded_at' => now(),
        ]);

        return $request->fresh();
    }
}
