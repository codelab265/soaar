<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('objective_id')
                    ->relationship('objective', 'title')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Select::make('difficulty')
                    ->options(TaskDifficulty::class)
                    ->default('simple')
                    ->required(),
                TextInput::make('minimum_duration')
                    ->required()
                    ->numeric()
                    ->default(5)
                    ->suffix('minutes'),
                TextInput::make('points_value')
                    ->required()
                    ->numeric()
                    ->default(5),
                Select::make('status')
                    ->options(TaskStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('repetition_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('repetition_decay')
                    ->required()
                    ->numeric()
                    ->default(1),
                DatePicker::make('scheduled_date'),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
