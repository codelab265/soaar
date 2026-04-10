<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('duration'),
                TextInput::make('price_mwk')
                    ->label('Price (MWK)')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                TextInput::make('price_points')
                    ->label('Price (Points)')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Select::make('content_type')
                    ->options([
                        'video' => 'Video',
                        'audio' => 'Audio',
                        'text' => 'Text',
                    ]),
                TextInput::make('content_url')
                    ->url(),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
