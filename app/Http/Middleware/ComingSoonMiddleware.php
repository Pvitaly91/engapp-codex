<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComingSoonMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if Coming Soon is not enabled
        if (!config('coming-soon.enabled', false)) {
            return $next($request);
        }

        // Get current route name and path
        $routeName = $request->route() ? $request->route()->getName() : null;
        $path = $request->path();

        // Remove locale prefix from path for matching
        $locales = config('app.supported_locales', []);
        if (empty($locales)) {
            $locales = ['uk', 'en', 'pl'];
        }
        
        foreach ($locales as $locale) {
            if (str_starts_with($path, $locale . '/')) {
                $path = substr($path, strlen($locale) + 1);
                break;
            }
        }

        // Check if route name matches
        $protectedRoutes = config('coming-soon.routes', []);
        if ($routeName && in_array($routeName, $protectedRoutes)) {
            return $this->showComingSoon($request);
        }

        // Check if path prefix matches
        $pathPrefixes = config('coming-soon.path_prefixes', []);
        foreach ($pathPrefixes as $prefix) {
            $prefix = ltrim($prefix, '/');
            if (str_starts_with($path, $prefix)) {
                return $this->showComingSoon($request);
            }
        }

        return $next($request);
    }

    /**
     * Return the Coming Soon response
     */
    protected function showComingSoon(Request $request): Response
    {
        $retryAfter = config('coming-soon.retry_after', 86400);

        return response()
            ->view('coming-soon', [], 503)
            ->header('Retry-After', $retryAfter);
    }
}
