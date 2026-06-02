<?php

namespace App\Modules\DatabaseStructure;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class DatabaseStructureServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'database-structure');
        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/database-structure.php' => config_path('database-structure.php'),
            ], 'database-structure-config');

            $this->publishes([
                __DIR__ . '/resources/views' => resource_path('views/vendor/database-structure'),
            ], 'database-structure-views');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/database-structure.php', 'database-structure');
        $this->app->singleton(Services\DatabaseStructureFetcher::class);
        $this->app->singleton(Services\ContentManagementMenuManager::class);
        $this->app->singleton(Services\FilterStorageManager::class);
        $this->app->singleton(Services\SearchPresetManager::class);
    }

    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth.admin'])
            ->prefix(config('database-structure.route_prefix', 'admin/database-structure'))
            ->name('database-structure.')
            ->group(__DIR__ . '/routes/web.php');
    }
}
