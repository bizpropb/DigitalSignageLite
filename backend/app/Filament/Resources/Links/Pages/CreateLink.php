<?php

namespace App\Filament\Resources\Links\Pages;

use App\Filament\Resources\Links\LinkResource;
use App\Models\Item;
use Filament\Resources\Pages\CreateRecord;

class CreateLink extends CreateRecord
{
    protected static string $resource = LinkResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Create the Item first
        $item = Item::create([
            'type' => 'Link',
            'name' => $data['item']['name'],
            'description' => $data['item']['description'],
            'duration' => $data['item']['duration'],
        ]);

        // Create the Link with the Item ID
        return $this->getModel()::create([
            'item_id' => $item->id,
            'url' => $data['url'],
            'animation' => $data['animation'],
            'animation_speed' => $data['animation_speed'],
        ]);
    }
}
