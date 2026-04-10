<?php

namespace App\Filament\Resources\Streaks\Schemas;

use App\Enums\StreakType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StreakForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('type')
                    ->options(StreakType::class)
                    ->default('daily')
                    ->required(),
                TextInput::make('current_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('longest_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                DatePicker::make('last_activity_date'),
                DatePicker::make('started_at'),
            ]);
    }
}
