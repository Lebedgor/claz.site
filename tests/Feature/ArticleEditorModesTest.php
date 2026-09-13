<?php

use App\Filament\Resources\ArticleResource\Pages\EditArticle;
use App\Models\Article;
use App\Models\User;
use Database\Seeders\WooCommerceReviewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('hydrates the html editor from the record and saves without touching the markup', function () {
    actingAs(User::factory()->create());
    $this->seed(WooCommerceReviewsSeeder::class);
    $article = Article::query()->where('slug->en', 'best-product-review-plugins-for-woocommerce')->first();
    $originalBody = $article->getTranslation('body_html', 'en');

    $component = Livewire::test(EditArticle::class, ['record' => $article->getKey()]);
    $data = $component->instance()->data;

    expect($data['editor_mode'])->toBe('html')
        ->and(is_string($data['body_html_src'] ?? null))->toBeTrue()
        ->and($data['body_html_src'])->toContain('ex-nav-row');

    $component->call('save');
    $component->assertHasNoErrors();

    $article->refresh();

    expect($article->getTranslation('body_html', 'en'))->toBe($originalBody)
        ->and($article->reading_time)->toBeGreaterThan(0)
        ->and($article->editor_mode->value)->toBe('html');
});

it('transfers the body between editors on mode switches', function () {
    actingAs(User::factory()->create());
    $this->seed(WooCommerceReviewsSeeder::class);
    $article = Article::query()->where('slug->en', 'best-product-review-plugins-for-woocommerce')->first();

    $component = Livewire::test(EditArticle::class, ['record' => $article->getKey()]);

    $component->set('data.editor_mode', 'tiptap');
    expect(filled($component->instance()->data['body_tiptap'] ?? null))->toBeTrue();

    $component->set('data.editor_mode', 'html');

    $source = $component->instance()->data['body_html_src'] ?? null;

    expect(is_string($source))->toBeTrue()
        ->and(str_starts_with($source, '{"type"'))->toBeFalse();

    $component->call('save');
    $component->assertHasNoErrors();

    $article->refresh();

    expect(is_string($article->getTranslation('body_html', 'en')))->toBeTrue()
        ->and(str_starts_with($article->getTranslation('body_html', 'en'), '{"type"'))->toBeFalse();
});

it('saves a tip tap body as html', function () {
    actingAs(User::factory()->create());
    $this->seed(WooCommerceReviewsSeeder::class);
    $article = Article::query()->where('slug->en', 'best-product-review-plugins-for-woocommerce')->first();

    $component = Livewire::test(EditArticle::class, ['record' => $article->getKey()]);
    $component->set('data.editor_mode', 'tiptap');
    $component->call('save');
    $component->assertHasNoErrors();

    $article->refresh();

    $body = $article->getTranslation('body_html', 'en');

    expect(is_string($body))->toBeTrue()
        ->and(str_starts_with($body, '{"type"'))->toBeFalse();
});
