<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

class MediaPickerField extends Field
{
    protected string $view = 'filament.forms.components.media-picker-field';

    protected string|Closure|null $diskName = null;

    public function disk(string|Closure|null $name): static
    {
        $this->diskName = $name;

        return $this;
    }

    public function getDiskName(): string
    {
        return $this->evaluate($this->diskName) ?? config('filament.default_filesystem_disk', 'public');
    }
}
