<?php

namespace App\Console\Commands;

use App\Enums\StreakType;
use App\Models\Streak;
use App\Services\StreakService;
use Illuminate\Console\Command;

class CheckStreaksCommand extends Command
{
    protected $signature = 'app:check-streaks';

    protected $description = 'Break daily streaks for users who had no activity yesterday';

    public function handle(StreakService $streakService): int
    {
        $streaks = Streak::where('type', StreakType::Daily)
            ->where('current_count', '>', 0)
            ->where(function ($query) {
                $query->whereNull('last_activity_date')
                    ->orWhereDate('last_activity_date', '<', now()->subDay());
            })
            ->with('user')
            ->get();

        $count = 0;

        foreach ($streaks as $streak) {
            $streakService->breakStreak($streak->user, StreakType::Daily);
            $count++;
        }

        $this->info("Broke {$count} streak(s).");

        return self::SUCCESS;
    }
}
