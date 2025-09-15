<?php

namespace App\Filament\Resources\Links\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use App\Models\Item;

class LinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_id')
                    ->label('Item')
                    ->options(Item::where('type', 'Link')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->helperText('Select the Link item this configuration applies to'),
                    
                Select::make('animation')
                    ->options([
                        'None' => 'None',
                        'Fade In' => 'Fade In',
                        'Slide Left' => 'Slide Left',
                        'Slide Right' => 'Slide Right',
                        'Slide Up' => 'Slide Up',
                        'Slide Down' => 'Slide Down',
                        'Zoom In' => 'Zoom In',
                        'Bounce' => 'Bounce',
                    ])
                    ->required()
                    ->default('None'),
                    
                TextInput::make('animation_speed')
                    ->label('Animation Speed')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->step(0.1)
                    ->min(0.1)
                    ->max(5)
                    ->suffix('x')
                    ->helperText('Animation speed multiplier (1 = normal speed, 2 = double speed, 0.5 = half speed)'),
            ]);
    }
}
