<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_picture')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('username')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                IconColumn::make('is_admin')
                    ->boolean(),
                IconColumn::make('suspended_at')
                    ->label('Suspended')
                    ->boolean(fn ($record) => $record->suspended_at !== null),
                TextColumn::make('discipline_score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_points')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('current_streak')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('longest_streak')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('suspend')
                    ->label('Suspend')
                    ->color('danger')
                    ->visible(fn ($record): bool => $record->suspended_at === null)
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->forceFill(['suspended_at' => now()])->save()),
                Action::make('unsuspend')
                    ->label('Unsuspend')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->suspended_at !== null)
                    ->action(fn ($record) => $record->forceFill(['suspended_at' => null])->save()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
