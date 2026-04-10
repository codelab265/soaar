<?php

namespace App\Filament\Resources\AccountabilityPartnerRequests;

use App\Filament\Resources\AccountabilityPartnerRequests\Pages\CreateAccountabilityPartnerRequest;
use App\Filament\Resources\AccountabilityPartnerRequests\Pages\EditAccountabilityPartnerRequest;
use App\Filament\Resources\AccountabilityPartnerRequests\Pages\ListAccountabilityPartnerRequests;
use App\Filament\Resources\AccountabilityPartnerRequests\Schemas\AccountabilityPartnerRequestForm;
use App\Filament\Resources\AccountabilityPartnerRequests\Tables\AccountabilityPartnerRequestsTable;
use App\Models\AccountabilityPartnerRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AccountabilityPartnerRequestResource extends Resource
{
    protected static ?string $model = AccountabilityPartnerRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Partner Requests';

    protected static string|UnitEnum|null $navigationGroup = 'Goal Management';

    public static function form(Schema $schema): Schema
    {
        return AccountabilityPartnerRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountabilityPartnerRequestsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountabilityPartnerRequests::route('/'),
            'create' => CreateAccountabilityPartnerRequest::route('/create'),
            'edit' => EditAccountabilityPartnerRequest::route('/{record}/edit'),
        ];
    }
}
