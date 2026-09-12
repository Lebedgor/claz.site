<?php

namespace App\Http\Controllers;

use App\Enums\ToolStatus;
use App\Models\Article;
use App\Models\ComparisonItem;
use App\Models\Tool;
use Illuminate\Contracts\View\View;

class ToolController extends Controller
{
    public function __invoke(Tool $tool): View
    {
        abort_unless($tool->status === ToolStatus::Published, 404);

        $tool->load('links');

        $criteria = $tool->criteria()->orderBy('criteria.sort_order')->get();

        $relatedArticles = Article::query()
            ->published()
            ->with('category')
            ->whereHas('comparisons.items', fn ($query) => $query->where('tool_id', $tool->getKey()))
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        $reviews = $this->editorialReviews($tool);

        $jsonLd = [[
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $tool->getTranslation('name', app()->getLocale()),
            'description' => strval($tool->getTranslation('description', app()->getLocale())),
            'url' => route('tools.show', $tool),
            'aggregateRating' => $tool->rating_avg !== null && $reviews !== []
                ? [
                    '@type' => 'AggregateRating',
                    'ratingValue' => (float) $tool->rating_avg,
                    'bestRating' => 10,
                    'worstRating' => 0,
                    'ratingCount' => count($reviews),
                    'reviewCount' => count($reviews),
                ]
                : null,
            'review' => $reviews !== [] ? $reviews : null,
        ]];

        return view('tool', [
            'tool' => $tool,
            'criteria' => $criteria,
            'relatedArticles' => $relatedArticles,
            'jsonLd' => $jsonLd,
        ]);
    }

    /** @return list<array<string, mixed>> */
    private function editorialReviews(Tool $tool): array
    {
        $locale = app()->getLocale();

        $items = ComparisonItem::query()
            ->where('tool_id', $tool->getKey())
            ->whereNotNull('score')
            ->with('comparison.article')
            ->orderBy('position')
            ->get();

        $reviews = [];

        foreach ($items as $item) {
            $verdict = strval($item->getTranslation('verdict', $locale));

            if ($verdict === '') {
                continue;
            }

            $article = $item->comparison?->article;

            $reviews[] = [
                '@type' => 'Review',
                'author' => ['@type' => 'Organization', 'name' => config('app.name')],
                'reviewRating' => [
                    '@type' => 'Rating',
                    'ratingValue' => (float) $item->score,
                    'bestRating' => 10,
                    'worstRating' => 0,
                ],
                'reviewBody' => $verdict,
                'url' => $article !== null && $article->published_at !== null
                    ? route('articles.show', $article)
                    : null,
            ];
        }

        return $reviews;
    }
}
