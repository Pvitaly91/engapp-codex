<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComingSoonMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAdmin($request)) {
            return $next($request);
        }

        $isEnabled = (bool) config('coming-soon.enabled');
        $isAdminOnly = (bool) config('coming-soon.admin_only');

        if (! $isEnabled && ! $isAdminOnly) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        $routeNames = config('coming-soon.route_names', []);

        if ($routeName && in_array($routeName, $routeNames, true)) {
            return $this->comingSoonResponse();
        }

        $routeNamePrefixes = config('coming-soon.route_name_prefixes', []);
        if ($routeName) {
            foreach ($routeNamePrefixes as $prefix) {
                if ($prefix !== '' && str_starts_with($routeName, $prefix)) {
                    return $this->comingSoonResponse();
                }
            }
        }

        $prefixes = config('coming-soon.path_prefixes', []);
        $path = '/' . ltrim($request->path(), '/');

        foreach ($prefixes as $prefix) {
            $normalized = '/' . ltrim($prefix, '/');

            if ($normalized !== '/' && str_starts_with($path, $normalized)) {
                return $this->comingSoonResponse();
            }
        }

        return $next($request);
    }

    private function comingSoonResponse(): Response
    {
        $response = response()->view('coming-soon', [], 503);

        if ($retryAfter = config('coming-soon.retry_after')) {
            $response->headers->set('Retry-After', $retryAfter);
        }

        return $response;
    }

    private function isAdmin(Request $request): bool
    {
        return (bool) (auth()->user()?->is_admin ?? $request->session()->get('admin_authenticated', false));
    }
}
