<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Support\Collection;

class AnalyticsService
{
    /**
     * Calculate the task completion rate for a user.
     */
    public function completionRate(User $user): float
    {
        $total = $this->totalTaskCount($user);

        if ($total === 0) {
            return 0;
        }

        $completed = $this->completedTaskCount($user);

        return round(($completed / $total) * 100, 2);
    }

    /**
     * Calculate weekly consistency (days with at least one task completed in the last 7 days).
     */
    public function weeklyConsistency(User $user): int
    {
        $weekAgo = now()->subDays(7);

        return (int) $user->goals()
            ->with(['objectives.tasks' => function ($query) use ($weekAgo) {
                $query->where('status', TaskStatus::Completed)
                    ->where('completed_at', '>=', $weekAgo);
            }])
            ->get()
            ->flatMap(fn ($goal) => $goal->objectives->flatMap(fn ($obj) => $obj->tasks))
            ->groupBy(fn ($task) => $task->completed_at->toDateString())
            ->count();
    }

    /**
     * Get points history for the last N days.
     *
     * @return Collection<int, array{date: string, points: int}>
     */
    public function pointsHistory(User $user, int $days = 30): Collection
    {
        $startDate = now()->subDays($days)->startOfDay();

        $transactions = $user->pointTransactions()
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get();

        return $transactions->groupBy(fn (PointTransaction $t) => $t->created_at->toDateString())
            ->map(fn (Collection $dayTransactions, string $date) => [
                'date' => $date,
                'points' => $dayTransactions->sum('points'),
            ])
            ->values();
    }

    /**
     * Get the discipline score trend over the last N weeks.
     *
     * @return array{current: float, trend: string}
     */
    public function disciplineTrend(User $user): array
    {
        $current = (float) $user->discipline_score;

        return [
            'current' => $current,
            'trend' => match (true) {
                $current >= 70 => 'excellent',
                $current >= 50 => 'good',
                $current >= 30 => 'needs_improvement',
                default => 'critical',
            },
        ];
    }

    /**
     * Get a summary of user analytics.
     *
     * @return array{completion_rate: float, weekly_consistency: int, current_streak: int, longest_streak: int, total_points: int, discipline_score: float, discipline_trend: string}
     */
    public function summary(User $user): array
    {
        $trend = $this->disciplineTrend($user);

        return [
            'completion_rate' => $this->completionRate($user),
            'weekly_consistency' => $this->weeklyConsistency($user),
            'current_streak' => $user->current_streak,
            'longest_streak' => $user->longest_streak,
            'total_points' => $user->total_points,
            'discipline_score' => $trend['current'],
            'discipline_trend' => $trend['trend'],
        ];
    }

    private function totalTaskCount(User $user): int
    {
        return $user->goals()
            ->with('objectives.tasks')
            ->get()
            ->flatMap(fn ($goal) => $goal->objectives->flatMap(fn ($obj) => $obj->tasks))
            ->count();
    }

    private function completedTaskCount(User $user): int
    {
        return $user->goals()
            ->with(['objectives.tasks' => fn ($q) => $q->where('status', TaskStatus::Completed)])
            ->get()
            ->flatMap(fn ($goal) => $goal->objectives->flatMap(fn ($obj) => $obj->tasks))
            ->count();
    }
}
