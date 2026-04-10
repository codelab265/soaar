<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Services\TaskCompletionService;
use Illuminate\Console\Command;

class ProcessMissedTasksCommand extends Command
{
    protected $signature = 'app:process-missed-tasks';

    protected $description = 'Mark pending tasks with past scheduled dates as missed and apply penalties';

    public function handle(TaskCompletionService $taskCompletionService): int
    {
        $tasks = Task::where('status', TaskStatus::Pending)
            ->whereNotNull('scheduled_date')
            ->where('scheduled_date', '<', now()->startOfDay())
            ->get();

        $count = 0;

        foreach ($tasks as $task) {
            $taskCompletionService->missTask($task);
            $count++;
        }

        $this->info("Processed {$count} missed task(s).");

        return self::SUCCESS;
    }
}
