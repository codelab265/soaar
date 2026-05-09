<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $status = (string) $request->query('status', 'all');
        if (! in_array($status, ['all', 'active', 'suspended'], true)) {
            $status = 'all';
        }

        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->when($status === 'active', fn (Builder $query): Builder => $query->whereNull('suspended_at'))
            ->when($status === 'suspended', fn (Builder $query): Builder => $query->whereNotNull('suspended_at'))
            ->when($search !== '', function (Builder $query) use ($search): Builder {
                return $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_admin')
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'is_admin' => (bool) $user->is_admin,
                'total_points' => $user->total_points,
                'discipline_score' => $user->discipline_score,
                'current_streak' => $user->current_streak,
                'longest_streak' => $user->longest_streak,
                'suspended_at' => $user->suspended_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/users', [
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
            'users' => $users,
            'summary' => [
                'total' => User::count(),
                'active' => User::whereNull('suspended_at')->count(),
                'suspended' => User::whereNotNull('suspended_at')->count(),
                'admins' => User::where('is_admin', true)->count(),
            ],
        ]);
    }

    public function suspend(User $user): RedirectResponse
    {
        if ($user->suspended_at !== null) {
            return back()->withErrors([
                'user' => 'User is already suspended.',
            ]);
        }

        $user->forceFill(['suspended_at' => now()])->save();

        return back()->with('success', 'User suspended.');
    }

    public function unsuspend(User $user): RedirectResponse
    {
        if ($user->suspended_at === null) {
            return back()->withErrors([
                'user' => 'User is not suspended.',
            ]);
        }

        $user->forceFill(['suspended_at' => null])->save();

        return back()->with('success', 'User unsuspended.');
    }
}
