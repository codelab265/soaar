<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ChallengeController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\GlobalNotificationController;
use App\Http\Controllers\Admin\GoalController;
use App\Http\Controllers\Admin\LeaderboardController;
use App\Http\Controllers\Admin\ObjectiveController;
use App\Http\Controllers\Admin\PartnerRequestController;
use App\Http\Controllers\Admin\PointTransactionController;
use App\Http\Controllers\Admin\StreakController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TaskController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->to(route('dashboard', absolute: false));
    }

    return redirect()->to(route('login', absolute: false));
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return redirect()->to(route('dashboard', absolute: false));
        })->name('index');

        Route::get('leaderboard', LeaderboardController::class)->name('leaderboard');
        Route::get('analytics', AnalyticsController::class)->name('analytics');

        Route::get('notifications/global', [GlobalNotificationController::class, 'create'])->name('notifications.global.create');
        Route::post('notifications/global', [GlobalNotificationController::class, 'store'])->name('notifications.global.store');

        // CRUD sections (to be implemented next)
        Route::get('partner-requests', [PartnerRequestController::class, 'index'])->name('partner-requests');
        Route::post('partner-requests/{partnerRequest}/accept', [PartnerRequestController::class, 'accept'])->name('partner-requests.accept');
        Route::post('partner-requests/{partnerRequest}/decline', [PartnerRequestController::class, 'decline'])->name('partner-requests.decline');
        Route::get('goals', [GoalController::class, 'index'])->name('goals');
        Route::post('goals/{goal}/approve', [GoalController::class, 'approve'])->name('goals.approve');
        Route::post('goals/{goal}/reject', [GoalController::class, 'reject'])->name('goals.reject');
        Route::get('objectives', [ObjectiveController::class, 'index'])->name('objectives');
        Route::post('objectives/{objective}/verify', [ObjectiveController::class, 'verify'])->name('objectives.verify');
        Route::get('tasks', [TaskController::class, 'index'])->name('tasks');
        Route::post('tasks/{task}/miss', [TaskController::class, 'miss'])->name('tasks.miss');
        Route::get('challenges/create', [ChallengeController::class, 'create'])->name('challenges.create');
        Route::post('challenges', [ChallengeController::class, 'store'])->name('challenges.store');
        Route::get('challenges/{challenge}/edit', [ChallengeController::class, 'edit'])->name('challenges.edit');
        Route::put('challenges/{challenge}', [ChallengeController::class, 'update'])->name('challenges.update');
        Route::post('challenges/{challenge}/status', [ChallengeController::class, 'updateStatus'])->name('challenges.status');
        Route::delete('challenges/{challenge}', [ChallengeController::class, 'destroy'])->name('challenges.destroy');
        Route::get('challenges', [ChallengeController::class, 'index'])->name('challenges');
        Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::post('courses/{course}/active', [CourseController::class, 'setActive'])->name('courses.active');
        Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::get('courses', [CourseController::class, 'index'])->name('courses');
        Route::get('point-transactions', [PointTransactionController::class, 'index'])->name('point-transactions');
        Route::get('streaks', [StreakController::class, 'index'])->name('streaks');
        Route::post('streaks/{streak}/break', [StreakController::class, 'break'])->name('streaks.break');
        Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions');
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');
        Route::get('users', [UserController::class, 'index'])->name('users');
        Route::post('users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/unsuspend', [UserController::class, 'unsuspend'])->name('users.unsuspend');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
