<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GoalStatus;
use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\User;
use App\Services\GoalVerificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoalController extends Controller
{
    public function __construct(
        private GoalVerificationService $verificationService,
    ) {}

    public function index(Request $request): Response
    {
        $statuses = array_map(
            fn (GoalStatus $status): string => $status->value,
            GoalStatus::cases(),
        );

        $status = (string) $request->query('status', 'all');

        if ($status !== 'all' && ! in_array($status, $statuses, true)) {
            $status = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $goals = Goal::query()
            ->with([
                'user:id,name,username,email',
                'accountabilityPartner:id,name,username,email',
            ])
            ->when($status !== 'all', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($search !== '', function (Builder $query) use ($search): Builder {
                return $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $query): Builder => $this->searchUser($query, $search))
                        ->orWhereHas('accountabilityPartner', fn (Builder $query): Builder => $this->searchUser($query, $search));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Goal $goal): array => [
                'id' => $goal->id,
                'title' => $goal->title,
                'status' => $goal->status->value,
                'deadline' => $goal->deadline?->toDateString(),
                'user' => $this->userPayload($goal->user),
                'accountability_partner' => $goal->accountability_partner_id
                    ? $this->userPayload($goal->accountabilityPartner)
                    : null,
                'created_at' => $goal->created_at?->toIso8601String(),
                'updated_at' => $goal->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/goals', [
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'goals' => $goals,
            'summary' => [
                'total' => Goal::count(),
                'active' => Goal::where('status', GoalStatus::Active->value)->count(),
                'pending_verification' => Goal::where('status', GoalStatus::PendingVerification->value)->count(),
                'verified_completed' => Goal::where('status', GoalStatus::VerifiedCompleted->value)->count(),
                'cancelled' => Goal::where('status', GoalStatus::Cancelled->value)->count(),
                'expired' => Goal::where('status', GoalStatus::Expired->value)->count(),
            ],
        ]);
    }

    public function approve(Goal $goal): RedirectResponse
    {
        if ($goal->status !== GoalStatus::PendingVerification) {
            return back()->withErrors([
                'goal' => 'Only goals pending verification can be approved.',
            ]);
        }

        $this->verificationService->approveGoal($goal);

        return back()->with('success', 'Goal approved.');
    }

    public function reject(Goal $goal): RedirectResponse
    {
        if ($goal->status !== GoalStatus::PendingVerification) {
            return back()->withErrors([
                'goal' => 'Only goals pending verification can be rejected.',
            ]);
        }

        $this->verificationService->rejectGoal($goal);

        return back()->with('success', 'Goal rejected.');
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
