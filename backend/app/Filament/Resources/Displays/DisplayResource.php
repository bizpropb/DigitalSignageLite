<?php

namespace App\Filament\Resources\Displays;

use App\Filament\Resources\Displays\Pages\CreateDisplay;
use App\Filament\Resources\Displays\Pages\EditDisplay;
use App\Filament\Resources\Displays\Pages\ListDisplays;
use App\Filament\Resources\Displays\Schemas\DisplayForm;
use App\Filament\Resources\Displays\Tables\DisplaysTable;
use App\Models\Display;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DisplayResource extends Resource
{
    protected static ?string $model = Display::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DisplayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DisplaysTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDisplays::route('/'),
            'create' => CreateDisplay::route('/create'),
            'edit' => EditDisplay::route('/{record}/edit'),
        ];
    }
}
