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
            __DIR__ . '/../config/paisapay.php',
            'paisapay'
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
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'paisapay');

        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/paisapay.php' => config_path('paisapay.php'),
        ], 'paisapay-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'paisapay-migrations');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/paisapay'),
        ], 'paisapay-views');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
    }
}
