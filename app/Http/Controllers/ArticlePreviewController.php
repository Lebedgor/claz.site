<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticlePage;
use Illuminate\Contracts\View\View;

class ArticlePreviewController extends Controller
{
    public function __invoke(Article $article, ArticlePage $page): View
    {
        $view = $page($article);
        $view->with('robots', 'noindex,nofollow');

        return $view;
    }
}
