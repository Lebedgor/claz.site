<?php

namespace App\Http\Controllers;

use App\Enums\ToolStatus;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tool;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = Cache::remember('sitemap-urls', 3600, fn (): array => $this->buildUrls());

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /** @return list<array{loc: string, lastmod: string|null}> */
    private function buildUrls(): array
    {
        $articles = Article::query()->published()->orderByDesc('published_at')->get();
        $tools = Tool::query()->where('status', ToolStatus::Published->value)->orderBy('id')->get();
        $categories = Category::query()->orderBy('id')->get();
        $latestUpdate = $articles->first()?->updated_at?->toIso8601String();

        $entries = [
            ['loc' => url('/'), 'lastmod' => $latestUpdate],
            ['loc' => route('articles.index'), 'lastmod' => $latestUpdate],
            ['loc' => route('tools.index'), 'lastmod' => $latestUpdate],
        ];

        foreach ($articles as $article) {
            $entries[] = ['loc' => route('articles.show', $article), 'lastmod' => $article->updated_at?->toIso8601String()];
        }

        foreach ($categories as $category) {
            $entries[] = ['loc' => route('category.show', $category), 'lastmod' => $category->updated_at?->toIso8601String()];
        }

        foreach ($tools as $tool) {
            $entries[] = ['loc' => route('tools.show', $tool), 'lastmod' => $tool->updated_at?->toIso8601String()];
        }

        $count = $tools->count();

        for ($a = 0; $a < $count; $a++) {
            for ($b = $a + 1; $b < $count; $b++) {
                $entries[] = [
                    'loc' => url('/vs/'.$this->slugOf($tools[$a]).'-vs-'.$this->slugOf($tools[$b])),
                    'lastmod' => null,
                ];
            }
        }

        return $entries;
    }

    private function slugOf(Tool $tool): string
    {
        return strval($tool->getTranslation('slug', app()->getLocale()));
    }
}
