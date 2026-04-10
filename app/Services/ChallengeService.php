<?php

namespace App\Services;

use App\Enums\ChallengeStatus;
use App\Enums\PointTransactionType;
use App\Models\Challenge;
use App\Models\ChallengeUser;
use App\Models\User;
use InvalidArgumentException;

class ChallengeService
{
    public function __construct(
        public PointsService $pointsService,
    ) {}

    /**
     * Join a user to a challenge.
     */
    public function joinChallenge(User $user, Challenge $challenge): ChallengeUser
    {
        if ($challenge->status !== ChallengeStatus::Active) {
            throw new InvalidArgumentException('Only active challenges can be joined.');
        }

        $existing = ChallengeUser::where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->first();

        if ($existing) {
            throw new InvalidArgumentException('User has already joined this challenge.');
        }

        return ChallengeUser::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'joined_at' => now(),
            'status' => ChallengeStatus::Active->value,
        ]);
    }

    /**
     * Complete a challenge for a user and award reward points.
     */
    public function completeChallenge(User $user, Challenge $challenge): ChallengeUser
    {
        $pivot = ChallengeUser::where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->firstOrFail();

        if ($pivot->status === ChallengeStatus::Completed) {
            throw new InvalidArgumentException('User has already completed this challenge.');
        }

        $pivot->update([
            'status' => ChallengeStatus::Completed->value,
            'completed_at' => now(),
        ]);

        $this->pointsService->awardPoints(
            user: $user,
            type: PointTransactionType::ChallengeReward,
            points: $challenge->reward_points,
            description: "Completed challenge: {$challenge->title}",
            transactionable: $challenge,
        );

        return $pivot->fresh();
    }

    /**
     * Check a user's progress in a challenge.
     *
     * @return array{joined: bool, status: ?string, days_elapsed: int, days_remaining: int}
     */
    public function checkProgress(User $user, Challenge $challenge): array
    {
        $pivot = ChallengeUser::where('user_id', $user->id)
            ->where('challenge_id', $challenge->id)
            ->first();

        if (! $pivot) {
            return [
                'joined' => false,
                'status' => null,
                'days_elapsed' => 0,
                'days_remaining' => $challenge->duration_days,
            ];
        }

        $daysElapsed = (int) $pivot->joined_at->diffInDays(now());

        return [
            'joined' => true,
            'status' => $pivot->status->value,
            'days_elapsed' => $daysElapsed,
            'days_remaining' => max(0, $challenge->duration_days - $daysElapsed),
        ];
    }
}
