<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StreakType;
use App\Http\Controllers\Controller;
use App\Models\Streak;
use App\Models\User;
use App\Services\StreakService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StreakController extends Controller
{
    public function __construct(
        private StreakService $streakService,
    ) {}

    public function index(Request $request): Response
    {
        $types = array_map(fn (StreakType $type): string => $type->value, StreakType::cases());

        $type = (string) $request->query('type', 'all');
        if ($type !== 'all' && ! in_array($type, $types, true)) {
            $type = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $streaks = Streak::query()
            ->with(['user:id,name,username,email,current_streak,longest_streak'])
            ->when($type !== 'all', fn (Builder $query): Builder => $query->where('type', $type))
            ->when($search !== '', fn (Builder $query): Builder => $query->whereHas('user', fn (Builder $q): Builder => $this->searchUser($q, $search)))
            ->orderByDesc('current_count')
            ->orderByDesc('longest_count')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Streak $streak): array => [
                'id' => $streak->id,
                'type' => $streak->type->value,
                'current_count' => $streak->current_count,
                'longest_count' => $streak->longest_count,
                'last_activity_date' => $streak->last_activity_date?->toDateString(),
                'started_at' => $streak->started_at?->toDateString(),
                'user' => $this->userPayload($streak->user),
            ]);

        return Inertia::render('admin/streaks', [
            'filters' => [
                'search' => $search,
                'type' => $type,
            ],
            'streaks' => $streaks,
            'summary' => [
                'total' => Streak::count(),
                'daily' => Streak::where('type', StreakType::Daily->value)->count(),
                'challenge' => Streak::where('type', StreakType::Challenge->value)->count(),
            ],
        ]);
    }

    public function break(Streak $streak): RedirectResponse
    {
        $this->streakService->breakStreak($streak->user, $streak->type);

        return back()->with('success', 'Streak broken.');
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
