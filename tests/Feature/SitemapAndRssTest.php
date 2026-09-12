<?php

use App\Enums\ArticleStatus;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Models\Article;
use App\Models\Tool;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->article = Article::create([
        'title' => ['en' => 'Seeded article'],
        'slug' => ['en' => 'seeded-article'],
        'body_html' => ['en' => '<p>Body</p>'],
        'status' => ArticleStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    Tool::create([
        'type' => ToolType::Ai,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Alpha AI'],
        'slug' => ['en' => 'alpha-ai'],
    ]);

    Tool::create([
        'type' => ToolType::Ai,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Beta Bot'],
        'slug' => ['en' => 'beta-bot'],
    ]);
});

it('renders sitemap with articles, tools, and vs pairs', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk()
        ->assertSee('http://127.0.0.1:8000/articles/seeded-article')
        ->assertSee('http://127.0.0.1:8000/tools/alpha-ai')
        ->assertSee('http://127.0.0.1:8000/vs/alpha-ai-vs-beta-bot');
});

it('excludes drafts from sitemap', function () {
    $this->article->update(['status' => ArticleStatus::Draft]);

    $this->get('/sitemap.xml')->assertOk()
        ->assertDontSee('articles/seeded-article');
});

it('renders rss with published articles', function () {
    $this->get('/rss.xml')->assertOk()
        ->assertSee('Seeded article')
        ->assertSee('<rss version="2.0"', false);
});
