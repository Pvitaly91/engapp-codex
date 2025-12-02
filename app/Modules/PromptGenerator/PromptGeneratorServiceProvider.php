<?php

namespace App\Modules\PromptGenerator;

use Illuminate\Support\ServiceProvider;

class PromptGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/prompt-generator.php', 'prompt-generator');
    }

    public function boot(): void
    {
        $modulePath = __DIR__;

        $this->loadRoutesFrom($modulePath . '/routes/web.php');
        $this->loadViewsFrom($modulePath . '/resources/views', 'prompt-generator');

        $this->publishes([
            $modulePath . '/config/prompt-generator.php' => config_path('prompt-generator.php'),
        ], 'prompt-generator-config');

        $this->publishes([
            $modulePath . '/resources/views' => resource_path('views/vendor/prompt-generator'),
        ], 'prompt-generator-views');
    }
}
