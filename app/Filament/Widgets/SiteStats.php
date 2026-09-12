<?php

namespace App\Filament\Widgets;

use App\Enums\ArticleStatus;
use App\Enums\CommentStatus;
use App\Models\Article;
use App\Models\ClickEvent;
use App\Models\Comment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteStats extends BaseWidget
{
    protected static ?int $sort = -10;

    protected function getStats(): array
    {
        return [
            Stat::make('Pending comments', Comment::query()->where('status', CommentStatus::Pending->value)->count()),
            Stat::make('Published articles', Article::query()->where('status', ArticleStatus::Published->value)->count()),
            Stat::make('Clicks (7 days)', ClickEvent::query()->where('created_at', '>=', now()->subDays(7))->count()),
        ];
    }
}
