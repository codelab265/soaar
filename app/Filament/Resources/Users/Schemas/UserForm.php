<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->schema([
                        FileUpload::make('profile_picture')
                            ->image()
                            ->disk('public')
                            ->directory('profile-pictures')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048),
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('username')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),
                        Toggle::make('is_admin'),
                        DateTimePicker::make('suspended_at')
                            ->label('Suspended at')
                            ->seconds(false),
                    ]),
                Section::make('Stats')
                    ->schema([
                        TextInput::make('discipline_score')
                            ->numeric()
                            ->default(0),
                        TextInput::make('total_points')
                            ->numeric()
                            ->default(0),
                        TextInput::make('current_streak')
                            ->numeric()
                            ->default(0),
                        TextInput::make('longest_streak')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
