<?php

namespace App\Filament\Resources\Objectives;

use App\Filament\Resources\Objectives\Pages\CreateObjective;
use App\Filament\Resources\Objectives\Pages\EditObjective;
use App\Filament\Resources\Objectives\Pages\ListObjectives;
use App\Filament\Resources\Objectives\RelationManagers\TasksRelationManager;
use App\Filament\Resources\Objectives\Schemas\ObjectiveForm;
use App\Filament\Resources\Objectives\Tables\ObjectivesTable;
use App\Models\Objective;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ObjectiveResource extends Resource
{
    protected static ?string $model = Objective::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Goal Management';

    public static function form(Schema $schema): Schema
    {
        return ObjectiveForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ObjectivesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            TasksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListObjectives::route('/'),
            'create' => CreateObjective::route('/create'),
            'edit' => EditObjective::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
