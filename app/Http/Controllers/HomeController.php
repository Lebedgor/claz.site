<?php

namespace App\Http\Controllers;

use App\Enums\ToolStatus;
use App\Models\Article;
use App\Models\Tool;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $articles = Article::query()
            ->published()
            ->with('category')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $tools = Tool::query()
            ->where('status', ToolStatus::Published->value)
            ->orderByRaw('rating_avg desc nulls last')
            ->limit(8)
            ->get();

        return view('home', [
            'articles' => $articles,
            'tools' => $tools,
            'jsonLd' => [[
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => config('app.name'),
                'url' => url('/'),
            ]],
        ]);
    }
}
