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
        // Load routes
        $this->loadRoutes();
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'databasemanager');
        
        // Load migrations from the current structure
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        // Publish assets if running in console
        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__.'/../config/databasemanager.php' => config_path('databasemanager.php'),
            ], 'databasemanager-config');

            // Publish views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/databasemanager'),
            ], 'databasemanager-views');

            // Publish migrations based on your structure
            $this->publishes([
                __DIR__.'/Database/migrations' => database_path('migrations'),
            ], 'databasemanager-migrations');

            // Register commands
            $this->commands([
                \YoussefElghaly\DatabaseManager\Console\Commands\ModuleMigrationStatus::class,
            ]);
        }

        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../config/databasemanager.php', 'databasemanager');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Nothing to register for now
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
            'prefix' => config('databasemanager.routes.prefix', 'database-designer'),
            'middleware' => config('databasemanager.routes.middleware', ['web']),
        ];
    }
}
