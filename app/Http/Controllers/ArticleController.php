<?php

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Services\ArticleRenderer;
use Illuminate\Contracts\View\View;

class ArticleController extends Controller
{
    public function __invoke(Article $article, ArticleRenderer $renderer): View
    {
        abort_unless($article->status === ArticleStatus::Published, 404);
        abort_unless($article->published_at !== null && $article->published_at->lte(now()), 404);

        $article->load([
            'category',
            'tags',
            'comparisons.items.tool.links',
            'comparisons.items.scores.criterion',
        ]);

        $body = $renderer->render($article);

        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $article->getTranslation('title', app()->getLocale()),
                'description' => strval($article->getTranslation('meta_description', app()->getLocale()) ?: $article->getTranslation('excerpt', app()->getLocale())),
                'image' => $article->cover !== null ? asset('storage/'.$article->cover) : null,
                'datePublished' => $article->published_at?->toIso8601String(),
                'dateModified' => $article->updated_at?->toIso8601String(),
                'mainEntityOfPage' => $article->getTranslation('slug', app()->getLocale()) ? route('articles.show', $article) : url('/'),
                'author' => ['@type' => 'Organization', 'name' => config('app.name')],
                'publisher' => ['@type' => 'Organization', 'name' => config('app.name')],
            ],
        ];

        foreach ($article->comparisons as $comparison) {
            $jsonLd[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => $comparison->getTranslation('title', app()->getLocale()),
                'itemListElement' => $comparison->items->map(fn ($item, $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'item' => [
                        '@type' => 'Product',
                        'name' => $item->tool?->getTranslation('name', app()->getLocale()),
                        'url' => $item->tool !== null ? route('tools.show', $item->tool) : null,
                    ],
                ])->all(),
            ];
        }

        return view('article', [
            'article' => $article,
            'body' => $body,
            'jsonLd' => $jsonLd,
        ]);
    }
}
