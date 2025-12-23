<?php

namespace App\Http\Middleware;

use App\Modules\LanguageManager\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;
        
        // Get supported locales from Language Manager module if available
        $supportedLocales = $this->getSupportedLocales();
        $defaultLocale = $this->getDefaultLocale();

        // First, check URL prefix for locale
        $segments = $request->segments();
        $firstSegment = $segments[0] ?? null;
        
        if ($firstSegment && in_array($firstSegment, $supportedLocales)) {
            $locale = $firstSegment;
        }
        // Then, check session
        elseif ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }
        // Then, check cookie
        elseif ($request->cookie('locale')) {
            $locale = $request->cookie('locale');
        }

        // Validate locale
        if ($locale && in_array($locale, $supportedLocales)) {
            app()->setLocale($locale);
            // Store in session for consistency
            $request->session()->put('locale', $locale);
        } else {
            // Fallback to default locale
            app()->setLocale($defaultLocale);
        }

        return $next($request);
    }

    /**
     * Get supported locales from Language Manager or fallback to config.
     */
    protected function getSupportedLocales(): array
    {
        // Try to get from Language Manager database
        if (Schema::hasTable('languages')) {
            $codes = LocaleService::getActiveLanguages()->pluck('code')->toArray();
            if (!empty($codes)) {
                return $codes;
            }
        }

        // Fallback to config
        return config('app.supported_locales', ['uk', 'en']);
    }

    /**
     * Get default locale from Language Manager or fallback to config.
     */
    protected function getDefaultLocale(): string
    {
        // Try to get from Language Manager database
        if (Schema::hasTable('languages')) {
            $default = LocaleService::getDefaultLanguage();
            if ($default) {
                return $default->code;
            }
        }

        // Fallback to config
        return config('app.locale', 'uk');
    }
}
