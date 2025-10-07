<?php

namespace App\Filament\Resources\Embeddings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;

class EmbeddingForm
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

                Textarea::make('embed_code')
                    ->label('Embed Code')
                    ->required()
                    ->rows(10)
                    ->columnSpanFull()
                    ->placeholder('<iframe width="100%" height="100%" src="https://sway.cloud.microsoft/s/obcdiwDpiOhlRDB2/embed" frameborder="0" marginheight="0" marginwidth="0" max-width="100%" sandbox="allow-forms allow-modals allow-orientation-lock allow-popups allow-same-origin allow-scripts" scrolling="no" style="border: none; max-width: 100%; max-height: 100vh" allowfullscreen mozallowfullscreen msallowfullscreen webkitallowfullscreen></iframe>')
                    ->hint('Paste the full iframe embed code provided by the service (Sway, YouTube, etc.).')
                    ->helperText('More info about YouTube embedding options: https://developers.google.com/youtube/player_parameters?hl=en'),
            ]);
    }
}
