<?php

use App\Actions\SyncComparisonScores;
use App\Enums\ArticleStatus;
use App\Enums\CriterionKind;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Article;
use App\Models\ComparisonScore;
use App\Models\Criterion;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('syncs comparison scores with live tool data', function () {
    $ease = Criterion::create(['name' => ['en' => 'Ease of use'], 'kind' => CriterionKind::Score, 'sort_order' => 10]);
    $price = Criterion::create(['name' => ['en' => 'Pricing'], 'kind' => CriterionKind::Score, 'sort_order' => 20]);
    $free = Criterion::create(['name' => ['en' => 'Free plan'], 'kind' => CriterionKind::Bool, 'sort_order' => 60]);

    $tool = Tool::create([
        'type' => ToolType::Plugin,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Yoast SEO'],
        'slug' => ['en' => 'yoast-seo'],
    ]);

    $tool->criteria()->attach([
        $ease->getKey() => ['value' => 9],
        $price->getKey() => ['value' => 7],
    ]);

    $article = Article::create([
        'title' => ['en' => 'Best SEO plugins'],
        'slug' => ['en' => 'best-seo-plugins'],
        'status' => ArticleStatus::Draft,
    ]);

    $comparison = $article->comparisons()->create([
        'title' => ['en' => 'SEO plugins compared'],
        'sort_order' => 0,
    ]);

    $item = $comparison->items()->create([
        'tool_id' => $tool->getKey(),
        'position' => 0,
        'score' => 8,
    ]);

    ComparisonScore::create([
        'comparison_item_id' => $item->getKey(),
        'criteria_id' => $free->getKey(),
        'value' => true,
    ]);

    app(SyncComparisonScores::class)->execute($article);

    $scores = ComparisonScore::query()->where('comparison_item_id', $item->getKey())->get();

    $easeScore = $scores->firstWhere('criteria_id', $ease->getKey());
    $priceScore = $scores->firstWhere('criteria_id', $price->getKey());
    $freeScore = $scores->firstWhere('criteria_id', $free->getKey());

    expect($scores)->toHaveCount(2)
        ->and($easeScore?->value)->toBe('9')
        ->and($priceScore?->value)->toBe('7')
        ->and($freeScore)->toBeNull();
});
