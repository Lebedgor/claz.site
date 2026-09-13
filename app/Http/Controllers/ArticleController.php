<?php

namespace App\Http\Controllers;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Services\ArticlePage;
use Illuminate\Contracts\View\View;

class ArticleController extends Controller
{
    public function __invoke(Article $article, ArticlePage $page): View
    {
        abort_unless($article->status === ArticleStatus::Published, 404);
        abort_unless($article->published_at !== null && $article->published_at->lte(now()), 404);

        return $page($article);
    }
}
