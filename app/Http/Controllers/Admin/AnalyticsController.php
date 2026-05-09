<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $days = (int) $request->query('days', 30);
        $days = in_array($days, [7, 14, 30, 90], true) ? $days : 30;

        $today = Carbon::today();
        $start = (clone $today)->subDays($days - 1)->startOfDay();

        $tasksByDay = Task::query()
            ->where('status', TaskStatus::Completed)
            ->whereBetween('completed_at', [$start, (clone $today)->endOfDay()])
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as completed_tasks, COALESCE(SUM(points_value), 0) as points_earned')
            ->groupByRaw('DATE(completed_at)')
            ->get()
            ->keyBy('date');

        $newUsersByDay = User::query()
            ->whereBetween('created_at', [$start, (clone $today)->endOfDay()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as new_users')
            ->groupByRaw('DATE(created_at)')
            ->pluck('new_users', 'date')
            ->all();

        $series = collect(range(0, $days - 1))
            ->map(function (int $offset) use ($today, $days, $tasksByDay, $newUsersByDay): array {
                $date = (clone $today)->subDays($days - 1 - $offset)->toDateString();
                $row = $tasksByDay->get($date);

                return [
                    'date' => $date,
                    'completedTasks' => $row ? (int) $row->completed_tasks : 0,
                    'pointsEarned' => $row ? (int) $row->points_earned : 0,
                    'newUsers' => (int) ($newUsersByDay[$date] ?? 0),
                ];
            })
            ->all();

        return Inertia::render('admin/analytics', [
            'filters' => [
                'days' => $days,
            ],
            'series' => $series,
            'summary' => [
                'completedTasks' => array_sum(array_map(fn (array $d): int => (int) $d['completedTasks'], $series)),
                'pointsEarned' => array_sum(array_map(fn (array $d): int => (int) $d['pointsEarned'], $series)),
                'newUsers' => array_sum(array_map(fn (array $d): int => (int) $d['newUsers'], $series)),
            ],
        ]);
    }
}
