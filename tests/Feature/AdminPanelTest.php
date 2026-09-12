<?php

use App\Enums\ArticleStatus;
use App\Enums\CriterionKind;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Article;
use App\Models\Category;
use App\Models\Criterion;
use App\Models\Tag;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('renders admin resources for the admin', function () {
    actingAs(User::factory()->create());

    Tool::create([
        'type' => ToolType::Plugin,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Row Tool'],
        'slug' => ['en' => 'row-tool'],
    ]);

    Category::create([
        'name' => ['en' => 'Row Category'],
        'slug' => ['en' => 'row-category'],
    ]);

    Article::create([
        'title' => ['en' => 'Row Article'],
        'slug' => ['en' => 'row-article'],
    ]);

    $this->get('/admin')->assertOk();
    $this->get('/admin/tools')->assertOk();
    $this->get('/admin/tools/create')->assertOk();
    $this->get('/admin/criteria')->assertOk();
    $this->get('/admin/categories')->assertOk();
    $this->get('/admin/tags')->assertOk();
    $this->get('/admin/articles')->assertOk();
    $this->get('/admin/articles/create')->assertOk();
    $this->get('/admin/media-library')->assertOk();
});

it('renders tool edit page with attached criteria values', function () {
    actingAs(User::factory()->create());

    $tool = Tool::create([
        'type' => ToolType::Plugin,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Yoast SEO'],
        'slug' => ['en' => 'yoast-seo'],
        'vendor' => 'Yoast',
    ]);

    $criterion = Criterion::create([
        'name' => ['en' => 'Ease of use'],
        'kind' => CriterionKind::Score,
    ]);

    $tool->criteria()->attach($criterion->getKey(), ['value' => 8]);

    $this->get('/admin/tools/'.$tool->getKey().'/edit')->assertOk()
        ->assertDontSee('\\" image\\"', false);
    $this->get('/admin/criteria/'.$criterion->getKey().'/edit')->assertOk();
});

it('renders article edit page with comparison blocks', function () {
    actingAs(User::factory()->create());

    $tool = Tool::create([
        'type' => ToolType::Plugin,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Yoast SEO'],
        'slug' => ['en' => 'yoast-seo'],
    ]);

    $article = Article::create([
        'title' => ['en' => 'Best AI chatbots in 2026'],
        'slug' => ['en' => 'best-ai-chatbots'],
        'body_html' => ['en' => '<p>Intro</p>'],
        'status' => ArticleStatus::Draft,
    ]);

    $comparison = $article->comparisons()->create([
        'title' => ['en' => 'Head to head'],
        'sort_order' => 0,
    ]);

    $comparison->items()->create([
        'tool_id' => $tool->getKey(),
        'position' => 0,
        'score' => 7.5,
    ]);

    $this->get('/admin/articles/'.$article->getKey().'/edit')->assertOk();
});

it('renders article edit page with category and tags', function () {
    actingAs(User::factory()->create());

    $category = Category::create([
        'name' => ['en' => 'AI tools'],
        'slug' => ['en' => 'ai-tools'],
    ]);

    $tag = Tag::create([
        'name' => ['en' => 'chatbots'],
        'slug' => ['en' => 'chatbots'],
    ]);

    $article = Article::create([
        'category_id' => $category->getKey(),
        'title' => ['en' => 'Best AI chatbots in 2026'],
        'slug' => ['en' => 'best-ai-chatbots'],
        'body_html' => ['en' => '<p>Intro</p>'],
        'status' => ArticleStatus::Draft,
    ]);

    $article->tags()->attach($tag->getKey());

    $this->get('/admin/articles/'.$article->getKey().'/edit')->assertOk();

    $this->get('/admin/tags/'.$tag->getKey().'/edit')->assertOk();
});
