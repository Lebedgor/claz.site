<?php

use App\Actions\SyncComparisonScores;
use App\Enums\ArticleStatus;
use App\Enums\CriterionKind;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Criterion;
use App\Models\Tool;
use App\Models\ToolLink;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function seedPublicFixture(): array
{
    $category = Category::create([
        'name' => ['en' => 'AI tools'],
        'slug' => ['en' => 'ai-tools'],
    ]);

    $ease = Criterion::create([
        'name' => ['en' => 'Ease of use'],
        'kind' => CriterionKind::Score,
        'sort_order' => 10,
    ]);

    $free = Criterion::create([
        'name' => ['en' => 'Free plan'],
        'kind' => CriterionKind::Bool,
        'sort_order' => 60,
    ]);

    $toolA = Tool::create([
        'type' => ToolType::Ai,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'AI Helper'],
        'slug' => ['en' => 'ai-helper'],
        'vendor' => 'Acme',
        'description' => ['en' => 'Smart AI assistant.'],
        'rating_avg' => 8.4,
    ]);

    $toolA->criteria()->attach([
        $ease->getKey() => ['value' => 9],
        $free->getKey() => ['value' => true],
    ]);

    ToolLink::create([
        'tool_id' => $toolA->getKey(),
        'code' => 'ai-helper',
        'url' => 'https://example.com/ai-helper',
        'is_affiliate' => true,
        'anchor' => ['en' => 'Try AI Helper'],
    ]);

    $toolB = Tool::create([
        'type' => ToolType::Ai,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Content Bot'],
        'slug' => ['en' => 'content-bot'],
        'description' => ['en' => 'Content generator.'],
    ]);

    $toolB->criteria()->attach([
        $ease->getKey() => ['value' => 6],
    ]);

    $article = Article::create([
        'category_id' => $category->getKey(),
        'title' => ['en' => 'Best AI helpers compared'],
        'slug' => ['en' => 'best-ai-helpers'],
        'excerpt' => ['en' => 'Two AI helpers compared.'],
        'body_html' => ['en' => '<p>Intro text.</p>'],
        'status' => ArticleStatus::Published,
        'published_at' => now()->subDay(),
        'meta_title' => ['en' => 'Best AI helpers'],
        'meta_description' => ['en' => 'Comparison of AI helpers'],
    ]);

    $comparison = $article->comparisons()->create([
        'title' => ['en' => 'AI helpers head to head'],
        'intro' => ['en' => 'Quick overview'],
        'verdict' => ['en' => 'AI Helper wins'],
        'sort_order' => 0,
    ]);

    $comparison->items()->create([
        'tool_id' => $toolA->getKey(),
        'position' => 0,
        'score' => 8.4,
        'verdict' => ['en' => 'Best overall'],
    ]);

    $comparison->items()->create([
        'tool_id' => $toolB->getKey(),
        'position' => 1,
        'score' => 6.1,
        'verdict' => ['en' => 'Good value'],
    ]);

    app(SyncComparisonScores::class)->execute($article);

    $article->update([
        'body_html' => ['en' => '<p>Intro text.</p> [[comparison:'.$comparison->getKey().']]'],
    ]);

    return [$article, $comparison, $toolA, $toolB];
}

it('renders home with published articles and tools', function () {
    seedPublicFixture();

    $this->get('/')->assertOk()
        ->assertSee('Best AI helpers compared')
        ->assertSee('AI Helper');
});

it('renders category page and 404s on unknown slug', function () {
    seedPublicFixture();

    $this->get('/category/ai-tools')->assertOk()
        ->assertSee('Best AI helpers compared');

    $this->get('/category/unknown')->assertNotFound();
});

it('renders article page with comparison table and seo markup', function () {
    seedPublicFixture();

    $this->get('/articles/best-ai-helpers')->assertOk()
        ->assertSee('Best AI helpers compared')
        ->assertSee('AI Helper')
        ->assertSee('Content Bot')
        ->assertSee('Ease of use')
        ->assertSee('Best overall')
        ->assertSee('/go/ai-helper')
        ->assertSee('"@type":"Article"', false)
        ->assertSee('"@type":"ItemList"', false)
        ->assertSee('hreflang="en"', false);
});

it('appends unreferenced comparison blocks to the article body', function () {
    [$article, $comparison] = seedPublicFixture();

    $extra = $article->comparisons()->create([
        'title' => ['en' => 'Editor picks'],
        'sort_order' => 1,
    ]);

    $this->get('/articles/best-ai-helpers')->assertOk()
        ->assertSee('Editor picks');

    expect($comparison->getKey())->toBeInt();
});

it('404s on draft article', function () {
    [$article] = seedPublicFixture();

    $article->update(['status' => ArticleStatus::Draft]);

    $this->get('/articles/best-ai-helpers')->assertNotFound();
});

it('renders tools index with type filter', function () {
    seedPublicFixture();

    $this->get('/tools?type=ai')->assertOk()
        ->assertSee('AI Helper');

    $this->get('/tools?type=plugin')->assertOk()
        ->assertSee('No tools found.');
});

it('renders tool page with criteria and 404s on unknown slug', function () {
    seedPublicFixture();

    $this->get('/tools/ai-helper')->assertOk()
        ->assertSee('Ease of use')
        ->assertSee('Try AI Helper')
        ->assertSee('Mentioned in articles');

    $this->get('/tools/unknown')->assertNotFound();
});
