<?php

namespace App\Modules\LanguageManager;

use App\Modules\LanguageManager\Services\LocaleService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class LanguageManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/language-manager.php', 'language-manager');

        // Register LocaleService as singleton
        $this->app->singleton(LocaleService::class, function () {
            return new LocaleService();
        });
    }

    public function boot(): void
    {
        $modulePath = __DIR__;

        $this->loadRoutesFrom($modulePath . '/routes/web.php');
        $this->loadViewsFrom($modulePath . '/resources/views', 'language-manager');
        $this->loadMigrationsFrom($modulePath . '/database/migrations');

        // Register middleware alias
        $this->app['router']->aliasMiddleware('locale.url', \App\Modules\LanguageManager\Http\Middleware\LocaleFromUrl::class);

        // Register Blade directives
        Blade::directive('localizedUrl', function ($expression) {
            return "<?php echo \App\Modules\LanguageManager\Services\LocaleService::localizedUrl({$expression}); ?>";
        });

        Blade::directive('switchLocaleUrl', function ($expression) {
            return "<?php echo \App\Modules\LanguageManager\Services\LocaleService::switchLocaleUrl({$expression}); ?>";
        });

        // Share languages with views that use public layouts and language-manager
        view()->composer(['layouts.engram', 'layouts.public-v2', 'language-manager::*'], function ($view) {
            $view->with('__languages', LocaleService::getActiveLanguages());
            $view->with('__currentLocale', LocaleService::getCurrentLocale());
            $view->with('__languageSwitcher', LocaleService::getLanguageSwitcherData());
        });

        // Publishing
        $this->publishes([
            $modulePath . '/config/language-manager.php' => config_path('language-manager.php'),
        ], 'language-manager-config');

        $this->publishes([
            $modulePath . '/resources/views' => resource_path('views/vendor/language-manager'),
        ], 'language-manager-views');

        $this->publishes([
            $modulePath . '/database/migrations/' => database_path('migrations'),
        ], 'language-manager-migrations');
    }
}
