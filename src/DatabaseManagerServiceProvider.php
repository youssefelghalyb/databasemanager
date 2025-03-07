<?php

namespace YoussefElghaly\DatabaseManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class DatabaseManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Load and publish configurations
        $this->mergeConfigFrom(__DIR__.'/../config/databasemanager.php', 'databasemanager');
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/databasemanager.php' => config_path('databasemanager.php'),
            ], 'databasemanager-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/databasemanager'),
            ], 'databasemanager-views');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'databasemanager-migrations');
        }

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                \YoussefElghaly\DatabaseManager\Console\Commands\ModuleMigrationStatus::class,
            ]);
        }

        // Load routes with configurable prefix and middleware
        $this->loadRoutes();
        
        // Load views and migrations
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'databasemanager');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Nothing to register here for now
    }

    /**
     * Load routes based on configuration.
     */
    protected function loadRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Get route group configuration.
     */
    protected function routeConfiguration()
    {
        return [
            'prefix' => config('databasemanager.routes.prefix'),
            'middleware' => config('databasemanager.routes.middleware'),
        ];
    }
}