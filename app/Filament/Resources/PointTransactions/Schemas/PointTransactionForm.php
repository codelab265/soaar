<?php

namespace App\Filament\Resources\PointTransactions\Schemas;

use App\Enums\PointTransactionType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PointTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('type')
                    ->options(PointTransactionType::class)
                    ->required(),
                TextInput::make('points')
                    ->required()
                    ->numeric(),
                TextInput::make('description')
                    ->required(),
                TextInput::make('transactionable_type'),
                TextInput::make('transactionable_id')
                    ->numeric(),
                Textarea::make('metadata')
                    ->columnSpanFull(),
            ]);
    }
}
