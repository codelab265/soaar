<?php

namespace App\Services;

use App\Enums\GoalStatus;
use App\Enums\PointTransactionType;
use App\Enums\TaskStatus;
use App\Models\User;

class DisciplineScoreService
{
    /**
     * Formula weights (must sum to 1.0).
     * 40% Completion rate, 30% Streak strength, 20% Partner verification success, 10% Penalty ratio.
     */
    public const WEIGHT_COMPLETION = 0.40;

    public const WEIGHT_STREAK = 0.30;

    public const WEIGHT_VERIFICATION = 0.20;

    public const WEIGHT_PENALTY = 0.10;

    /** Maximum streak days used to normalize streak score. */
    public const MAX_STREAK_NORMALIZATION = 100;

    /**
     * Calculate the discipline score for a user (0–100).
     */
    public function calculate(User $user): float
    {
        $completionRate = $this->calculateCompletionRate($user);
        $streakStrength = $this->calculateStreakStrength($user);
        $verificationSuccess = $this->calculateVerificationSuccess($user);
        $penaltyRatio = $this->calculatePenaltyRatio($user);

        $score = ($completionRate * self::WEIGHT_COMPLETION)
            + ($streakStrength * self::WEIGHT_STREAK)
            + ($verificationSuccess * self::WEIGHT_VERIFICATION)
            + ($penaltyRatio * self::WEIGHT_PENALTY);

        return round(min(100, max(0, $score)), 2);
    }

    /**
     * Update the discipline score for a user.
     */
    public function updateScore(User $user): User
    {
        $score = $this->calculate($user);

        $user->update(['discipline_score' => $score]);

        return $user->fresh();
    }

    /**
     * Task/Goal completion rate as percentage (0–100).
     */
    private function calculateCompletionRate(User $user): float
    {
        $totalTasks = $user->goals()
            ->with('objectives.tasks')
            ->get()
            ->flatMap(fn ($goal) => $goal->objectives->flatMap(fn ($obj) => $obj->tasks))
            ->count();

        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $user->goals()
            ->with(['objectives.tasks' => fn ($q) => $q->where('status', TaskStatus::Completed)])
            ->get()
            ->flatMap(fn ($goal) => $goal->objectives->flatMap(fn ($obj) => $obj->tasks))
            ->count();

        return ($completedTasks / $totalTasks) * 100;
    }

    /**
     * Streak strength based on longest streak, normalized to 100.
     */
    private function calculateStreakStrength(User $user): float
    {
        $longest = $user->longest_streak;

        return min(100, ($longest / self::MAX_STREAK_NORMALIZATION) * 100);
    }

    /**
     * Partner verification success rate as percentage (0–100).
     */
    private function calculateVerificationSuccess(User $user): float
    {
        $goalsWithPartner = $user->goals()
            ->whereNotNull('accountability_partner_id')
            ->whereIn('status', [GoalStatus::VerifiedCompleted, GoalStatus::Expired, GoalStatus::Cancelled])
            ->count();

        if ($goalsWithPartner === 0) {
            return 100;
        }

        $verifiedGoals = $user->goals()
            ->whereNotNull('accountability_partner_id')
            ->where('status', GoalStatus::VerifiedCompleted)
            ->count();

        return ($verifiedGoals / $goalsWithPartner) * 100;
    }

    /**
     * Inverse penalty ratio (fewer penalties = higher score, 0–100).
     */
    private function calculatePenaltyRatio(User $user): float
    {
        $totalTransactions = $user->pointTransactions()->count();

        if ($totalTransactions === 0) {
            return 100;
        }

        $penaltyTypes = [
            PointTransactionType::MissedTask,
            PointTransactionType::GoalExpired,
            PointTransactionType::MissedDeadline,
            PointTransactionType::PartnerRejection,
            PointTransactionType::StreakBroken,
        ];

        $penaltyCount = $user->pointTransactions()
            ->whereIn('type', $penaltyTypes)
            ->count();

        $penaltyRate = $penaltyCount / $totalTransactions;

        return max(0, (1 - $penaltyRate) * 100);
    }
}
