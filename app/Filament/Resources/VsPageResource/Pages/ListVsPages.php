<?php

namespace App\Filament\Resources\VsPageResource\Pages;

use App\Filament\Resources\VsPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVsPages extends ListRecords
{
    protected static string $resource = VsPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
