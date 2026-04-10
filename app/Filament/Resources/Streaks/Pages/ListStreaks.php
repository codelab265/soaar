<?php

namespace App\Filament\Resources\Streaks\Pages;

use App\Filament\Resources\Streaks\StreakResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStreaks extends ListRecords
{
    protected static string $resource = StreakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
