<?php

namespace App\Modules\FileManager;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class FileManagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'file-manager');
        $this->registerRoutes();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/file-manager.php' => config_path('file-manager.php'),
            ], 'file-manager-config');

            $this->publishes([
                __DIR__.'/resources/views' => resource_path('views/vendor/file-manager'),
            ], 'file-manager-views');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/file-manager.php', 'file-manager');
        $this->app->singleton(Services\FileSystemService::class);
    }

    /**
     * Register the module routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth.admin'])
            ->prefix(config('file-manager.route_prefix', 'admin/file-manager'))
            ->name('file-manager.')
            ->group(__DIR__.'/routes/web.php');
    }
}
