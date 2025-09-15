<?php

namespace App\Filament\Resources\Displays\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Program;

class DisplayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                Select::make('program_id')
                    ->label('Program')
                    ->options(Program::pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->helperText('Select the program this display should run'),
                
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
