<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChallengeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreChallengeRequest;
use App\Http\Requests\Admin\UpdateChallengeRequest;
use App\Models\Challenge;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ChallengeController extends Controller
{
    public function updateStatus(Request $request, Challenge $challenge): RedirectResponse
    {
        $statuses = array_map(fn (ChallengeStatus $status): string => $status->value, ChallengeStatus::cases());

        $data = $request->validate([
            'status' => ['required', 'string', Rule::in($statuses)],
        ]);

        $challenge->forceFill(['status' => $data['status']])->save();

        return back()->with('success', 'Challenge status updated.');
    }

    public function destroy(Challenge $challenge): RedirectResponse
    {
        $challenge->users()->detach();
        $challenge->delete();

        return back()->with('success', 'Challenge deleted.');
    }

    public function create(): Response
    {
        return Inertia::render('admin/challenges/create', [
            'statuses' => array_map(fn (ChallengeStatus $status): string => $status->value, ChallengeStatus::cases()),
            'defaults' => [
                'status' => ChallengeStatus::Active->value,
            ],
        ]);
    }

    public function store(StoreChallengeRequest $request): RedirectResponse
    {
        Challenge::create($request->validated());

        return redirect()
            ->to(route('admin.challenges', absolute: false))
            ->with('success', 'Challenge created.');
    }

    public function edit(Challenge $challenge): Response
    {
        return Inertia::render('admin/challenges/edit', [
            'statuses' => array_map(fn (ChallengeStatus $status): string => $status->value, ChallengeStatus::cases()),
            'challenge' => [
                'id' => $challenge->id,
                'title' => $challenge->title,
                'description' => $challenge->description,
                'duration_days' => $challenge->duration_days,
                'reward_points' => $challenge->reward_points,
                'status' => $challenge->status->value,
                'start_date' => $challenge->start_date?->toDateString(),
                'end_date' => $challenge->end_date?->toDateString(),
            ],
        ]);
    }

    public function update(UpdateChallengeRequest $request, Challenge $challenge): RedirectResponse
    {
        $challenge->update($request->validated());

        return redirect()
            ->to(route('admin.challenges', absolute: false))
            ->with('success', 'Challenge updated.');
    }

    public function index(Request $request): Response
    {
        $statuses = array_map(fn (ChallengeStatus $status): string => $status->value, ChallengeStatus::cases());

        $status = (string) $request->query('status', 'all');
        if ($status !== 'all' && ! in_array($status, $statuses, true)) {
            $status = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $challenges = Challenge::query()
            ->withCount('users')
            ->when($status !== 'all', fn (Builder $query): Builder => $query->where('status', $status))
            ->when($search !== '', fn (Builder $query): Builder => $query->where('title', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Challenge $challenge): array => [
                'id' => $challenge->id,
                'title' => $challenge->title,
                'duration_days' => $challenge->duration_days,
                'reward_points' => $challenge->reward_points,
                'status' => $challenge->status->value,
                'start_date' => $challenge->start_date?->toDateString(),
                'end_date' => $challenge->end_date?->toDateString(),
                'users_count' => $challenge->users_count,
                'created_at' => $challenge->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/challenges', [
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'challenges' => $challenges,
            'summary' => [
                'total' => Challenge::count(),
                'active' => Challenge::where('status', ChallengeStatus::Active->value)->count(),
                'completed' => Challenge::where('status', ChallengeStatus::Completed->value)->count(),
                'cancelled' => Challenge::where('status', ChallengeStatus::Cancelled->value)->count(),
            ],
        ]);
    }
}
