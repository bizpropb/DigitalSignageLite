<?php

namespace App\Filament\Resources\Embeddings\Pages;

use App\Filament\Resources\Embeddings\EmbeddingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmbeddings extends ListRecords
{
    protected static string $resource = EmbeddingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
