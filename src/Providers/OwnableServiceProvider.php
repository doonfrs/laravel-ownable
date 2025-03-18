<?php

namespace Trinavo\Ownable\Providers;

use Illuminate\Support\ServiceProvider;

class OwnableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot()
    {

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Publish configuration (optional)
        $this->publishes([
            __DIR__ . '/../../config/ownable.php' => config_path('ownable.php'),
        ], 'config');
    }

    /**
     * Register any application services.
     */
    public function register()
    {
        // Merge package config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/ownable.php',
            'ownable'
        );
    }
}
