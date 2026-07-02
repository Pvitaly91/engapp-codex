<?php

namespace App\Http\Middleware;

use App\Modules\LanguageManager\Services\LocaleService;
use App\Support\SiteMode;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSiteModeLocale
{
    public function __construct(private SiteMode $siteMode) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->siteMode->isDevelopment($request)) {
            return $next($request);
        }

        $segments = $request->segments();
        $requestedLocale = strtolower((string) ($segments[0] ?? ''));
        $knownLocales = LocaleService::getSupportedLocaleCodes();

        if ($requestedLocale !== ''
            && in_array($requestedLocale, $knownLocales, true)
            && ! $this->siteMode->localeAllowed($requestedLocale, $request)) {
            array_shift($segments);

            return $this->redirectToProductionLocale($request, $segments);
        }

        return $next($request);
    }

    private function redirectToProductionLocale(Request $request, array $segments): RedirectResponse
    {
        $locale = $this->siteMode->defaultProductionLocale();
        $defaultLocale = LocaleService::getDefaultLocaleCode();

        if ($locale !== $defaultLocale) {
            array_unshift($segments, $locale);
        }

        $path = '/'.implode('/', $segments);
        $query = $request->getQueryString();

        return redirect()->to($path.($query ? '?'.$query : ''));
    }
}
