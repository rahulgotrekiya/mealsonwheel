<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The category list is part of the site chrome, so it is bound once
        // here rather than fetched again by every controller.
        View::composer('partials.storefront.*', function ($view) {
            $view->with('navCategories', Category::orderBy('name')->get());
        });
    }
}
