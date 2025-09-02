<?php

namespace App\Filament\Resources\Displays\Pages;

use App\Filament\Resources\Displays\DisplayResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListDisplays extends ListRecords
{
    protected static string $resource = DisplayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('how_to_register')
                ->label('How to Register Display')
                ->icon('heroicon-o-question-mark-circle')
                ->modalHeading('How to Register a Display')
                ->modalDescription('Follow these steps to register a new display device.')
                ->modalContent(view('filament.modals.register-display-help'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
        ];
    }

}
