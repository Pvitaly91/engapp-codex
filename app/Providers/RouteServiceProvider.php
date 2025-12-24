<?php

namespace App\Providers;

use App\Modules\LanguageManager\Models\Language;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/admin';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Register localized routes for non-default languages
            $this->registerLocalizedRoutes();
        });
    }

    /**
     * Register routes with locale prefix for non-default languages.
     */
    protected function registerLocalizedRoutes(): void
    {
        // Skip if languages table doesn't exist
        if (!Schema::hasTable('languages')) {
            return;
        }

        try {
            $languages = Language::getActive();
            $defaultLanguage = Language::getDefault();

            if (!$defaultLanguage || $languages->isEmpty()) {
                return;
            }

            // Get non-default language codes for URL prefixes
            $localePrefixes = $languages
                ->where('is_default', false)
                ->pluck('code')
                ->toArray();

            if (empty($localePrefixes)) {
                return;
            }

            // Register the same web routes with locale prefix
            foreach ($localePrefixes as $locale) {
                Route::middleware('web')
                    ->prefix($locale)
                    ->group(base_path('routes/web.php'));
            }
        } catch (\Exception $e) {
            // Silently fail if database isn't ready
        }
    }
}
