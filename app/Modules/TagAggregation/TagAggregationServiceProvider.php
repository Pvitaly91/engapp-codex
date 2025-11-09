<?php

namespace App\Modules\TagAggregation;

use Illuminate\Support\ServiceProvider;

class TagAggregationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/tag-aggregation.php', 'tag-aggregation');
        
        // Register the service as a singleton
        $this->app->singleton(
            \App\Modules\TagAggregation\Services\TagAggregationService::class,
            function ($app) {
                return new \App\Modules\TagAggregation\Services\TagAggregationService();
            }
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/tag-aggregation.php' => config_path('tag-aggregation.php'),
        ], 'tag-aggregation-config');
    }
}
