<?php

namespace App\Services;

use App\Enums\ObjectiveStatus;
use App\Enums\PointTransactionType;
use App\Models\Objective;
use App\Models\PointTransaction;
use InvalidArgumentException;

class ObjectiveCompletionService
{
    public function __construct(
        public PointsService $pointsService,
    ) {}

    /**
     * Complete an objective and award base points (+40).
     *
     * @return array{objective: Objective, transaction: ?PointTransaction, points_awarded: int}
     */
    public function completeObjective(Objective $objective): array
    {
        if ($objective->status === ObjectiveStatus::Completed || $objective->status === ObjectiveStatus::Verified) {
            throw new InvalidArgumentException('Objective is already completed or verified.');
        }

        $objective->update(['status' => ObjectiveStatus::Completed]);

        $user = $objective->goal->user;

        $transaction = $this->pointsService->awardPoints(
            user: $user,
            type: PointTransactionType::ObjectiveCompletion,
            points: PointsService::OBJECTIVE_COMPLETION_BASE,
            description: "Completed objective: {$objective->title}",
            transactionable: $objective,
        );

        return [
            'objective' => $objective->fresh(),
            'transaction' => $transaction,
            'points_awarded' => $transaction?->points ?? 0,
        ];
    }

    /**
     * Verify a completed objective and award verification bonus (+10).
     *
     * @return array{objective: Objective, transaction: ?PointTransaction, points_awarded: int}
     */
    public function verifyObjective(Objective $objective): array
    {
        if ($objective->status !== ObjectiveStatus::Completed) {
            throw new InvalidArgumentException('Only completed objectives can be verified.');
        }

        $objective->update(['status' => ObjectiveStatus::Verified]);

        $user = $objective->goal->user;

        $transaction = $this->pointsService->awardPoints(
            user: $user,
            type: PointTransactionType::ObjectiveCompletion,
            points: PointsService::OBJECTIVE_VERIFICATION_BONUS,
            description: "Verified objective: {$objective->title}",
            transactionable: $objective,
            metadata: ['verified' => true],
        );

        return [
            'objective' => $objective->fresh(),
            'transaction' => $transaction,
            'points_awarded' => $transaction?->points ?? 0,
        ];
    }
}
