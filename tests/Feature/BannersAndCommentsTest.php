<?php

use App\Enums\ArticleStatus;
use App\Enums\CommentStatus;
use App\Enums\CriterionKind;
use App\Enums\ToolStatus;
use App\Enums\ToolType;
use App\Livewire\ArticleComments;
use App\Models\Article;
use App\Models\Banner;
use App\Models\Comment;
use App\Models\Criterion;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

function seedCommentFixture(): Article
{
    $article = Article::create([
        'title' => ['en' => 'Commentable article'],
        'slug' => ['en' => 'commentable-article'],
        'body_html' => ['en' => '<p>Body</p>'],
        'status' => ArticleStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    return $article;
}

it('shows approved comments and hides pending ones', function () {
    $article = seedCommentFixture();

    Comment::create([
        'article_id' => $article->getKey(),
        'name' => 'Anna',
        'email' => 'anna@example.com',
        'body' => 'Nice article',
        'status' => CommentStatus::Approved,
    ]);

    Comment::create([
        'article_id' => $article->getKey(),
        'name' => 'Spam Bot',
        'email' => 'spam@example.com',
        'body' => 'Pending spam text',
        'status' => CommentStatus::Pending,
    ]);

    $this->get('/articles/commentable-article')->assertOk()
        ->assertSee('Nice article')
        ->assertDontSee('Pending spam text');
});

it('creates a pending comment through the livewire form', function () {
    $article = seedCommentFixture();

    livewire(ArticleComments::class, ['article' => $article])
        ->set('name', 'Jane')
        ->set('email', 'jane@example.com')
        ->set('body', 'Great comparison!')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('comments', [
        'article_id' => $article->getKey(),
        'name' => 'Jane',
        'status' => 'pending',
        'body' => 'Great comparison!',
    ]);
});

it('sanitizes html in comment bodies', function () {
    $article = seedCommentFixture();

    livewire(ArticleComments::class, ['article' => $article])
        ->set('name', 'Hacker')
        ->set('email', 'hacker@example.com')
        ->set('body', '<p>Hi <script>alert(1)</script><a href="https://example.com">link</a></p>')
        ->call('submit')
        ->assertHasNoErrors();

    $comment = Comment::query()->where('article_id', $article->getKey())->first();

    expect($comment?->body)->toContain('link')
        ->and($comment?->body)->not->toContain('script');
});

it('silently drops honeypot submissions', function () {
    $article = seedCommentFixture();

    livewire(ArticleComments::class, ['article' => $article])
        ->set('name', 'Bot')
        ->set('email', 'bot@example.com')
        ->set('body', 'I am a bot')
        ->set('website', 'https://spam.example')
        ->call('submit');

    $this->assertDatabaseCount('comments', 0);
});

it('rate limits comment submissions', function () {
    $article = seedCommentFixture();

    foreach (range(1, 4) as $i) {
        livewire(ArticleComments::class, ['article' => $article])
            ->set('name', 'Author '.$i)
            ->set('email', 'author'.$i.'@example.com')
            ->set('body', 'Comment number '.$i)
            ->call('submit');
    }

    $this->assertDatabaseCount('comments', 3);
});

it('moderates comments in the admin', function () {
    actingAs(User::factory()->create());

    $article = seedCommentFixture();

    $comment = Comment::create([
        'article_id' => $article->getKey(),
        'name' => 'Moderated',
        'email' => 'm@example.com',
        'body' => 'Please approve',
        'status' => CommentStatus::Pending,
    ]);

    $this->get('/admin/comments')->assertOk();

    $comment->update(['status' => CommentStatus::Approved]);

    expect($comment->refresh()->status)->toBe(CommentStatus::Approved);
});

it('renders active banner and hides expired ones', function () {
    $article = seedCommentFixture();

    Tool::create([
        'type' => ToolType::Plugin,
        'status' => ToolStatus::Published,
        'name' => ['en' => 'Some Tool'],
        'slug' => ['en' => 'some-tool'],
    ]);

    Criterion::create([
        'name' => ['en' => 'Ease of use'],
        'kind' => CriterionKind::Score,
    ]);

    Banner::create([
        'placement' => 'header',
        'title' => 'Active banner',
        'html' => '<div>Buy my thing</div>',
        'is_active' => true,
    ]);

    Banner::create([
        'placement' => 'header',
        'title' => 'Expired banner',
        'html' => '<div>Old campaign</div>',
        'is_active' => true,
        'starts_at' => now()->subDays(10),
        'ends_at' => now()->subDay(),
    ]);

    $this->get('/')->assertOk()
        ->assertSee('Buy my thing')
        ->assertDontSee('Old campaign');
});
