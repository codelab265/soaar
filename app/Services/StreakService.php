<?php

namespace App\Services;

use App\Enums\StreakType;
use App\Models\Streak;
use App\Models\User;

class StreakService
{
    public function __construct(
        public PointsService $pointsService,
    ) {}

    /**
     * Record daily activity for a user's streak.
     * Only increments once per day; awards milestone bonuses.
     */
    public function recordActivity(User $user, StreakType $type = StreakType::Daily): Streak
    {
        $streak = $this->getOrCreateStreak($user, $type);

        if ($streak->last_activity_date?->isToday()) {
            return $streak;
        }

        $isConsecutive = $streak->last_activity_date?->isYesterday() ?? false;

        if ($isConsecutive) {
            $streak->increment('current_count');
        } else {
            $streak->update([
                'current_count' => 1,
                'started_at' => now()->toDateString(),
            ]);
        }

        $streak->update(['last_activity_date' => now()->toDateString()]);
        $streak->refresh();

        if ($streak->current_count > $streak->longest_count) {
            $streak->update(['longest_count' => $streak->current_count]);
        }

        $this->syncUserStreakFields($user, $streak);

        $this->pointsService->awardStreakMilestone($user, $streak->current_count, $streak);

        return $streak->fresh();
    }

    /**
     * Break a user's streak and apply the penalty.
     */
    public function breakStreak(User $user, StreakType $type = StreakType::Daily): Streak
    {
        $streak = $this->getOrCreateStreak($user, $type);

        if ($streak->current_count === 0) {
            return $streak;
        }

        $streak->update([
            'current_count' => 0,
            'started_at' => null,
        ]);

        $this->syncUserStreakFields($user, $streak);

        $this->pointsService->applyStreakBrokenPenalty($user, $streak);

        return $streak->fresh();
    }

    /**
     * Get or create a streak record for the user and type.
     */
    private function getOrCreateStreak(User $user, StreakType $type): Streak
    {
        return Streak::firstOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            ['current_count' => 0, 'longest_count' => 0],
        );
    }

    /**
     * Sync the User model's streak fields from the Streak model (daily type only).
     */
    private function syncUserStreakFields(User $user, Streak $streak): void
    {
        if ($streak->type !== StreakType::Daily) {
            return;
        }

        $user->update([
            'current_streak' => $streak->current_count,
            'longest_streak' => $streak->longest_count,
        ]);
    }
}
