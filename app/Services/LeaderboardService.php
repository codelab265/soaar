<?php

namespace App\Services;

use App\Enums\PointTransactionType;
use App\Models\User;
use Illuminate\Support\Collection;

class LeaderboardService
{
    /** Bonus points for top 10 leaderboard placement. */
    public const TOP_10_BONUS = 100;

    public function __construct(
        public PointsService $pointsService,
    ) {}

    /**
     * Get the leaderboard sorted by total points.
     *
     * @return Collection<int, User>
     */
    public function getLeaderboard(int $limit = 10): Collection
    {
        return User::orderByDesc('total_points')
            ->limit($limit)
            ->get();
    }

    /**
     * Award leaderboard bonus to the top 10 users.
     *
     * @return int Number of bonuses awarded
     */
    public function awardTopTenBonus(): int
    {
        $topUsers = $this->getLeaderboard(10);
        $count = 0;

        foreach ($topUsers as $user) {
            if ($user->total_points <= 0) {
                continue;
            }

            $this->pointsService->awardPoints(
                user: $user,
                type: PointTransactionType::LeaderboardReward,
                points: self::TOP_10_BONUS,
                description: 'Top 10 leaderboard bonus',
            );
            $count++;
        }

        return $count;
    }

    /**
     * Get a user's leaderboard rank.
     */
    public function getUserRank(User $user): int
    {
        return User::where('total_points', '>', $user->total_points)->count() + 1;
    }
}
