<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ObjectiveStatus;
use App\Http\Controllers\Controller;
use App\Models\Objective;
use App\Models\User;
use App\Services\ObjectiveCompletionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ObjectiveController extends Controller
{
    public function __construct(
        private ObjectiveCompletionService $completionService,
    ) {}

    public function index(Request $request): Response
    {
        $statuses = array_map(
            fn (ObjectiveStatus $status): string => $status->value,
            ObjectiveStatus::cases(),
        );

        $status = (string) $request->query('status', 'all');

        if ($status !== 'all' && ! in_array($status, $statuses, true)) {
            $status = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $objectives = Objective::query()
            ->with([
                'goal:id,title,user_id',
                'goal.user:id,name,username,email',
            ])
            ->withCount('tasks')
            ->when($status !== 'all', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): Builder {
                return $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhereHas('goal', fn (Builder $query): Builder => $query->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('goal.user', fn (Builder $query): Builder => $this->searchUser($query, $search));
                });
            })
            ->orderBy('priority')
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Objective $objective): array => [
                'id' => $objective->id,
                'title' => $objective->title,
                'status' => $objective->status->value,
                'priority' => $objective->priority,
                'tasks_count' => $objective->tasks_count,
                'goal' => [
                    'id' => $objective->goal->id,
                    'title' => $objective->goal->title,
                ],
                'owner' => $this->userPayload($objective->goal->user),
                'created_at' => $objective->created_at?->toIso8601String(),
                'updated_at' => $objective->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/objectives', [
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'objectives' => $objectives,
            'summary' => [
                'total' => Objective::count(),
                'pending' => Objective::where('status', ObjectiveStatus::Pending->value)->count(),
                'in_progress' => Objective::where('status', ObjectiveStatus::InProgress->value)->count(),
                'completed' => Objective::where('status', ObjectiveStatus::Completed->value)->count(),
                'verified' => Objective::where('status', ObjectiveStatus::Verified->value)->count(),
            ],
        ]);
    }

    public function verify(Objective $objective): RedirectResponse
    {
        if ($objective->status !== ObjectiveStatus::Completed) {
            return back()->withErrors([
                'objective' => 'Only completed objectives can be verified.',
            ]);
        }

        $this->completionService->verifyObjective($objective);

        return back()->with('success', 'Objective verified.');
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
