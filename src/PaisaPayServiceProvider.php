<?php

namespace TufikHasan\PaisaPay;

use Illuminate\Support\ServiceProvider;

class PaisaPayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/paisa.php',
            'paisa'
        );

        // Register the PaymentService
        $this->app->singleton(\TufikHasan\PaisaPay\Services\PaymentService::class, function ($app) {
            return new \TufikHasan\PaisaPay\Services\PaymentService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/paisa.php' => config_path('paisa.php'),
        ], 'paisa-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'paisa-migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
    }
}
