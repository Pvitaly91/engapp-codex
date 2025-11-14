<?php

namespace App\Modules\PageManager;

use Illuminate\Support\ServiceProvider;

class PageManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/page-manager.php', 'page-manager');
    }

    public function boot(): void
    {
        $modulePath = __DIR__;

        $this->loadRoutesFrom($modulePath . '/routes/web.php');
        $this->loadViewsFrom($modulePath . '/resources/views', 'page-manager');
        $this->loadMigrationsFrom($modulePath . '/database/migrations');

        $this->publishes([
            $modulePath . '/config/page-manager.php' => config_path('page-manager.php'),
        ], 'page-manager-config');

        $this->publishes([
            $modulePath . '/resources/views' => resource_path('views/vendor/page-manager'),
        ], 'page-manager-views');

        $this->publishes([
            $modulePath . '/database/migrations/' => database_path('migrations'),
        ], 'page-manager-migrations');
    }
}
