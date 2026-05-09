<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskCompletionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function __construct(
        private TaskCompletionService $completionService,
    ) {}

    public function index(Request $request): Response
    {
        $statuses = array_map(fn (TaskStatus $status): string => $status->value, TaskStatus::cases());
        $difficulties = array_map(fn (TaskDifficulty $difficulty): string => $difficulty->value, TaskDifficulty::cases());

        $status = (string) $request->query('status', 'all');
        if ($status !== 'all' && ! in_array($status, $statuses, true)) {
            $status = 'all';
        }

        $difficulty = (string) $request->query('difficulty', 'all');
        if ($difficulty !== 'all' && ! in_array($difficulty, $difficulties, true)) {
            $difficulty = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $tasks = Task::query()
            ->with([
                'objective:id,goal_id,title',
                'goal:id,title,user_id',
                'user:id,name,username,email',
            ])
            ->when($status !== 'all', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($difficulty !== 'all', fn (Builder $query): Builder => $query->where('difficulty', $difficulty))
            ->when($search !== '', function (Builder $query) use ($search): Builder {
                return $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhereHas('objective', fn (Builder $query): Builder => $query->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('goal', fn (Builder $query): Builder => $query->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn (Builder $query): Builder => $this->searchUser($query, $search));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Task $task): array => [
                'id' => $task->id,
                'title' => $task->title,
                'difficulty' => $task->difficulty->value,
                'status' => $task->status->value,
                'points_value' => $task->points_value,
                'effective_points' => $task->effectivePoints(),
                'scheduled_date' => $task->scheduled_date?->toDateString(),
                'completed_at' => $task->completed_at?->toIso8601String(),
                'objective' => [
                    'id' => $task->objective?->id,
                    'title' => $task->objective?->title,
                ],
                'goal' => [
                    'id' => $task->goal?->id ?? $task->objective?->goal?->id,
                    'title' => $task->goal?->title ?? $task->objective?->goal?->title,
                ],
                'owner' => $task->user ? $this->userPayload($task->user) : null,
            ]);

        return Inertia::render('admin/tasks', [
            'filters' => [
                'search' => $search,
                'status' => $status,
                'difficulty' => $difficulty,
            ],
            'tasks' => $tasks,
            'summary' => [
                'total' => Task::count(),
                'pending' => Task::where('status', TaskStatus::Pending->value)->count(),
                'in_progress' => Task::where('status', TaskStatus::InProgress->value)->count(),
                'completed' => Task::where('status', TaskStatus::Completed->value)->count(),
                'missed' => Task::where('status', TaskStatus::Missed->value)->count(),
            ],
        ]);
    }

    public function miss(Task $task): RedirectResponse
    {
        if ($task->status === TaskStatus::Completed) {
            return back()->withErrors([
                'task' => 'Completed tasks cannot be marked as missed.',
            ]);
        }

        $this->completionService->missTask($task);

        return back()->with('success', 'Task marked as missed.');
    }

    private function searchUser(Builder $query, string $search): Builder
    {
        return $query
            ->where('name', 'like', "%{$search}%")
            ->orWhere('username', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
    }

    /**
     * @return array{id: int, name: string, username: string|null, email: string}
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
        ];
    }
}
