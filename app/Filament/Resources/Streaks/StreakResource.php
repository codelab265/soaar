<?php

namespace App\Filament\Resources\Streaks;

use App\Filament\Resources\Streaks\Pages\CreateStreak;
use App\Filament\Resources\Streaks\Pages\EditStreak;
use App\Filament\Resources\Streaks\Pages\ListStreaks;
use App\Filament\Resources\Streaks\Schemas\StreakForm;
use App\Filament\Resources\Streaks\Tables\StreaksTable;
use App\Models\Streak;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StreakResource extends Resource
{
    protected static ?string $model = Streak::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static string|UnitEnum|null $navigationGroup = 'Points & Streaks';

    public static function form(Schema $schema): Schema
    {
        return StreakForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StreaksTable::configure($table);
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
            'index' => ListStreaks::route('/'),
            'create' => CreateStreak::route('/create'),
            'edit' => EditStreak::route('/{record}/edit'),
        ];
    }
}
