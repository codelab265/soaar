<?php

namespace App\Console\Commands;

use App\Enums\StreakType;
use App\Models\Streak;
use App\Notifications\StreakAtRiskNotification;
use Illuminate\Console\Command;

class SendStreakRiskNotificationsCommand extends Command
{
    protected $signature = 'app:send-streak-risk-notifications';

    protected $description = 'Warn users whose streak will break if no activity today';

    public function handle(): int
    {
        $streaks = Streak::where('type', StreakType::Daily)
            ->where('current_count', '>', 0)
            ->whereDate('last_activity_date', now()->subDay())
            ->with('user')
            ->get();

        $count = 0;

        foreach ($streaks as $streak) {
            $streak->user->notify(new StreakAtRiskNotification($streak->current_count));
            $count++;
        }

        $this->info("Sent {$count} streak risk notification(s).");

        return self::SUCCESS;
    }
}
