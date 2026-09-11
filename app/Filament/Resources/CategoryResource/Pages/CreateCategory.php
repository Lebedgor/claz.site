<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Support\Slugger;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['slug']['en'] ?? null)) {
            $data['slug']['en'] = Slugger::unique(Category::class, 'en', strval($data['name']['en'] ?? ''));
        }

        return $data;
    }
}
