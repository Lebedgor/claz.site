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
        $locale = app()->getLocale();

        $tools = Tool::query()
            ->where('status', ToolStatus::Published->value)
            ->when($this->type !== '', fn ($query) => $query->where('type', $this->type))
            ->when($this->search !== '', fn ($query) => $query->where("name->{$locale}", 'like', "%{$this->search}%"))
            ->orderByRaw('rating_avg desc nulls last')
            ->paginate(12)
            ->withQueryString();

        $allPublished = Tool::query()
            ->where('status', ToolStatus::Published->value)
            ->orderByRaw('rating_avg desc nulls last')
            ->get();

        $jsonLd = [[
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => __('site.tools_index.title'),
            'description' => __('site.tools_index.meta_description'),
            'url' => route('tools.index'),
            'inLanguage' => $locale,
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $allPublished->values()->map(fn (Tool $tool, int $i): array => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'url' => route('tools.show', $tool),
                    'name' => $tool->getTranslation('name', $locale),
                ])->all(),
            ],
        ]];

        return view('livewire.tools-index', ['tools' => $tools, 'jsonLd' => $jsonLd])
            ->layout('components.layouts.public')
            ->title(__('site.nav.tools'));
    }
}
