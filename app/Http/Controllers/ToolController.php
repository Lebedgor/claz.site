<?php

namespace App\Http\Controllers;

use App\Enums\ToolStatus;
use App\Models\Article;
use App\Models\Tool;
use Illuminate\Contracts\View\View;

class ToolController extends Controller
{
    public function __invoke(Tool $tool): View
    {
        abort_unless($tool->status === ToolStatus::Published, 404);

        $tool->load('links');

        $criteria = $tool->criteria()->orderBy('criteria.sort_order')->get();

        $relatedArticles = Article::query()
            ->published()
            ->with('category')
            ->whereHas('comparisons.items', fn ($query) => $query->where('tool_id', $tool->getKey()))
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('tool', [
            'tool' => $tool,
            'criteria' => $criteria,
            'relatedArticles' => $relatedArticles,
        ]);
    }
}
