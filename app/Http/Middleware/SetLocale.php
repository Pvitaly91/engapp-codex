<?php

namespace App\Http\Middleware;

use App\Modules\LanguageManager\Services\LocaleService;
use App\Support\SiteMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(private SiteMode $siteMode) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get supported locales from Language Manager module if available
        $supportedLocales = $this->getSupportedLocales();
        $availableLocales = $this->siteMode->availableLocales($supportedLocales, $request);
        $defaultLocale = $this->siteMode->isProduction($request)
            ? $this->siteMode->defaultProductionLocale()
            : $this->getDefaultLocale();

        // Check URL prefix for locale - this is the primary source of truth for URL-based routing
        $segments = $request->segments();
        $firstSegment = $segments[0] ?? null;

        if ($firstSegment && in_array($firstSegment, $availableLocales, true)) {
            // URL has a locale prefix - use it and persist the choice
            $locale = $firstSegment;
            $this->storeLocale($locale);
        } else {
            // No locale prefix means default language (ignore session/cookie for URLs without prefix)
            $locale = $defaultLocale;
            $this->storeLocale($locale);
        }

        // Validate and set locale
        if (in_array($locale, $availableLocales, true)) {
            app()->setLocale($locale);
        } else {
            app()->setLocale($defaultLocale);
        }

        return $next($request);
    }

    /**
     * Store the resolved locale in the session and a long-lived cookie.
     */
    protected function storeLocale(string $locale): void
    {
        session(['locale' => $locale]);

        // Keep cookie lifetime consistent with set-locale route (1 year)
        cookie()->queue(cookie('locale', $locale, 60 * 24 * 365));
    }

    /**
     * Get supported locales from Language Manager or fallback to config.
     */
    protected function getSupportedLocales(): array
    {
        return LocaleService::getSupportedLocaleCodes();
    }

    /**
     * Get default locale from Language Manager or fallback to config.
     */
    protected function getDefaultLocale(): string
    {
        return LocaleService::getDefaultLocaleCode();
    }
}
