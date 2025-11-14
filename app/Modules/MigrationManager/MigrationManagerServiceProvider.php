<?php

namespace App\Modules\MigrationManager;

use Illuminate\Support\ServiceProvider;

class MigrationManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/migration-manager.php',
            'migration-manager'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'migration-manager');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/migration-manager.php' => config_path('migration-manager.php'),
            ], 'migration-manager-config');

            $this->publishes([
                __DIR__.'/resources/views' => resource_path('views/vendor/migration-manager'),
            ], 'migration-manager-views');
        }
    }
}
