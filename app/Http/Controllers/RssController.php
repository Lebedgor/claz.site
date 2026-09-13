<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleRenderer;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class RssController extends Controller
{
    public function __invoke(): Response
    {
        $renderer = app(ArticleRenderer::class);

        $articles = Cache::remember('rss-articles', 3600, fn (): array => Article::query()
            ->published()
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(20)
            ->get()
            ->map(fn (Article $article): array => [
                'title' => $article->getTranslation('title', app()->getLocale()),
                'url' => route('articles.show', $article),
                'description' => strval($article->getTranslation('excerpt', app()->getLocale()) ?: strip_tags(strval($article->getTranslation('body_html', app()->getLocale())))),
                'date' => strval($article->published_at?->toRssString() ?? now()->toRssString()),
                'category' => $article->category?->getTranslation('name', app()->getLocale()),
                'cover' => $article->cover_url,
                'content' => $renderer->render($article),
            ])
            ->all());

        return response()
            ->view('rss', ['articles' => $articles])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
