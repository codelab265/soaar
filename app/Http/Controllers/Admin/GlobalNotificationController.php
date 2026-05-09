<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StreakType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GlobalNotificationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('admin/notifications/global');
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var array{audience: string, title: string, body: string} $data */
        $data = $request->validate([
            'audience' => ['required', 'string', 'in:all,inactive,streak_at_risk'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:500'],
        ]);

        $query = User::query()->whereNull('suspended_at');

        if ($data['audience'] === 'inactive') {
            $cutoff = now()->subDays(2)->toDateString();

            $query->whereHas('streaks', function ($q) use ($cutoff): void {
                $q->where('type', StreakType::Daily)
                    ->where(function ($qq) use ($cutoff): void {
                        $qq->whereNull('last_activity_date')
                            ->orWhereDate('last_activity_date', '<=', $cutoff);
                    });
            });
        }

        if ($data['audience'] === 'streak_at_risk') {
            $yesterday = now()->subDay()->toDateString();

            $query->whereHas('streaks', function ($q) use ($yesterday): void {
                $q->where('type', StreakType::Daily)
                    ->where('current_count', '>', 0)
                    ->whereDate('last_activity_date', $yesterday);
            });
        }

        $query->chunkById(500, function ($users) use ($data): void {
            foreach ($users as $user) {
                $user->notify(new AdminBroadcastNotification($data['title'], $data['body']));
            }
        });

        return back()->with('success', 'Notification sent.');
    }
}
