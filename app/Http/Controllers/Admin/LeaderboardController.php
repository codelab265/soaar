<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $sort = $request->string('sort')->toString();

        if (! in_array($sort, ['total_points', 'discipline_score', 'current_streak'], true)) {
            $sort = 'total_points';
        }

        $users = User::query()
            ->whereNull('suspended_at')
            ->orderByDesc($sort)
            ->orderBy('id')
            ->limit(50)
            ->get(['id', 'name', 'username', 'email', 'total_points', 'discipline_score', 'current_streak', 'longest_streak']);

        return Inertia::render('admin/leaderboard', [
            'sort' => $sort,
            'users' => $users,
        ]);
    }
}
