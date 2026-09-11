<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Support\Slugger;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['slug']['en'] ?? null)) {
            $category = Category::query()->findOrFail((int) $this->record->getKey());
            $name = strval($data['name']['en'] ?? $category->getTranslation('name', 'en'));
            $data['slug']['en'] = Slugger::unique(Category::class, 'en', $name, (int) $category->getKey());
        }

        return $data;
    }
}
