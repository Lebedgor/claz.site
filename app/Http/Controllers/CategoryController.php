<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function __invoke(Category $category): View
    {
        $locale = app()->getLocale();
        $name = $category->getTranslation('name', $locale);

        $articles = Article::query()
            ->published()
            ->with('category')
            ->where('category_id', $category->getKey())
            ->orderByDesc('published_at')
            ->paginate(9);

        $jsonLd = [[
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => __('site.category.articles_in', ['name' => $name]),
            'description' => __('site.category.meta_description', ['name' => $name]),
            'url' => route('category.show', $category),
            'inLanguage' => $locale,
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $articles->getCollection()->map(fn (Article $article, int $i): array => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'url' => route('articles.show', $article),
                    'name' => $article->getTranslation('title', $locale),
                ])->all(),
            ],
        ]];

        return view('category', [
            'category' => $category,
            'name' => $name,
            'articles' => $articles,
            'jsonLd' => $jsonLd,
        ]);
    }
}
