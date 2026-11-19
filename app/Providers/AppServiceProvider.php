<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();

        View::composer(['layouts.app', 'layouts.navigation', 'cart.*', 'product.*', 'home'], function ($view) {
            $cartCount = 0;
            try {
                if (auth()->check()) {
                    $cartCount = (int) \App\Models\CartItem::where('user_id', auth()->id())->sum('quantity');
                }
            } catch (\Throwable $e) {
                $cartCount = 0;
            }
            $view->with('cartCount', $cartCount);
        });
    }
}
