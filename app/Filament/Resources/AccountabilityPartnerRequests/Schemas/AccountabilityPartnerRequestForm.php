<?php

namespace App\Filament\Resources\AccountabilityPartnerRequests\Schemas;

use App\Enums\PartnerRequestStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class AccountabilityPartnerRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('goal_id')
                    ->relationship('goal', 'title')
                    ->required(),
                Select::make('requester_id')
                    ->relationship('requester', 'name')
                    ->required(),
                Select::make('partner_id')
                    ->relationship('partner', 'name')
                    ->required(),
                Select::make('status')
                    ->options(PartnerRequestStatus::class)
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('responded_at'),
            ]);
    }
}
