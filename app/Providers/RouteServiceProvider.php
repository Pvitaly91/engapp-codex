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
            $localeConfig = $this->getLocaleConfig();

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));

            // Register localized routes for non-default languages
            $this->registerLocalizedRoutes($localeConfig);

            // Redirect legacy localized admin URLs back to non-localized paths
            $this->registerAdminLocaleRedirects($localeConfig);
        });
    }

    /**
     * Register routes with locale prefix for non-default languages.
     */
    protected function registerLocalizedRoutes(array $localeConfig): void
    {
        // Skip if languages table doesn't exist
        if (!Schema::hasTable('languages')) {
            return;
        }

        if (empty($localeConfig['database_locales'])) {
            return;
        }

        $localePrefixes = array_values(array_diff($localeConfig['database_locales'], [$localeConfig['default']]));

        if (empty($localePrefixes)) {
            return;
        }

        // Register the same web routes with locale prefix
        foreach ($localePrefixes as $locale) {
            Route::middleware('web')
                ->prefix($locale)
                ->group(base_path('routes/web.php'));
        }
    }

    /**
     * Redirect legacy localized admin URLs to the non-localized admin paths.
     */
    protected function registerAdminLocaleRedirects(array $localeConfig): void
    {
        if (empty($localeConfig['active'])) {
            return;
        }

        Route::middleware('web')->group(function () use ($localeConfig) {
            Route::get('/{locale}/admin/{path?}', function (Request $request, string $locale, ?string $path = null) {
                $suffix = $path ? '/' . ltrim($path, '/') : '';
                $query = $request->getQueryString();

                $target = '/admin' . $suffix;
                if ($query) {
                    $target .= '?' . $query;
                }

                return redirect()->to($target, 301);
            })
                ->whereIn('locale', $localeConfig['active'])
                ->where('path', '.*');
        });
    }

    /**
     * Collect the active locales and default language code.
     *
     * @return array{default: string, active: array<int, string>, database_locales: array<int, string>}
     */
    protected function getLocaleConfig(): array
    {
        $defaultLocale = config('app.locale', 'uk');
        $locales = config('app.supported_locales', [$defaultLocale]);
        $databaseLocales = [];

        if (Schema::hasTable('languages')) {
            try {
                $languages = Language::getActive();
                $defaultLanguage = Language::getDefault();

                if ($languages->isNotEmpty()) {
                    $databaseLocales = $languages->pluck('code')->toArray();
                }

                if ($defaultLanguage) {
                    $defaultLocale = $defaultLanguage->code;
                }
            } catch (\Exception $e) {
                // Silently fall back to config values
            }
        }

        $activeLocales = $databaseLocales !== [] ? $databaseLocales : $locales;

        if (! in_array($defaultLocale, $activeLocales)) {
            $activeLocales[] = $defaultLocale;
        }

        return [
            'default' => $defaultLocale,
            'active' => array_values(array_unique(array_filter($activeLocales))),
            'database_locales' => array_values(array_unique(array_filter($databaseLocales))),
        ];
    }
}
