<?php

namespace App\Http\Controllers;

use App\Enums\ToolStatus;
use App\Models\Comparison;
use App\Models\ComparisonItem;
use App\Models\ComparisonScore;
use App\Models\Criterion;
use App\Models\Tool;
use App\Models\ToolCriterion;
use App\Models\VsPage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class VsPageController extends Controller
{
    public function __invoke(string $slugs): View|RedirectResponse
    {
        [$aSlug, $bSlug] = array_pad(explode('-vs-', $slugs, 2), 2, null);

        abort_if(! is_string($aSlug) || ! is_string($bSlug) || $aSlug === '' || $bSlug === '' || $aSlug === $bSlug, 404);

        $locale = app()->getLocale();

        $toolA = $this->findPublishedTool($aSlug, $locale);
        $toolB = $this->findPublishedTool($bSlug, $locale);

        abort_if($toolA === null || $toolB === null, 404);

        [$first, $second] = $toolA->getKey() <= $toolB->getKey() ? [$toolA, $toolB] : [$toolB, $toolA];

        if ($first->getKey() !== $toolA->getKey()) {
            return redirect()->route('vs.show', ['slugs' => $this->canonicalSlugs($first, $second, $locale)], 301);
        }

        $editorial = VsPage::query()
            ->where('tool_a_id', $first->getKey())
            ->where('tool_b_id', $second->getKey())
            ->first();

        abort_if($editorial !== null && ! $editorial->is_published, 404);

        $comparison = $this->buildComparison($first, $second, $editorial, $locale);

        $jsonLd = [[
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $comparison->getTranslation('title', $locale),
            'url' => url("/vs/{$slugs}"),
            'itemListElement' => $comparison->items->map(function (ComparisonItem $item, int $index) use ($locale): array {
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
        ]];

        return view('vs', [
            'first' => $first,
            'second' => $second,
            'comparison' => $comparison,
            'intro' => $editorial !== null && filled($editorial->getTranslation('intro', $locale))
                ? $editorial->getTranslation('intro', $locale)
                : __('site.vs.intro', ['a' => $first->getTranslation('name', $locale), 'b' => $second->getTranslation('name', $locale)]),
            'conclusion' => $editorial !== null && filled($editorial->getTranslation('conclusion', $locale))
                ? $editorial->getTranslation('conclusion', $locale)
                : __('site.vs.conclusion'),
            'jsonLd' => $jsonLd,
        ]);
    }

    private function findPublishedTool(string $slug, string $locale): ?Tool
    {
        return Tool::query()
            ->where("slug->{$locale}", $slug)
            ->where('status', ToolStatus::Published->value)
            ->first();
    }

    private function canonicalSlugs(Tool $first, Tool $second, string $locale): string
    {
        return $first->getTranslation('slug', $locale).'-vs-'.$second->getTranslation('slug', $locale);
    }

    /** @return Collection<int, ComparisonScore> */
    private function transientScores(Tool $tool): Collection
    {
        $values = ToolCriterion::query()
            ->where('tool_id', $tool->getKey())
            ->pluck('value', 'criteria_id');

        return Criterion::query()
            ->whereIn('id', $values->keys())
            ->orderBy('sort_order')
            ->get()
            ->map(function (Criterion $criterion) use ($values): ComparisonScore {
                $score = new ComparisonScore([
                    'criteria_id' => (int) $criterion->getKey(),
                    'value' => $values->get($criterion->getKey()),
                ]);
                $score->setRelation('criterion', $criterion);

                return $score;
            });
    }

    private function buildComparison(Tool $first, Tool $second, ?VsPage $editorial, string $locale): Comparison
    {
        $items = collect([$first, $second])->map(function (Tool $tool, int $index): ComparisonItem {
            $item = new ComparisonItem([
                'position' => $index,
                'score' => $tool->rating_avg,
            ]);
            $item->setRelation('tool', $tool);
            $item->setRelation('scores', $this->transientScores($tool));

            return $item;
        });

        $intro = $editorial !== null && filled($editorial->getTranslation('intro', $locale))
            ? $editorial->getTranslation('intro', $locale)
            : __('site.vs.intro', ['a' => $first->getTranslation('name', $locale), 'b' => $second->getTranslation('name', $locale)]);

        $verdict = $editorial !== null && filled($editorial->getTranslation('conclusion', $locale))
            ? $editorial->getTranslation('conclusion', $locale)
            : __('site.vs.conclusion');

        $comparison = new Comparison([
            'title' => [$locale => $first->getTranslation('name', $locale).' vs '.$second->getTranslation('name', $locale)],
            'intro' => [$locale => $intro],
            'verdict' => [$locale => $verdict],
            'sort_order' => 0,
        ]);
        $comparison->setRelation('items', $items);

        return $comparison;
    }
}
