<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class MediaLibrary extends Page
{
    protected string $view = 'filament.pages.media-library';

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $navigationLabel = 'File manager';

    protected static ?string $title = 'File manager';
}
