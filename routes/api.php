<?php

use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChallengeController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\GoalController;
use App\Http\Controllers\Api\V1\GoalVerificationController;
use App\Http\Controllers\Api\V1\LeaderboardController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ObjectiveController;
use App\Http\Controllers\Api\V1\PartnerRequestController;
use App\Http\Controllers\Api\V1\PayChanguChargeController;
use App\Http\Controllers\Api\V1\PayChanguCourseChargeController;
use App\Http\Controllers\Api\V1\PayChanguMetaController;
use App\Http\Controllers\Api\V1\PayChanguSubscriptionChargeController;
use App\Http\Controllers\Api\V1\PointTransactionController;
use App\Http\Controllers\Api\V1\StreakController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Webhooks\PayChanguWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('paychangu/webhook', PayChanguWebhookController::class);

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        // User
        Route::put('users/me', [UserController::class, 'updateMe']);
        Route::post('users/me/profile-picture', [UserController::class, 'updateProfilePicture']);
        Route::post('users/me/push-token', [UserController::class, 'updatePushToken']);
        Route::get('users/search', [UserController::class, 'search']);

        // Goals
        Route::apiResource('goals', GoalController::class);
        Route::post('goals/{goal}/cancel', [GoalController::class, 'cancel']);
        Route::post('goals/{goal}/submit-verification', [GoalController::class, 'submitVerification']);

        // Objectives
        Route::get('goals/{goal}/objectives', [ObjectiveController::class, 'index']);
        Route::post('goals/{goal}/objectives', [ObjectiveController::class, 'store']);
        Route::put('objectives/{objective}', [ObjectiveController::class, 'update']);
        Route::delete('objectives/{objective}', [ObjectiveController::class, 'destroy']);
        Route::post('objectives/{objective}/complete', [ObjectiveController::class, 'complete']);

        // Tasks
        Route::get('objectives/{objective}/tasks', [TaskController::class, 'index']);
        Route::get('tasks/today', [TaskController::class, 'today']);
        Route::post('tasks', [TaskController::class, 'storeIndependent']);
        Route::post('objectives/{objective}/tasks', [TaskController::class, 'store']);
        Route::put('tasks/{task}', [TaskController::class, 'update']);
        Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
        Route::post('tasks/{task}/complete', [TaskController::class, 'complete']);

        // Points & Streaks
        Route::get('points', [PointTransactionController::class, 'index']);
        Route::get('points/summary', [PointTransactionController::class, 'summary']);
        Route::get('streaks', [StreakController::class, 'index']);

        // Accountability Partners
        Route::post('goals/{goal}/partner-requests', [PartnerRequestController::class, 'store']);
        Route::get('partner-requests', [PartnerRequestController::class, 'incoming']);
        Route::get('partner-requests/outgoing', [PartnerRequestController::class, 'outgoing']);
        Route::post('partner-requests/{partnerRequest}/accept', [PartnerRequestController::class, 'accept']);
        Route::post('partner-requests/{partnerRequest}/decline', [PartnerRequestController::class, 'decline']);

        // Goal Verification
        Route::post('goals/{goal}/approve', [GoalVerificationController::class, 'approve']);
        Route::post('goals/{goal}/reject', [GoalVerificationController::class, 'reject']);
        Route::post('goals/{goal}/request-proof', [GoalVerificationController::class, 'requestProof']);
        Route::post('goals/{goal}/submit-proof', [GoalVerificationController::class, 'submitProof']);

        // Challenges
        Route::get('challenges', [ChallengeController::class, 'index']);
        Route::get('challenges/{challenge}', [ChallengeController::class, 'show']);
        Route::post('challenges/{challenge}/join', [ChallengeController::class, 'join']);
        Route::post('challenges/{challenge}/complete', [ChallengeController::class, 'complete']);
        Route::get('challenges/{challenge}/progress', [ChallengeController::class, 'progress']);

        // Leaderboard
        Route::middleware('premium')->group(function () {
            Route::get('leaderboard', [LeaderboardController::class, 'index']);
            Route::get('leaderboard/me', [LeaderboardController::class, 'me']);
        });

        // Courses
        Route::get('courses', [CourseController::class, 'index']);
        Route::get('courses/{course}', [CourseController::class, 'show']);
        Route::post('courses/{course}/enroll', [CourseController::class, 'enroll']);
        Route::post('courses/{course}/paychangu/mobile-money', [PayChanguCourseChargeController::class, 'initializeMobileMoney']);
        Route::post('courses/{course}/paychangu/bank-transfer', [PayChanguCourseChargeController::class, 'initializeBankTransfer']);

        // PayChangu
        Route::get('paychangu/mobile-money/operators', [PayChanguMetaController::class, 'mobileMoneyOperators']);
        Route::get('paychangu/charges/{chargeId}', [PayChanguChargeController::class, 'show']);

        // Subscriptions
        Route::get('subscription', [SubscriptionController::class, 'show']);
        Route::get('subscriptions', [SubscriptionController::class, 'history']);
        Route::post('subscription', [SubscriptionController::class, 'store']);
        Route::post('subscription/cancel', [SubscriptionController::class, 'cancel']);
        Route::post('subscription/renew', [SubscriptionController::class, 'renew']);
        Route::post('subscription/paychangu/mobile-money', [PayChanguSubscriptionChargeController::class, 'initializeMobileMoney']);
        Route::post('subscription/paychangu/bank-transfer', [PayChanguSubscriptionChargeController::class, 'initializeBankTransfer']);

        // Analytics
        Route::get('analytics/summary', [AnalyticsController::class, 'summary']);
        Route::middleware('premium')->group(function () {
            Route::get('analytics/completion-rate', [AnalyticsController::class, 'completionRate']);
            Route::get('analytics/weekly-consistency', [AnalyticsController::class, 'weeklyConsistency']);
            Route::get('analytics/points-history', [AnalyticsController::class, 'pointsHistory']);
            Route::get('analytics/discipline-trend', [AnalyticsController::class, 'disciplineTrend']);
        });

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('notifications/clear-all', [NotificationController::class, 'clearAll']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    });
});
