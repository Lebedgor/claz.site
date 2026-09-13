<?php

namespace App\Services;

use App\Enums\BannerPlacement;
use App\Models\Article;
use App\Models\Comparison;

class ArticleRenderer
{
    public function render(Article $article, string $locale = 'en'): string
    {
        $body = strval($article->getTranslation('body_html', $locale));

        $referencedIds = [];

        $html = (string) preg_replace_callback(
            '/\[\[comparison:(\d+)\]\]/',
            function (array $matches) use ($article, &$referencedIds): string {
                $id = (int) $matches[1];
                $referencedIds[] = $id;

                $comparison = $article->comparisons->firstWhere('id', $id);

                if ($comparison === null) {
                    return '';
                }

                return view('components.comparison-table', ['comparison' => $comparison])->render();
            },
            $body,
        );

        $extra = $article->comparisons
            ->reject(fn (Comparison $comparison): bool => in_array($comparison->getKey(), $referencedIds, true))
            ->map(fn (Comparison $comparison): string => view('components.comparison-table', ['comparison' => $comparison])->render())
            ->implode('');

        $banner = view('components.banner', ['placement' => BannerPlacement::InArticle->value])->render();

        $html = (string) preg_replace('/<video\b(?![^>]*\bmuted\b)/i', '<video muted', $html.$extra.$banner);

        return $html;
    }
}
