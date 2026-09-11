<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use App\Models\Tag;
use App\Support\Slugger;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['slug']['en'] ?? null)) {
            $data['slug']['en'] = Slugger::unique(Tag::class, 'en', strval($data['name']['en'] ?? ''));
        }

        return $data;
    }
}
