<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Order;
use App\Policies\OrderPolicy;
use App\Support\PanelMenu;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Order::class, OrderPolicy::class);

        // The category list is part of the site chrome, so it is bound once
        // here rather than fetched again by every controller.
        View::composer('partials.storefront.*', function ($view) {
            $view->with('navCategories', Category::orderBy('name')->get());
        });

        // The staff sidebar is one component; only its entries differ by role.
        View::composer('partials.panel.sidebar', function ($view) {
            $view->with('panelMenu', PanelMenu::for(auth()->user()));
        });
    }
}
