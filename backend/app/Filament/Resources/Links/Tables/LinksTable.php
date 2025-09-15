<?php

namespace App\Filament\Resources\Links\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item.name')
                    ->label('Item Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('item.description')
                    ->label('Item Description')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('animation')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'None' => 'gray',
                        'Fade In' => 'info',
                        'Slide Left', 'Slide Right', 'Slide Up', 'Slide Down' => 'success',
                        'Zoom In' => 'warning',
                        'Bounce' => 'danger',
                        default => 'primary',
                    }),
                TextColumn::make('animation_speed')
                    ->label('Speed')
                    ->numeric()
                    ->sortable()
                    ->suffix('x'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
