<?php

namespace App\Console\Commands;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Notifications\DeadlineApproachingNotification;
use Illuminate\Console\Command;

class SendDeadlineApproachingNotificationsCommand extends Command
{
    protected $signature = 'app:send-deadline-approaching-notifications';

    protected $description = 'Notify users when active goals are nearing their deadlines';

    private const DAYS_BEFORE_DEADLINE = 2;

    public function handle(): int
    {
        $targetDate = now()->addDays(self::DAYS_BEFORE_DEADLINE)->toDateString();

        $goals = Goal::query()
            ->whereIn('status', [GoalStatus::Active, GoalStatus::PendingVerification])
            ->whereDate('deadline', $targetDate)
            ->with('user')
            ->get();

        $count = 0;

        foreach ($goals as $goal) {
            $goal->user->notify(new DeadlineApproachingNotification($goal));
            $count++;
        }

        $this->info("Sent {$count} deadline approaching notification(s).");

        return self::SUCCESS;
    }
}
