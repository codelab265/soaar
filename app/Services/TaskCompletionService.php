<?php

namespace App\Services;

use App\Enums\PointTransactionType;
use App\Enums\StreakType;
use App\Enums\TaskStatus;
use App\Models\PointTransaction;
use App\Models\Task;
use App\Models\User;
use InvalidArgumentException;

class TaskCompletionService
{
    /** Minimum duration in minutes for a task to earn points. */
    public const MINIMUM_DURATION_FOR_POINTS = 5;

    /** Number of repetitions after which decay begins. */
    public const DECAY_THRESHOLD = 10;

    /** Decay multiplier applied per repetition beyond threshold. */
    public const DECAY_RATE = 0.05;

    /** Minimum decay multiplier (floor). */
    public const MINIMUM_DECAY = 0.10;

    public function __construct(
        public PointsService $pointsService,
        public StreakService $streakService,
    ) {}

    /**
     * Complete a task and award points if eligible.
     *
     * @param  int  $durationMinutes  Actual time spent on the task in minutes.
     * @return array{task: Task, transaction: ?PointTransaction, points_awarded: int, reason: string}
     */
    public function completeTask(Task $task, int $durationMinutes): array
    {
        if ($task->status === TaskStatus::Completed) {
            throw new InvalidArgumentException('Task is already completed.');
        }

        $task->update([
            'status' => TaskStatus::Completed,
            'completed_at' => now(),
        ]);

        // Anti-gaming: tasks under 5 minutes earn no points
        if ($durationMinutes < self::MINIMUM_DURATION_FOR_POINTS) {
            return [
                'task' => $task->fresh(),
                'transaction' => null,
                'points_awarded' => 0,
                'reason' => 'Task completed too quickly (under 5 minutes). No points awarded.',
            ];
        }

        // Update repetition count and decay
        $this->updateRepetitionDecay($task);

        $user = $this->resolveUser($task);
        $effectivePoints = $task->effectivePoints();

        $transaction = $this->pointsService->awardPoints(
            user: $user,
            type: PointTransactionType::TaskCompletion,
            points: $effectivePoints,
            description: "Completed task: {$task->title}",
            transactionable: $task,
            metadata: [
                'difficulty' => $task->difficulty->value,
                'base_points' => $task->points_value,
                'decay_multiplier' => $task->repetition_decay,
                'duration_minutes' => $durationMinutes,
            ],
        );

        $this->streakService->recordActivity($user, StreakType::Daily);

        return [
            'task' => $task->fresh(),
            'transaction' => $transaction,
            'points_awarded' => $transaction?->points ?? 0,
            'reason' => $transaction
                ? "Awarded {$transaction->points} points."
                : 'Daily point cap reached. No additional points awarded.',
        ];
    }

    /**
     * Mark a task as missed and apply the penalty.
     */
    public function missTask(Task $task): PointTransaction
    {
        $task->update(['status' => TaskStatus::Missed]);

        $user = $this->resolveUser($task);

        $transaction = $this->pointsService->deductPoints(
            user: $user,
            type: PointTransactionType::MissedTask,
            points: PointsService::MISSED_TASK_PENALTY,
            description: "Missed task: {$task->title}",
            transactionable: $task,
        );

        $this->streakService->breakStreak($user, StreakType::Daily);

        return $transaction;
    }

    /**
     * Increment the repetition count and recalculate decay.
     */
    private function updateRepetitionDecay(Task $task): void
    {
        $newCount = $task->repetition_count + 1;
        $decay = $this->calculateDecay($newCount);

        $task->update([
            'repetition_count' => $newCount,
            'repetition_decay' => $decay,
        ]);
    }

    /**
     * Calculate the repetition decay multiplier.
     *
     * After DECAY_THRESHOLD repetitions, each subsequent repetition
     * reduces the multiplier by DECAY_RATE, down to MINIMUM_DECAY.
     */
    public function calculateDecay(int $repetitionCount): float
    {
        if ($repetitionCount <= self::DECAY_THRESHOLD) {
            return 1.00;
        }

        $excessRepetitions = $repetitionCount - self::DECAY_THRESHOLD;
        $decay = 1.00 - $excessRepetitions * self::DECAY_RATE;

        return max(self::MINIMUM_DECAY, round($decay, 2));
    }

    /**
     * Resolve the user who owns the task.
     */
    private function resolveUser(Task $task): User
    {
        return $task->user;
    }
}
