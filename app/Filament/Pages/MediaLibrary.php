<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;

class MediaLibrary extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.media-library';

    protected static \UnitEnum|string|null $navigationGroup = 'Content';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationLabel = 'Media library';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    #[Locked]
    public string $directory = 'uploads';

    public function mount(): void
    {
        $this->getSchema('form')?->fill();
    }

    protected function form(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('files')
                ->label('Upload files')
                ->multiple()
                ->image()
                ->disk('public')
                ->directory($this->directory)
                ->maxSize(4096),
        ])->statePath('data');
    }

    public function upload(): void
    {
        $state = $this->getSchema('form')?->getState();
        unset($state);
        $this->data = [];

        Notification::make()->title('Files uploaded')->success()->send();
    }

    /** @return list<array{path: string, url: string, size: string}> */
    public function getFiles(): array
    {
        $disk = Storage::disk('public');

        return collect($disk->allFiles($this->directory))
            ->filter(fn (string $path): bool => (int) $disk->size($path) > 0)
            ->sortDesc()
            ->map(fn (string $path): array => [
                'path' => $path,
                'url' => $disk->url($path),
                'size' => number_format($disk->size($path) / 1024).' KB',
            ])
            ->values()
            ->all();
    }

    public function deleteFile(string $path): void
    {
        $path = str_replace('\\', '/', $path);

        if (! str_starts_with($path, $this->directory.'/')) {
            Notification::make()->title('Invalid path')->danger()->send();

            return;
        }

        Storage::disk('public')->delete($path);
        Notification::make()->title('File deleted')->success()->send();
    }
}
