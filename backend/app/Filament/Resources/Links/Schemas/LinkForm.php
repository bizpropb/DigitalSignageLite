<?php

namespace App\Filament\Resources\Links\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class LinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('item_id'),
                
                TextInput::make('item.name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),
                    
                Textarea::make('item.description')
                    ->label('Description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                    
                TextInput::make('item.duration')
                    ->label('Duration')
                    ->required()
                    ->numeric()
                    ->suffix('seconds'),
                
                TextInput::make('url')
                    ->required()
                    ->url()
                    ->maxLength(255)
                    ->placeholder('https://example.com'),
                    
                Select::make('animation')
                    ->options([
                        'none' => 'None',
                        'down' => 'Down',
                        'down&reset' => 'Down & Reset',
                        'down&up' => 'Down & Up',
                    ])
                    ->required()
                    ->default('none'),
                    
                TextInput::make('animation_speed')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
