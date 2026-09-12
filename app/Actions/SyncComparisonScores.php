<?php

namespace App\Actions;

use App\Models\Article;
use App\Models\ComparisonItem;
use App\Models\ComparisonScore;
use App\Models\ToolCriterion;

class SyncComparisonScores
{
    public function execute(Article $article): void
    {
        foreach ($article->comparisons as $comparison) {
            foreach ($comparison->items as $item) {
                $this->syncItem($item);
            }
        }
    }

    private function syncItem(ComparisonItem $item): void
    {
        $tool = $item->tool;

        if ($tool === null) {
            ComparisonScore::query()
                ->where('comparison_item_id', $item->getKey())
                ->delete();

            return;
        }

        $live = ToolCriterion::query()
            ->where('tool_id', $tool->getKey())
            ->with('criterion')
            ->get()
            ->mapWithKeys(function (ToolCriterion $row): array {
                $criterion = $row->criterion;

                if ($criterion === null) {
                    return [];
                }

                return [
                    (int) $row->criteria_id => [
                        'value' => $row->value,
                        'sort_order' => (int) $criterion->sort_order,
                    ],
                ];
            });

        ComparisonScore::query()
            ->where('comparison_item_id', $item->getKey())
            ->whereNotIn('criteria_id', $live->keys()->all())
            ->delete();

        $current = ComparisonScore::query()
            ->where('comparison_item_id', $item->getKey())
            ->get()
            ->mapWithKeys(fn (ComparisonScore $score): array => [(int) $score->criteria_id => $score]);

        foreach ($live as $criteriaId => $payload) {
            $score = $current->get($criteriaId);

            if ($score === null) {
                ComparisonScore::create([
                    'comparison_item_id' => $item->getKey(),
                    'criteria_id' => $criteriaId,
                    'value' => $payload['value'],
                    'sort_order' => $payload['sort_order'],
                ]);

                continue;
            }

            if ($score->value !== $payload['value']) {
                $score->update(['value' => $payload['value']]);
            }
        }
    }
}
