<?php

namespace App\Console\Commands;

use App\Enums\GoalStatus;
use App\Models\Goal;
use App\Services\GoalVerificationService;
use Illuminate\Console\Command;

class AutoApproveGoalsCommand extends Command
{
    protected $signature = 'app:auto-approve-goals';

    protected $description = 'Auto-approve goals pending verification for 48+ hours at 80% reward';

    public function handle(GoalVerificationService $verificationService): int
    {
        $cutoff = now()->subHours(GoalVerificationService::AUTO_APPROVE_HOURS);

        $goals = Goal::where('status', GoalStatus::PendingVerification)
            ->where('updated_at', '<=', $cutoff)
            ->get();

        $count = 0;

        foreach ($goals as $goal) {
            $verificationService->autoApproveGoal($goal);
            $count++;
        }

        $this->info("Auto-approved {$count} goal(s).");

        return self::SUCCESS;
    }
}
