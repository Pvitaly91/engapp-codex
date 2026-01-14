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
        if (!config('coming-soon.enabled', false)) {
            return $next($request);
        }

        // Check if current route name is in the list
        $routes = config('coming-soon.routes', []);
        $currentRouteName = $request->route()?->getName();

        if ($currentRouteName && in_array($currentRouteName, $routes, true)) {
            return $this->comingSoonResponse();
        }

        // Check if current path matches any prefix
        $prefixes = config('coming-soon.prefixes', []);
        $currentPath = '/' . ltrim($request->path(), '/');

        foreach ($prefixes as $prefix) {
            $normalizedPrefix = '/' . ltrim($prefix, '/');
            if (str_starts_with($currentPath, $normalizedPrefix)) {
                return $this->comingSoonResponse();
            }
        }

        return $next($request);
    }

    /**
     * Return the Coming Soon response.
     */
    protected function comingSoonResponse(): Response
    {
        $retryAfter = config('coming-soon.retry_after', 86400);

        return response()
            ->view('coming-soon', [], 503)
            ->header('Retry-After', $retryAfter);
    }
}
