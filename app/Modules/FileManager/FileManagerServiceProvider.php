<?php

namespace App\Modules\FileManager;

use Illuminate\Support\ServiceProvider;

class FileManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/file-manager.php', 'file-manager');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'file-manager');

        $this->publishes([
            __DIR__.'/config/file-manager.php' => config_path('file-manager.php'),
        ], 'file-manager-config');

        $this->publishes([
            __DIR__.'/resources/views' => resource_path('views/vendor/file-manager'),
        ], 'file-manager-views');
    }
}
