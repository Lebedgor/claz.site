<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use App\Models\Tag;
use App\Support\Slugger;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['slug']['en'] ?? null)) {
            $tag = Tag::query()->findOrFail((int) $this->record->getKey());
            $name = strval($data['name']['en'] ?? $tag->getTranslation('name', 'en'));
            $data['slug']['en'] = Slugger::unique(Tag::class, 'en', $name, (int) $tag->getKey());
        }

        return $data;
    }
}
