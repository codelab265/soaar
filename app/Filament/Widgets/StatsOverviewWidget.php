<?php

namespace App\Filament\Widgets;

use App\Models\Goal;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count()),
            Stat::make('Active Goals', Goal::where('status', 'active')->count()),
            Stat::make('Completed Goals', Goal::where('status', 'verified_completed')->count()),
            Stat::make('Average Discipline Score', number_format((float) User::avg('discipline_score'), 1)),
        ];
    }
}
