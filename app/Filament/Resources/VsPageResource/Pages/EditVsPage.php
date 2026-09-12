<?php

namespace App\Filament\Resources\VsPageResource\Pages;

use App\Filament\Resources\VsPageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVsPage extends EditRecord
{
    protected static string $resource = VsPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
