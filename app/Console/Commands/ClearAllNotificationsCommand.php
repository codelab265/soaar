<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class ClearAllNotificationsCommand extends Command
{
    protected $signature = 'app:clear-all-notifications';

    protected $description = 'Clear all database notifications for all users';

    public function handle(): int
    {
        $deletedCount = DatabaseNotification::query()->count();

        if ($deletedCount > 0) {
            DatabaseNotification::query()->delete();
        }

        $this->info("Cleared {$deletedCount} notification(s) across all users.");

        return self::SUCCESS;
    }
}
