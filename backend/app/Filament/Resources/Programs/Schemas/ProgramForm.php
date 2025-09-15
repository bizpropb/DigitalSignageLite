<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use App\Models\Item;
use Illuminate\Support\Str;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Repeater::make('programItems')
                    ->relationship('programItems')
                    ->schema([
                        Select::make('item_id')
                            ->hiddenLabel()
                            ->preload(false)
                            ->getOptionLabelUsing(function (string $value): string {
                                $item = Item::find($value);
                                return $item
                                    ? Str::limit($item->name, 30) . ' || ' . Str::limit($item->description, 60)
                                    : '';
                            })
                            ->getOptionLabelFromRecordUsing(
                                fn ($record) => $record
                                    ? Str::limit($record->name, 30) . ' || ' . Str::limit($record->description, 60)
                                    : ''
                            )
                            ->getSearchResultsUsing(
                                fn (string $search) => Item::query()
                                    ->where('name', 'ilike', "%{$search}%")
                                    ->orWhere('description', 'ilike', "%{$search}%")
                                    ->limit(10)
                                    ->get()
                                    ->mapWithKeys(
                                        fn ($i) => [
                                            $i->id => Str::limit($i->name, 30)
                                                  . ' || '
                                                  . Str::limit($i->description, 60)
                                        ]
                                    )
                                    ->toArray()
                            )
                            ->searchable()
                            ->searchDebounce(500)
                            ->required(),
                    ])
                    ->orderable('sort_order')
                    ->itemLabel('Drag & drop the arrow on the left to sort items')
                    ->addActionLabel('Add item')
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
