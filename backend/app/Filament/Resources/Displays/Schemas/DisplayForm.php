<?php

namespace App\Filament\Resources\Displays\Schemas;

use App\Enums\DisplayType;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class DisplayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                Select::make('display_type')
                    ->options([
                        'New' => 'New',
                        'Inactive' => 'Inactive',
                        'Public Facing' => 'Public Facing',
                        'Internal Use' => 'Internal Use',
                        'Advertising' => 'Advertising',
                    ])
                    ->default('New')
                    ->required(),
                
                TextInput::make('location')
                    ->maxLength(255)
                    ->placeholder('e.g., Main Lobby, Conference Room A'),
                
                Select::make('status')
                    ->options([
                        'connected' => 'Connected',
                        'disconnected' => 'Disconnected',
                        'error' => 'Error',
                    ])
                    ->default('disconnected')
                    ->disabled()
                    ->helperText('Status is automatically managed by the system'),
            ]);
    }
}
