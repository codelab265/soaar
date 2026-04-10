<?php

namespace App\Services;

use App\Enums\GoalStatus;
use App\Enums\PointTransactionType;
use App\Enums\TaskStatus;
use App\Models\Goal;
use App\Models\PointTransaction;
use InvalidArgumentException;

class GoalCompletionService
{
    public function __construct(
        public PointsService $pointsService,
        public GoalService $goalService,
    ) {}

    /**
     * Complete a goal and award points with bonuses, capped at 175.
     *
     * @param  float  $rewardMultiplier  Scales all point awards (e.g. 0.80 for auto-approval).
     * @return array{goal: Goal, transactions: list<PointTransaction>, total_points: int}
     */
    public function completeGoal(Goal $goal, bool $isPartnerVerified = false, float $rewardMultiplier = 1.0): array
    {
        if ($goal->status !== GoalStatus::Active && $goal->status !== GoalStatus::PendingVerification) {
            throw new InvalidArgumentException(
                "Cannot complete a goal with status '{$goal->status->value}'."
            );
        }

        $this->goalService->markVerifiedCompleted(
            $goal->status === GoalStatus::Active
                ? $this->goalService->submitForVerification($goal)
                : $goal
        );

        $user = $goal->user;
        $transactions = [];
        $totalAwarded = 0;
        $remaining = PointsService::MAX_POINTS_PER_GOAL;

        $basePoints = min((int) (PointsService::GOAL_COMPLETION_BASE * $rewardMultiplier), $remaining);
        if ($basePoints > 0) {
            $transaction = $this->pointsService->awardPoints(
                user: $user,
                type: PointTransactionType::GoalCompletion,
                points: $basePoints,
                description: "Completed goal: {$goal->title}",
                transactionable: $goal,
            );
            if ($transaction) {
                $transactions[] = $transaction;
                $totalAwarded += $transaction->points;
                $remaining -= $transaction->points;
            }
        }

        if ($remaining > 0 && $this->isCompletedEarly($goal)) {
            $earlyPoints = min((int) (PointsService::GOAL_EARLY_BONUS * $rewardMultiplier), $remaining);
            $transaction = $this->pointsService->awardPoints(
                user: $user,
                type: PointTransactionType::EarlyCompletion,
                points: $earlyPoints,
                description: "Early completion bonus: {$goal->title}",
                transactionable: $goal,
            );
            if ($transaction) {
                $transactions[] = $transaction;
                $totalAwarded += $transaction->points;
                $remaining -= $transaction->points;
            }
        }

        if ($remaining > 0 && $this->hasFullTaskCompletion($goal)) {
            $fullTaskPoints = min((int) (PointsService::GOAL_FULL_TASK_BONUS * $rewardMultiplier), $remaining);
            $transaction = $this->pointsService->awardPoints(
                user: $user,
                type: PointTransactionType::FullTaskCompletion,
                points: $fullTaskPoints,
                description: "100% task completion bonus: {$goal->title}",
                transactionable: $goal,
            );
            if ($transaction) {
                $transactions[] = $transaction;
                $totalAwarded += $transaction->points;
                $remaining -= $transaction->points;
            }
        }

        if ($remaining > 0 && $isPartnerVerified) {
            $partnerPoints = min((int) (PointsService::GOAL_PARTNER_VERIFICATION_BONUS * $rewardMultiplier), $remaining);
            $transaction = $this->pointsService->awardPoints(
                user: $user,
                type: PointTransactionType::PartnerVerification,
                points: $partnerPoints,
                description: "Partner verification bonus: {$goal->title}",
                transactionable: $goal,
            );
            if ($transaction) {
                $transactions[] = $transaction;
                $totalAwarded += $transaction->points;
            }
        }

        return [
            'goal' => $goal->fresh(),
            'transactions' => $transactions,
            'total_points' => $totalAwarded,
        ];
    }

    /**
     * Determine if the goal was completed before its deadline.
     */
    private function isCompletedEarly(Goal $goal): bool
    {
        return now()->lt($goal->deadline);
    }

    /**
     * Determine if all tasks under the goal are completed.
     */
    private function hasFullTaskCompletion(Goal $goal): bool
    {
        $goal->load('objectives.tasks');

        $totalTasks = 0;
        $completedTasks = 0;

        foreach ($goal->objectives as $objective) {
            foreach ($objective->tasks as $task) {
                $totalTasks++;
                if ($task->status === TaskStatus::Completed) {
                    $completedTasks++;
                }
            }
        }

        return $totalTasks > 0 && $totalTasks === $completedTasks;
    }
}
