<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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
        Gate::define('viewHorizon', fn (User $user): bool => true);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('components.layouts.public', function ($view): void {
            $view->with('navCategories', Category::query()->orderBy('sort_order')->limit(6)->get());
        });
    }
}
