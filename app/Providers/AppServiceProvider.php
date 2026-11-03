<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Settings;
use App\Observers\PublicMenuObserver;
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
        Category::observe(PublicMenuObserver::class);
        Product::observe(PublicMenuObserver::class);
        Settings::observe(PublicMenuObserver::class);
    }
}
