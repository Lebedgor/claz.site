<?php

namespace App\Livewire;

use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;

class ArticlesIndex extends Component
{
    #[Url]
    public int|string $page = 1;

    public int $visiblePages = 1;

    public int $perPage = 9;

    public function mount(): void
    {
        $this->visiblePages = $this->currentPage();
    }

    public function loadMore(): void
    {
        $lastPage = max(1, (int) ceil($this->totalArticles() / $this->perPage));

        if ($this->visiblePages < $lastPage) {
            $this->visiblePages++;
        }
    }

    public function render(): View
    {
        $locale = app()->getLocale();
        $total = $this->totalArticles();
        $shown = min($total, $this->visiblePages * $this->perPage);

        $articles = Article::query()
            ->published()
            ->with('category')
            ->orderByDesc('published_at')
            ->limit($shown)
            ->get();

        $paginator = (new LengthAwarePaginator([], $total, $this->perPage, $this->currentPage()))
            ->setPath(route('articles.index', [], false))
            ->withQueryString();

        $jsonLd = [[
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => __('site.articles_index.title'),
            'description' => __('site.articles_index.meta_description'),
            'url' => $this->currentPage() > 1
                ? route('articles.index').'?page='.$this->currentPage()
                : route('articles.index'),
            'inLanguage' => $locale,
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $articles->values()->map(fn (Article $article, int $i): array => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'url' => route('articles.show', $article),
                    'name' => $article->getTranslation('title', $locale),
                ])->all(),
            ],
        ]];

        return view('livewire.articles-index', [
            'articles' => $articles,
            'paginator' => $paginator,
            'hasMore' => $shown < $total,
            'jsonLd' => $jsonLd,
        ])
            ->layout('components.layouts.public')
            ->title(__('site.articles_index.title'));
    }

    private function currentPage(): int
    {
        return is_numeric($this->page) ? max(1, (int) $this->page) : 1;
    }

    private function totalArticles(): int
    {
        return (int) Article::query()->published()->count();
    }
}
