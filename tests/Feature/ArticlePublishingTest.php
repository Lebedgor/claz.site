<?php

use App\Enums\ArticleStatus;
use App\Filament\Resources\ArticleResource\Pages\EditArticle;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

function seedDraftArticle(): Article
{
    return Article::create([
        'title' => ['en' => 'Publishable article'],
        'slug' => ['en' => 'publishable-article'],
        'body_html' => ['en' => '<p>Body</p>'],
        'status' => ArticleStatus::Draft,
    ]);
}

it('publishes a draft article via the header action', function () {
    actingAs(User::factory()->create());
    $article = seedDraftArticle();

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->callAction('publish');

    $article->refresh();

    expect($article->status)->toBe(ArticleStatus::Published)
        ->and($article->published_at)->not->toBeNull();
});

it('unpublishes an article back to draft', function () {
    actingAs(User::factory()->create());
    $article = seedDraftArticle();
    $article->update(['status' => ArticleStatus::Published, 'published_at' => now()]);

    Livewire::test(EditArticle::class, ['record' => $article->getKey()])
        ->callAction('unpublish');

    expect($article->refresh()->status)->toBe(ArticleStatus::Draft);
});
