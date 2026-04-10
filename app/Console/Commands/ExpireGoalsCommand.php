<?php

namespace App\Console\Commands;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Services\GoalService;
use Illuminate\Console\Command;

class ExpireGoalsCommand extends Command
{
    protected $signature = 'app:expire-goals';

    protected $description = 'Expire active goals that are past their deadline and apply penalties';

    public function handle(GoalService $goalService): int
    {
        $goals = Goal::where('status', GoalStatus::Active)
            ->where('deadline', '<', now()->startOfDay())
            ->get();

        $count = 0;

        foreach ($goals as $goal) {
            $goalService->expireGoal($goal);
            $count++;
        }

        $this->info("Expired {$count} goal(s).");

        return self::SUCCESS;
    }
}
