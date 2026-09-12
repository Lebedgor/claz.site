<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('components.layouts.public', function ($view): void {
            $view->with('navCategories', Category::query()->orderBy('sort_order')->limit(6)->get());
        });
    }
}
