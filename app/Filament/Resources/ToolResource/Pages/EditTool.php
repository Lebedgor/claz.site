<?php

namespace App\Filament\Resources\ToolResource\Pages;

use App\Filament\Resources\ToolResource;
use App\Models\Tool;
use App\Support\Slugger;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTool extends EditRecord
{
    protected static string $resource = ToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['slug']['en'] ?? null)) {
            $tool = Tool::query()->findOrFail((int) $this->record->getKey());
            $name = strval($data['name']['en'] ?? $tool->getTranslation('name', 'en'));
            $data['slug']['en'] = Slugger::unique(Tool::class, 'en', $name, (int) $tool->getKey());
        }

        return $data;
    }
}
