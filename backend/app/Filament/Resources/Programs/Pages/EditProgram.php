<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProgram extends EditRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record->getKey()]));
    }
}
