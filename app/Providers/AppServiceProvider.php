<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar el servicio de búsqueda semántica
        $this->app->singleton(\App\Services\SemanticSearchService::class, function ($app) {
            return new \App\Services\SemanticSearchService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Compartir datos globales con todas las vistas
        View::composer('*', function ($view) {
            $view->with('appName', config('app.name'));
        });

        // IDs de favoritos del usuario (una sola consulta por request)
        View::composer(['components.product-card', 'products.show'], function ($view) {
            $ids = [];
            if (auth()->check()) {
                static $cached = null;
                if ($cached === null) {
                    $cached = auth()->user()->wishlistProductIds();
                }
                $ids = $cached;
            }
            $view->with('wishlistIds', $ids);
        });
    }
}
