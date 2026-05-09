<?php

namespace App\Console\Commands;

use App\Services\LeaderboardService;
use Illuminate\Console\Command;

class AwardLeaderboardBonusCommand extends Command
{
    protected $signature = 'app:award-leaderboard-bonus';

    protected $description = 'Award daily top-10 leaderboard bonus points once per user per day';

    public function __construct(
        private readonly LeaderboardService $leaderboardService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $awarded = $this->leaderboardService->awardTopTenBonus(now());

        $this->info("Awarded leaderboard bonus to {$awarded} user(s).");

        return self::SUCCESS;
    }
}
