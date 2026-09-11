<?php

namespace App\Filament\Resources\ToolResource\Pages;

use App\Filament\Resources\ToolResource;
use App\Models\Tool;
use App\Support\Slugger;
use Filament\Resources\Pages\CreateRecord;

class CreateTool extends CreateRecord
{
    protected static string $resource = ToolResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['slug']['en'] ?? null)) {
            $data['slug']['en'] = Slugger::unique(Tool::class, 'en', strval($data['name']['en'] ?? ''));
        }

        return $data;
    }
}
