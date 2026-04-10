<?php

namespace App\Filament\Resources\Subscriptions\Schemas;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionTier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('tier')
                    ->options(SubscriptionTier::class)
                    ->required(),
                Select::make('status')
                    ->options(SubscriptionStatus::class)
                    ->required(),
                TextInput::make('price_mwk')
                    ->label('Price (MWK)')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at'),
                DateTimePicker::make('cancelled_at'),
            ]);
    }
}
