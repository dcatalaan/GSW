<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Directiva personalizada para formateo de moneda
        Blade::directive('currency', function ($amount) {
            return "<?php echo '$' . number_format($amount, 2); ?>";
        });

        // Directiva para verificar si una ruta está activa
        Blade::if('activeRoute', function ($routeName) {
            return request()->routeIs($routeName);
        });
    }
}
