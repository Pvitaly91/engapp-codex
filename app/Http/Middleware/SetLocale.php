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
        // Get supported locales from Language Manager module if available
        $supportedLocales = $this->getSupportedLocales();
        $defaultLocale = $this->getDefaultLocale();

        // Check URL prefix for locale - this is the primary source of truth for URL-based routing
        $segments = $request->segments();
        $firstSegment = $segments[0] ?? null;
        
        if ($firstSegment && in_array($firstSegment, $supportedLocales)) {
            // URL has a locale prefix - use it
            $locale = $firstSegment;
        } else {
            // No locale prefix in URL - use default locale
            // This ensures "/" always shows default language regardless of session
            $locale = $defaultLocale;
        }

        // Validate and set locale
        if (in_array($locale, $supportedLocales)) {
            app()->setLocale($locale);
        } else {
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
