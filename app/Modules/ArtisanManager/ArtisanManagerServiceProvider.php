<?php

namespace App\Modules\ArtisanManager;

use Illuminate\Support\ServiceProvider;

class ArtisanManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/artisan-manager.php',
            'artisan-manager'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'artisan-manager');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/artisan-manager.php' => config_path('artisan-manager.php'),
            ], 'artisan-manager-config');

            $this->publishes([
                __DIR__.'/resources/views' => resource_path('views/vendor/artisan-manager'),
            ], 'artisan-manager-views');
        }
    }
}
