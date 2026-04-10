<?php

namespace App\Filament\Resources\Goals\Schemas;

use App\Enums\GoalStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GoalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('why')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('category'),
                DatePicker::make('deadline')
                    ->required(),
                Select::make('status')
                    ->options(GoalStatus::class)
                    ->default('active')
                    ->required(),
                Select::make('accountability_partner_id')
                    ->relationship('accountabilityPartner', 'name'),
            ]);
    }
}
