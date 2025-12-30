<?php

namespace App\Http\Middleware;

use App\Modules\LanguageManager\Services\LocaleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class RedirectLocalizedAdmin
{
    /**
     * Cached supported locales for performance.
     */
    protected static ?array $cachedLocales = null;

    /**
     * Handle an incoming request.
     *
     * Redirects requests like /{locale}/admin/... to /admin/... with 301 status.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $segments = $request->segments();
        
        // Check if first segment is a locale and second is "admin"
        if (count($segments) >= 2) {
            $firstSegment = $segments[0];
            $secondSegment = $segments[1];
            
            if ($secondSegment === 'admin' && $this->isLocale($firstSegment)) {
                // Build the redirect URL without locale prefix
                $newPath = '/' . implode('/', array_slice($segments, 1));
                $queryString = $request->getQueryString();
                
                if ($queryString) {
                    $newPath .= '?' . $queryString;
                }
                
                return redirect($newPath, 301);
            }
        }

        return $next($request);
    }

    /**
     * Check if the given segment is a valid locale code.
     */
    protected function isLocale(string $segment): bool
    {
        $supportedLocales = $this->getSupportedLocales();
        return in_array($segment, $supportedLocales);
    }

    /**
     * Get supported locales from Language Manager or fallback to config.
     * Results are cached for the lifetime of the request.
     */
    protected function getSupportedLocales(): array
    {
        // Return cached result if available
        if (self::$cachedLocales !== null) {
            return self::$cachedLocales;
        }

        // Try to get from Language Manager database
        if (Schema::hasTable('languages')) {
            try {
                $codes = LocaleService::getActiveLanguages()->pluck('code')->toArray();
                if (!empty($codes)) {
                    self::$cachedLocales = $codes;
                    return self::$cachedLocales;
                }
            } catch (\Exception $e) {
                // Database not ready
            }
        }

        // Fallback to config
        self::$cachedLocales = config('app.supported_locales', ['uk', 'en']);
        return self::$cachedLocales;
    }
}
