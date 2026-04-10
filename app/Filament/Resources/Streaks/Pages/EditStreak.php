<?php

namespace App\Filament\Resources\Streaks\Pages;

use App\Filament\Resources\Streaks\StreakResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStreak extends EditRecord
{
    protected static string $resource = StreakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
