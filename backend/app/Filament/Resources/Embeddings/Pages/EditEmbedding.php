<?php

namespace App\Filament\Resources\Embeddings\Pages;

use App\Filament\Resources\Embeddings\EmbeddingResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditEmbedding extends EditRecord
{
    protected static string $resource = EmbeddingResource::class;

    public function getRecordTitle(): string
    {
        return $this->getRecord()->item->name;
    }

    protected function getFormActions(): array
    {
        return [
            ...parent::getFormActions(),
            Action::make('insertYouTubeHelper')
                ->label('Insert YouTube Helper')
                ->color('gray')
                ->action(function () {
                    $helperText = 'Below you find an iframe example for a youtube video. The src line contains many options like:   |
autoplay=1 (play automatically)                                                                                                              |
mute=1 (video plays muted)                                                                                                                    |
controls=1 (video plays with youtube\'s control interface)                                                                    |
cc_load_policy=1 (video plays with closed_captions [subtitles] enabled)                                           |
cc_lang_pref=en (english language was selected for the subtitles, if available)                                |
hl=en (english language for interface)                                                                                                   |
More info about options: https://developers.google.com/youtube/player_parameters?hl=en          |
-------------------------------------------------------------------------------------------------
<iframe
  width="100%"
  height="100%"
  src="https://www.youtube.com/embed/SqcY0GlETPk?autoplay=1&mute=1&controls=0&cc_load_policy=1&cc_lang_pref=en&hl=en"
  title="YouTube video player"
  frameborder="0"
  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
  referrerpolicy="strict-origin-when-cross-origin"
  allowfullscreen>
</iframe>';

                    $this->data['embed_code'] = $helperText;
                }),
        ];
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

        $data['item'] = [
            'name' => $record->item->name,
            'description' => $record->item->description,
            'duration' => $record->item->duration ?? 0,
        ];

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->item->update([
            'name' => $data['item']['name'],
            'description' => $data['item']['description'],
            'duration' => $data['item']['duration'] ?? 0,
        ]);

        $record->update([
            'embed_code' => $data['embed_code'],
        ]);

        return $record;
    }
}
