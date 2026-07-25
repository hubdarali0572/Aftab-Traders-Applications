<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

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
        Vite::prefetch(concurrency: 3);

        if ($this->shouldForceHttps()) {
            URL::forceScheme('https');
        }

        Relation::enforceMorphMap([
            'user' => 'App\Models\User',
            'brand' => 'App\Models\Brand',
            'product-category' => 'App\Models\ProductCategory',
            'Unit' => 'App\Models\Unit',
            'product' => 'App\Models\Product',
            'product-selling-price' => 'App\Models\ProductSellingPrice',
            'warehouse' => 'App\Models\Warehouse',
            // Add other models here as needed
        ]);
    }

    private function shouldForceHttps(): bool
    {
        if (filter_var(config('app.force_https'), FILTER_VALIDATE_BOOL)) {
            return true;
        }

        return str_starts_with((string) config('app.url'), 'https://');
    }
}
