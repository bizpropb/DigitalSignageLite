<?php

namespace App\Filament\Resources\Links\Pages;

use App\Filament\Resources\Links\LinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditLink extends EditRecord
{
    protected static string $resource = LinkResource::class;

    public function getRecordTitle(): string
    {
        return $this->getRecord()->item->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        
        // Load Item data into form
        $data['item'] = [
            'name' => $record->item->name,
            'description' => $record->item->description,
            'duration' => $record->item->duration ?? 0,
        ];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update the associated Item
        $record->item->update([
            'name' => $data['item']['name'],
            'description' => $data['item']['description'],
            'duration' => $data['item']['duration'] ?? 0,
        ]);

        // Update the Link
        $record->update([
            'url' => $data['url'],
            'animation' => $data['animation'],
            'animation_speed' => $data['animation_speed'],
        ]);

        return $record;
    }
}
