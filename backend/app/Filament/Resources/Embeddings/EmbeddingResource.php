<?php

namespace App\Filament\Resources\Embeddings;

use App\Filament\Resources\Embeddings\Pages\CreateEmbedding;
use App\Filament\Resources\Embeddings\Pages\EditEmbedding;
use App\Filament\Resources\Embeddings\Pages\ListEmbeddings;
use App\Filament\Resources\Embeddings\Schemas\EmbeddingForm;
use App\Filament\Resources\Embeddings\Tables\EmbeddingsTable;
use App\Models\Embedding;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EmbeddingResource extends Resource
{
    protected static ?string $model = Embedding::class;

    protected static string | UnitEnum | null $navigationGroup = 'Items';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracket;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EmbeddingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmbeddingsTable::configure($table);
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
            'index' => ListEmbeddings::route('/'),
            'create' => CreateEmbedding::route('/create'),
            'edit' => EditEmbedding::route('/{record}/edit'),
        ];
    }
}
