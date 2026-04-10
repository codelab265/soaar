<?php

namespace App\Console\Commands;

use App\Enums\StreakType;
use App\Models\Streak;
use App\Notifications\InactivityNotification;
use Illuminate\Console\Command;

class SendInactivityNotificationsCommand extends Command
{
    protected $signature = 'app:send-inactivity-notifications';

    protected $description = 'Notify users who have been inactive for 2+ days';

    /** Minimum days of inactivity before notifying. */
    private const INACTIVITY_THRESHOLD_DAYS = 2;

    public function handle(): int
    {
        $cutoff = now()->subDays(self::INACTIVITY_THRESHOLD_DAYS);

        $streaks = Streak::where('type', StreakType::Daily)
            ->where(function ($query) use ($cutoff) {
                $query->whereNull('last_activity_date')
                    ->orWhereDate('last_activity_date', '<=', $cutoff);
            })
            ->with('user')
            ->get();

        $count = 0;

        foreach ($streaks as $streak) {
            $inactiveDays = $streak->last_activity_date
                ? (int) now()->diffInDays($streak->last_activity_date)
                : self::INACTIVITY_THRESHOLD_DAYS;

            $streak->user->notify(new InactivityNotification($inactiveDays));
            $count++;
        }

        $this->info("Sent {$count} inactivity notification(s).");

        return self::SUCCESS;
    }
}
