<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Contracts\View\View;

class ArticlePage
{
    public function __construct(
        private readonly ArticleRenderer $renderer,
        private readonly FaqExtractor $faqExtractor,
    ) {}

    public function __invoke(Article $article): View
    {
        $locale = app()->getLocale();

        $article->load([
            'category',
            'tags',
            'comparisons.items.tool.links',
            'comparisons.items.scores.criterion',
        ]);

        $title = $article->getTranslation('title', $locale);
        $body = $this->renderer->render($article);
        $coverUrl = $article->cover_url;

        $description = strval($article->getTranslation('meta_description', $locale) ?: $article->getTranslation('excerpt', $locale));

        $jsonLd = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $title,
                'description' => $description,
                'image' => $coverUrl,
                'datePublished' => $article->published_at?->toIso8601String(),
                'dateModified' => $article->updated_at?->toIso8601String(),
                'mainEntityOfPage' => route('articles.show', $article),
                'author' => ['@type' => 'Organization', 'name' => config('app.name')],
                'publisher' => ['@type' => 'Organization', 'name' => config('app.name')],
                'wordCount' => str_word_count(strip_tags($body)),
                'inLanguage' => $locale,
                'articleSection' => $article->category?->getTranslation('name', $locale),
                'keywords' => $article->tags->map(fn ($tag) => $tag->getTranslation('name', $locale))->all(),
            ],
        ];

        $faq = $this->faqExtractor->extract($article->getTranslation('body_html', $locale));

        if ($faq !== []) {
            $jsonLd[] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(fn (array $pair): array => [
                    '@type' => 'Question',
                    'name' => $pair['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $pair['answer'],
                    ],
                ], $faq),
            ];
        }

        foreach ($article->comparisons as $comparison) {
            $jsonLd[] = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => $comparison->getTranslation('title', $locale),
                'itemListElement' => $comparison->items->map(function ($item, $index) use ($locale): array {
                    $review = $item->score !== null ? [
                        '@type' => 'Review',
                        'author' => ['@type' => 'Organization', 'name' => config('app.name')],
                        'reviewRating' => [
                            '@type' => 'Rating',
                            'ratingValue' => (float) $item->score,
                            'bestRating' => 10,
                            'worstRating' => 0,
                        ],
                        'reviewBody' => strval($item->getTranslation('verdict', $locale)) ?: null,
                    ] : null;

                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'item' => [
                            '@type' => 'Product',
                            'name' => $item->tool?->getTranslation('name', $locale),
                            'url' => $item->tool !== null ? route('tools.show', $item->tool) : null,
                            'review' => $review,
                        ],
                    ];
                })->all(),
            ];
        }

        return view('article', [
            'article' => $article,
            'body' => $body,
            'jsonLd' => $jsonLd,
            'robots' => null,
        ]);
    }
}
