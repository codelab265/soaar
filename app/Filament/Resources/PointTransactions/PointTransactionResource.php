<?php

namespace App\Filament\Resources\PointTransactions;

use App\Filament\Resources\PointTransactions\Pages\CreatePointTransaction;
use App\Filament\Resources\PointTransactions\Pages\EditPointTransaction;
use App\Filament\Resources\PointTransactions\Pages\ListPointTransactions;
use App\Filament\Resources\PointTransactions\Schemas\PointTransactionForm;
use App\Filament\Resources\PointTransactions\Tables\PointTransactionsTable;
use App\Models\PointTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PointTransactionResource extends Resource
{
    protected static ?string $model = PointTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Point Transactions';

    protected static string|UnitEnum|null $navigationGroup = 'Points & Streaks';

    public static function form(Schema $schema): Schema
    {
        return PointTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PointTransactionsTable::configure($table);
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
            'index' => ListPointTransactions::route('/'),
            'create' => CreatePointTransaction::route('/create'),
            'edit' => EditPointTransaction::route('/{record}/edit'),
        ];
    }
}
