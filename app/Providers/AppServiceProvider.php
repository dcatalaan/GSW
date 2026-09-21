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
    }
}
