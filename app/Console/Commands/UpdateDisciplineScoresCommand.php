<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DisciplineScoreService;
use Illuminate\Console\Command;

class UpdateDisciplineScoresCommand extends Command
{
    protected $signature = 'app:update-discipline-scores';

    protected $description = 'Recalculate discipline scores for all users (weekly)';

    public function handle(DisciplineScoreService $disciplineScoreService): int
    {
        $users = User::all();
        $count = 0;

        foreach ($users as $user) {
            $disciplineScoreService->updateScore($user);
            $count++;
        }

        $this->info("Updated discipline scores for {$count} user(s).");

        return self::SUCCESS;
    }
}
