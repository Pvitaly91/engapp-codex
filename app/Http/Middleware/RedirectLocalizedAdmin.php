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
     */
    protected function getSupportedLocales(): array
    {
        // Try to get from Language Manager database
        if (Schema::hasTable('languages')) {
            try {
                $codes = LocaleService::getActiveLanguages()->pluck('code')->toArray();
                if (!empty($codes)) {
                    return $codes;
                }
            } catch (\Exception $e) {
                // Database not ready
            }
        }

        // Fallback to config
        return config('app.supported_locales', ['uk', 'en']);
    }
}
