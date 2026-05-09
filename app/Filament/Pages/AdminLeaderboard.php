<?php

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class AdminLeaderboard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static string|UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $title = 'Leaderboard';

    protected string $view = 'filament.pages.admin-leaderboard';

    public string $sort = 'total_points';

    public function mount(): void
    {
        $this->sort = in_array($this->sort, ['total_points', 'discipline_score', 'current_streak'], true)
            ? $this->sort
            : 'total_points';
    }

    /**
     * @return Collection<int, User>
     */
    public function users(): Collection
    {
        return User::query()
            ->whereNull('suspended_at')
            ->orderByDesc($this->sort)
            ->orderBy('id')
            ->limit(50)
            ->get(['id', 'name', 'username', 'email', 'total_points', 'discipline_score', 'current_streak', 'longest_streak']);
    }
}
