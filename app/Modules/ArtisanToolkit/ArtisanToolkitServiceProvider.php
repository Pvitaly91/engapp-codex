<?php

namespace App\Modules\ArtisanToolkit;

use Illuminate\Support\ServiceProvider;

class ArtisanToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/artisan-toolkit.php',
            'artisan-toolkit'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'artisan-toolkit');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/artisan-toolkit.php' => config_path('artisan-toolkit.php'),
            ], 'artisan-toolkit-config');

            $this->publishes([
                __DIR__.'/resources/views' => resource_path('views/vendor/artisan-toolkit'),
            ], 'artisan-toolkit-views');
        }
    }
}
