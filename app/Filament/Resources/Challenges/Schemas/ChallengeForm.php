<?php

namespace App\Filament\Resources\Challenges\Schemas;

use App\Enums\ChallengeStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ChallengeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('duration_days')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('reward_points')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Select::make('status')
                    ->options(ChallengeStatus::class)
                    ->default('active')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
            ]);
    }
}
