<?php

namespace App\Services;

use App\Enums\GoalStatus;
use App\Enums\PointTransactionType;
use App\Models\Goal;
use InvalidArgumentException;

class GoalService
{
    /** Minimum hours between goal deletions to prevent gaming. */
    public const DELETION_COOLDOWN_HOURS = 24;

    /**
     * Valid status transitions map.
     *
     * @var array<string, list<GoalStatus>>
     */
    private const TRANSITIONS = [
        'active' => [GoalStatus::PendingVerification, GoalStatus::Cancelled, GoalStatus::Expired],
        'pending_verification' => [GoalStatus::VerifiedCompleted, GoalStatus::Active],
        'verified_completed' => [],
        'cancelled' => [],
        'expired' => [],
    ];

    public function __construct(
        public PointsService $pointsService,
    ) {}

    /**
     * Expire an active goal past its deadline and apply penalty.
     */
    public function expireGoal(Goal $goal): Goal
    {
        $this->assertTransition($goal, GoalStatus::Expired);

        $goal->update(['status' => GoalStatus::Expired]);

        $this->pointsService->deductPoints(
            user: $goal->user,
            type: PointTransactionType::GoalExpired,
            points: PointsService::GOAL_EXPIRED_PENALTY,
            description: "Goal expired: {$goal->title}",
            transactionable: $goal,
        );

        return $goal->fresh();
    }

    /**
     * Cancel an active goal (no penalty for voluntary cancellation).
     */
    public function cancelGoal(Goal $goal): Goal
    {
        $this->assertTransition($goal, GoalStatus::Cancelled);

        $goal->update(['status' => GoalStatus::Cancelled]);

        return $goal->fresh();
    }

    /**
     * Submit a goal for partner verification.
     */
    public function submitForVerification(Goal $goal): Goal
    {
        $this->assertTransition($goal, GoalStatus::PendingVerification);

        $goal->update(['status' => GoalStatus::PendingVerification]);

        return $goal->fresh();
    }

    /**
     * Mark a goal as verified completed (called after partner approval or auto-approve).
     */
    public function markVerifiedCompleted(Goal $goal): Goal
    {
        $this->assertTransition($goal, GoalStatus::VerifiedCompleted);

        $goal->update(['status' => GoalStatus::VerifiedCompleted]);

        return $goal->fresh();
    }

    /**
     * Check whether a user can delete a goal (cooldown enforcement).
     */
    public function canDeleteGoal(Goal $goal): bool
    {
        $lastDeletion = $goal->user->goals()
            ->onlyTrashed()
            ->latest('deleted_at')
            ->value('deleted_at');

        if (! $lastDeletion) {
            return true;
        }

        return now()->diffInHours($lastDeletion) >= self::DELETION_COOLDOWN_HOURS;
    }

    /**
     * Assert that a status transition is valid.
     *
     * @throws InvalidArgumentException
     */
    private function assertTransition(Goal $goal, GoalStatus $target): void
    {
        $allowed = self::TRANSITIONS[$goal->status->value] ?? [];

        if (! in_array($target, $allowed, true)) {
            throw new InvalidArgumentException(
                "Cannot transition goal from '{$goal->status->value}' to '{$target->value}'."
            );
        }
    }
}
