<?php

namespace App\Filament\Resources\Items\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
                        'Link' => 'Link',
                        'Image' => 'Image',
                        'Video' => 'Video',
                        'Text' => 'Text',
                    ])
                    ->required(),
                
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                
                Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                
                TextInput::make('duration')
                    ->required()
                    ->numeric()
                    ->suffix('seconds')
                    ->helperText('Duration in seconds for how long this item should be displayed'),
            ]);
    }
}
