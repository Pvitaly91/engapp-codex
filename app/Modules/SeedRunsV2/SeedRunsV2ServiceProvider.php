<?php

namespace App\Modules\SeedRunsV2;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class SeedRunsV2ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/seed-runs-v2.php',
            'seed-runs-v2'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'seed-runs-v2');

        // Register Livewire components
        Livewire::component('seed-runs-v2.index', Http\Livewire\SeedRunsIndex::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/seed-runs-v2.php' => config_path('seed-runs-v2.php'),
            ], 'seed-runs-v2-config');

            $this->publishes([
                __DIR__.'/resources/views' => resource_path('views/vendor/seed-runs-v2'),
            ], 'seed-runs-v2-views');
        }
    }
}
