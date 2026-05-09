<?php

namespace App\Http\Controllers;

use App\Enums\GoalStatus;
use App\Enums\StreakType;
use App\Enums\TaskStatus;
use App\Models\Goal;
use App\Models\Streak;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $days = (int) $request->input('days', 7);
        $days = in_array($days, [7, 14, 30]) ? $days : 7;

        $today = Carbon::today();
        $startOfRange = (clone $today)->subDays($days - 1)->startOfDay();
        $startOf7Days = (clone $today)->subDays(6)->startOfDay();
        $startOfWeek = Carbon::now()->startOfWeek();

        $goalsQuery = Goal::query()->where('user_id', $user->id);

        $tasksQuery = Task::query()->where('user_id', $user->id);

        $activeGoalCount = (clone $goalsQuery)
            ->where('status', GoalStatus::Active)
            ->count();

        $tasksDueTodayCount = (clone $tasksQuery)
            ->whereDate('scheduled_date', $today)
            ->whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])
            ->count();

        $pendingTaskCount = (clone $tasksQuery)
            ->whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])
            ->count();

        $completedThisWeekCount = (clone $tasksQuery)
            ->where('status', TaskStatus::Completed)
            ->where('completed_at', '>=', $startOfWeek)
            ->count();

        $tasksByDay = (clone $tasksQuery)
            ->where('status', TaskStatus::Completed)
            ->whereBetween('completed_at', [$startOfRange, (clone $today)->endOfDay()])
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as completed_tasks, COALESCE(SUM(points_value), 0) as points_earned')
            ->groupByRaw('DATE(completed_at)')
            ->get()
            ->keyBy('date');

        $activityDays = collect(range(0, $days - 1))
            ->map(function (int $offset) use ($today, $days, $tasksByDay): array {
                $date = (clone $today)->subDays($days - 1 - $offset)->toDateString();
                $row = $tasksByDay->get($date);

                return [
                    'date' => $date,
                    'completedTasks' => $row ? (int) $row->completed_tasks : 0,
                    'pointsEarned' => $row ? (int) $row->points_earned : 0,
                ];
            })
            ->all();

        $adminActivityDays = $user->is_admin ? (function () use ($today, $startOfRange, $days): array {
            $platformTasksByDay = Task::query()
                ->where('status', TaskStatus::Completed)
                ->whereBetween('completed_at', [$startOfRange, (clone $today)->endOfDay()])
                ->selectRaw('DATE(completed_at) as date, COUNT(*) as completed_tasks, COALESCE(SUM(points_value), 0) as points_earned')
                ->groupByRaw('DATE(completed_at)')
                ->get()
                ->keyBy('date');

            $newUsersByDay = User::query()
                ->whereBetween('created_at', [$startOfRange, (clone $today)->endOfDay()])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as new_users')
                ->groupByRaw('DATE(created_at)')
                ->pluck('new_users', 'date')
                ->all();

            return collect(range(0, $days - 1))
                ->map(function (int $offset) use ($today, $days, $platformTasksByDay, $newUsersByDay): array {
                    $date = (clone $today)->subDays($days - 1 - $offset)->toDateString();
                    $row = $platformTasksByDay->get($date);

                    return [
                        'date' => $date,
                        'completedTasks' => $row ? (int) $row->completed_tasks : 0,
                        'pointsEarned' => $row ? (int) $row->points_earned : 0,
                        'newUsers' => (int) ($newUsersByDay[$date] ?? 0),
                    ];
                })
                ->all();
        })() : null;

        $recentTasks = (clone $tasksQuery)
            ->with(['objective:id,goal_id,title', 'goal:id,title'])
            ->orderByDesc('scheduled_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get(['id', 'objective_id', 'title', 'status', 'scheduled_date', 'points_value', 'completed_at'])
            ->map(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status->value,
                'scheduled_date' => $task->scheduled_date?->toDateString(),
                'completed_at' => $task->completed_at?->toIso8601String(),
                'points_value' => $task->points_value,
                'objective' => $task->objective ? [
                    'title' => $task->objective->title,
                    'goal' => ($task->goal ?? $task->objective->goal) ? [
                        'title' => ($task->goal ?? $task->objective->goal)?->title,
                    ] : null,
                ] : null,
            ]);

        $dailyStreak = Streak::query()
            ->where('user_id', $user->id)
            ->where('type', StreakType::Daily)
            ->first();

        return Inertia::render('dashboard', [
            'isAdmin' => (bool) $user->is_admin,
            'stats' => [
                'totalPoints' => $user->total_points,
                'disciplineScore' => $user->discipline_score,
                'currentStreak' => $user->current_streak,
                'longestStreak' => $user->longest_streak,
                'activeGoals' => $activeGoalCount,
                'tasksDueToday' => $tasksDueTodayCount,
                'pendingTasks' => $pendingTaskCount,
                'completedTasksThisWeek' => $completedThisWeekCount,
                'dailyStreakCurrent' => $dailyStreak?->current_count,
                'dailyStreakLongest' => $dailyStreak?->longest_count,
                'dailyStreakLastActivityDate' => $dailyStreak?->last_activity_date?->toDateString(),
            ],
            'filters' => ['days' => $days],
            'activity' => [
                'activityDays' => $activityDays,
                'adminActivityDays' => $adminActivityDays,
            ],
            'recentTasks' => $recentTasks,
            'adminStats' => $user->is_admin ? [
                'totalUsers' => User::query()->count(),
                'activeUsers' => User::query()->whereNull('suspended_at')->count(),
                'suspendedUsers' => User::query()->whereNotNull('suspended_at')->count(),
                'activeGoals' => Goal::query()->where('status', GoalStatus::Active)->count(),
                'pendingVerificationGoals' => Goal::query()->where('status', GoalStatus::PendingVerification)->count(),
                'completedGoals' => Goal::query()->where('status', GoalStatus::VerifiedCompleted)->count(),
                'pendingTasks' => Task::query()->whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])->count(),
                'tasksDueToday' => Task::query()
                    ->whereDate('scheduled_date', $today)
                    ->whereIn('status', [TaskStatus::Pending, TaskStatus::InProgress])
                    ->count(),
                'completedTasksThisWeek' => Task::query()
                    ->where('status', TaskStatus::Completed)
                    ->where('completed_at', '>=', $startOfWeek)
                    ->count(),
            ] : null,
        ]);
    }
}
