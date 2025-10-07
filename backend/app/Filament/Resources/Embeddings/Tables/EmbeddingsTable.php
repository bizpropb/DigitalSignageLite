<?php

namespace App\Filament\Resources\Embeddings\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmbeddingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('item.description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('item.duration')
                    ->label('Duration')
                    ->suffix(' sec')
                    ->sortable(),

                TextColumn::make('embed_code')
                    ->label('Embed Code')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->embed_code),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
