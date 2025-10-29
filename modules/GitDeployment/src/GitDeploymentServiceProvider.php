<?php

namespace Modules\GitDeployment;

use Illuminate\Support\ServiceProvider;

class GitDeploymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/git-deployment.php', 'git-deployment');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'git-deployment');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/git-deployment.php' => config_path('git-deployment.php'),
        ], 'git-deployment-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/git-deployment'),
        ], 'git-deployment-views');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'git-deployment-migrations');
    }
}
