<?php

namespace App\Filament\Resources\Objectives\Schemas;

use App\Enums\ObjectiveStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ObjectiveForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('goal_id')
                    ->relationship('goal', 'title')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Select::make('status')
                    ->options(ObjectiveStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
