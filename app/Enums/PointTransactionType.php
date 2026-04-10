<?php

namespace App\Enums;

enum PointTransactionType: string
{
    case TaskCompletion = 'task_completion';
    case ObjectiveCompletion = 'objective_completion';
    case GoalCompletion = 'goal_completion';
    case EarlyCompletion = 'early_completion';
    case FullTaskCompletion = 'full_task_completion';
    case PartnerVerification = 'partner_verification';
    case StreakBonus = 'streak_bonus';
    case ChallengeReward = 'challenge_reward';
    case LeaderboardReward = 'leaderboard_reward';
    case MissedTask = 'missed_task';
    case GoalExpired = 'goal_expired';
    case MissedDeadline = 'missed_deadline';
    case PartnerRejection = 'partner_rejection';
    case StreakBroken = 'streak_broken';
}
