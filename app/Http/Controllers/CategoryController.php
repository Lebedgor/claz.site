<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function __invoke(Category $category): View
    {
        $articles = Article::query()
            ->published()
            ->with('category')
            ->where('category_id', $category->getKey())
            ->orderByDesc('published_at')
            ->paginate(9);

        return view('category', [
            'category' => $category,
            'articles' => $articles,
        ]);
    }
}
