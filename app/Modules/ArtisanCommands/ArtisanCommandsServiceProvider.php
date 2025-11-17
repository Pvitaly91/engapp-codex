<?php

namespace App\Modules\ArtisanCommands;

use Illuminate\Support\ServiceProvider;

class ArtisanCommandsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/artisan-commands.php', 'artisan-commands');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'artisan-commands');

        $this->publishes([
            __DIR__ . '/config/artisan-commands.php' => config_path('artisan-commands.php'),
        ], 'artisan-commands-config');

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/artisan-commands'),
        ], 'artisan-commands-views');
    }
}
