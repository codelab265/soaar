<?php

namespace App\Services;

use App\Enums\PointTransactionType;
use App\Models\PointTransaction;
use App\Models\User;
use App\Notifications\PointsChangedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PointsService
{
    /** Maximum points a user can earn from tasks per day. */
    public const DAILY_TASK_CAP = 60;

    /** Points deducted for a missed task. */
    public const MISSED_TASK_PENALTY = -5;

    /** Points deducted when a streak is broken. */
    public const STREAK_BROKEN_PENALTY = -25;

    /** Points deducted when a goal expires. */
    public const GOAL_EXPIRED_PENALTY = -75;

    /** Points deducted when a deadline is missed. */
    public const MISSED_DEADLINE_PENALTY = -50;

    /** Points deducted on partner rejection. */
    public const PARTNER_REJECTION_PENALTY = -15;

    /** Base points for objective completion. */
    public const OBJECTIVE_COMPLETION_BASE = 40;

    /** Bonus for verified objective. */
    public const OBJECTIVE_VERIFICATION_BONUS = 10;

    /** Base points for goal completion. */
    public const GOAL_COMPLETION_BASE = 100;

    /** Bonus for early goal completion. */
    public const GOAL_EARLY_BONUS = 30;

    /** Bonus for 100% task completion on a goal. */
    public const GOAL_FULL_TASK_BONUS = 25;

    /** Bonus for partner-verified goal. */
    public const GOAL_PARTNER_VERIFICATION_BONUS = 20;

    /** Maximum points awardable per goal. */
    public const MAX_POINTS_PER_GOAL = 175;

    /**
     * Streak milestone thresholds and their rewards.
     *
     * @var array<int, int>
     */
    public const STREAK_MILESTONES = [
        7 => 15,
        14 => 30,
        30 => 75,
        60 => 150,
        100 => 300,
    ];

    /**
     * Get the total task points earned by a user today.
     */
    public function dailyTaskPointsEarned(User $user, ?Carbon $date = null): int
    {
        $date ??= now();

        return (int) $user->pointTransactions()
            ->where('type', PointTransactionType::TaskCompletion)
            ->whereDate('created_at', $date)
            ->where('points', '>', 0)
            ->sum('points');
    }

    /**
     * Calculate remaining task points available for a user today.
     */
    public function remainingDailyTaskPoints(User $user, ?Carbon $date = null): int
    {
        return max(0, self::DAILY_TASK_CAP - $this->dailyTaskPointsEarned($user, $date));
    }

    /**
     * Award points to a user, respecting the daily cap for task completions.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function awardPoints(
        User $user,
        PointTransactionType $type,
        int $points,
        string $description,
        ?Model $transactionable = null,
        array $metadata = [],
    ): ?PointTransaction {
        if ($points <= 0) {
            return null;
        }

        // Apply daily cap for task completions only
        if ($type === PointTransactionType::TaskCompletion) {
            $remaining = $this->remainingDailyTaskPoints($user);

            if ($remaining <= 0) {
                return null;
            }

            $points = min($points, $remaining);
        }

        return $this->createTransaction($user, $type, $points, $description, $transactionable, $metadata);
    }

    /**
     * Deduct points from a user (penalties are not capped).
     *
     * @param  array<string, mixed>  $metadata
     */
    public function deductPoints(
        User $user,
        PointTransactionType $type,
        int $points,
        string $description,
        ?Model $transactionable = null,
        array $metadata = [],
    ): PointTransaction {
        $absolutePoints = abs($points);

        return $this->createTransaction($user, $type, -$absolutePoints, $description, $transactionable, $metadata);
    }

    /**
     * Apply the goal expired penalty (-75).
     */
    public function applyGoalExpiredPenalty(User $user, ?Model $transactionable = null): PointTransaction
    {
        return $this->deductPoints(
            user: $user,
            type: PointTransactionType::GoalExpired,
            points: self::GOAL_EXPIRED_PENALTY,
            description: 'Goal expired penalty',
            transactionable: $transactionable,
        );
    }

    /**
     * Apply the missed deadline penalty (-50).
     */
    public function applyMissedDeadlinePenalty(User $user, ?Model $transactionable = null): PointTransaction
    {
        return $this->deductPoints(
            user: $user,
            type: PointTransactionType::MissedDeadline,
            points: self::MISSED_DEADLINE_PENALTY,
            description: 'Missed deadline penalty',
            transactionable: $transactionable,
        );
    }

    /**
     * Apply the partner rejection penalty (-15).
     */
    public function applyPartnerRejectionPenalty(User $user, ?Model $transactionable = null): PointTransaction
    {
        return $this->deductPoints(
            user: $user,
            type: PointTransactionType::PartnerRejection,
            points: self::PARTNER_REJECTION_PENALTY,
            description: 'Partner rejection penalty',
            transactionable: $transactionable,
        );
    }

    /**
     * Apply the streak broken penalty (-25).
     */
    public function applyStreakBrokenPenalty(User $user, ?Model $transactionable = null): PointTransaction
    {
        return $this->deductPoints(
            user: $user,
            type: PointTransactionType::StreakBroken,
            points: self::STREAK_BROKEN_PENALTY,
            description: 'Streak broken penalty',
            transactionable: $transactionable,
        );
    }

    /**
     * Award streak milestone bonus if the streak count matches a milestone.
     */
    public function awardStreakMilestone(User $user, int $streakDays, ?Model $transactionable = null): ?PointTransaction
    {
        if (! isset(self::STREAK_MILESTONES[$streakDays])) {
            return null;
        }

        $reward = self::STREAK_MILESTONES[$streakDays];

        return $this->awardPoints(
            user: $user,
            type: PointTransactionType::StreakBonus,
            points: $reward,
            description: "{$streakDays}-day streak milestone bonus",
            transactionable: $transactionable,
            metadata: ['milestone_days' => $streakDays],
        );
    }

    /**
     * Create a point transaction and update the user's total.
     *
     * @param  array<string, mixed>  $metadata
     */
    private function createTransaction(
        User $user,
        PointTransactionType $type,
        int $points,
        string $description,
        ?Model $transactionable = null,
        array $metadata = [],
    ): PointTransaction {
        /** @var PointTransaction $transaction */
        $transaction = $user->pointTransactions()->create([
            'type' => $type,
            'points' => $points,
            'description' => $description,
            'transactionable_type' => $transactionable?->getMorphClass(),
            'transactionable_id' => $transactionable?->getKey(),
            'metadata' => $metadata ?: null,
        ]);

        $user->increment('total_points', $points);

        $user->notify(new PointsChangedNotification($points, $description));

        return $transaction;
    }
}
