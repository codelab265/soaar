<?php

namespace App\Services;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Models\PointTransaction;
use App\Notifications\PartnerCheckInNotification;
use InvalidArgumentException;

class GoalVerificationService
{
    /** Hours before auto-approval kicks in. */
    public const AUTO_APPROVE_HOURS = 48;

    /** Reward multiplier for auto-approved goals (80%). */
    public const AUTO_APPROVE_MULTIPLIER = 0.80;

    public function __construct(
        public GoalService $goalService,
        public GoalCompletionService $goalCompletionService,
        public PointsService $pointsService,
    ) {}

    /**
     * Submit a goal for verification by the accountability partner.
     */
    public function submitForVerification(Goal $goal): Goal
    {
        $goal = $this->goalService->submitForVerification($goal);

        if ($goal->accountability_partner_id) {
            $partner = $goal->accountabilityPartner;
            $partner->notify(new PartnerCheckInNotification($goal, $goal->user->name));
        }

        return $goal;
    }

    /**
     * Partner approves the goal — full rewards.
     *
     * @return array{goal: Goal, transactions: list<PointTransaction>, total_points: int}
     */
    public function approveGoal(Goal $goal): array
    {
        if ($goal->status !== GoalStatus::PendingVerification) {
            throw new InvalidArgumentException('Only goals pending verification can be approved.');
        }

        return $this->goalCompletionService->completeGoal($goal, isPartnerVerified: true);
    }

    /**
     * Partner rejects the goal — apply penalty and revert to active.
     */
    public function rejectGoal(Goal $goal): Goal
    {
        if ($goal->status !== GoalStatus::PendingVerification) {
            throw new InvalidArgumentException('Only goals pending verification can be rejected.');
        }

        $goal->update(['status' => GoalStatus::Active]);

        $this->pointsService->applyPartnerRejectionPenalty($goal->user, $goal);

        return $goal->fresh();
    }

    /**
     * Auto-approve goals pending verification for 48+ hours at 80% reward.
     */
    public function autoApproveGoal(Goal $goal): array
    {
        if ($goal->status !== GoalStatus::PendingVerification) {
            throw new InvalidArgumentException('Only goals pending verification can be auto-approved.');
        }

        return $this->goalCompletionService->completeGoal(
            $goal,
            isPartnerVerified: false,
            rewardMultiplier: self::AUTO_APPROVE_MULTIPLIER,
        );
    }
}
