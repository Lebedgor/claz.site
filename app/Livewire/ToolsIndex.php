<?php

namespace App\Livewire;

use App\Enums\ToolStatus;
use App\Models\Tool;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ToolsIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $type = '';

    #[Url]
    public string $search = '';

    public function render(): View
    {
        $tools = Tool::query()
            ->where('status', ToolStatus::Published->value)
            ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
            ->when($this->search !== '', fn ($query) => $query->where('name->en', 'like', "%{$this->search}%"))
            ->orderByRaw('rating_avg desc nulls last')
            ->paginate(12)
            ->withQueryString();

        return view('livewire.tools-index', ['tools' => $tools])
            ->layout('components.layouts.public')
            ->title(__('site.nav.tools'));
    }
}
