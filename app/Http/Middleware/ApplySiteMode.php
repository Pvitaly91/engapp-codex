<?php

namespace App\Http\Middleware;

use App\Support\SiteMode;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApplySiteMode
{
    public function __construct(private SiteMode $siteMode) {}

    public function handle(Request $request, Closure $next): Response
    {
        $mode = $this->siteMode->current($request);
        $features = $this->siteMode->availableFeatures($request);

        $request->attributes->set('site_mode', $mode);
        $request->attributes->set('site_features', $features);
        View::share('siteMode', $mode);
        View::share('siteFeatures', $features);

        $response = $next($request);

        if ((bool) config('site-mode.expose_mode_header', true)) {
            $response->headers->set('X-Site-Mode', $mode);
        }

        $response->headers->set('Vary', $this->appendVary(
            (string) $response->headers->get('Vary'),
            'Host'
        ));

        if ($mode === SiteMode::DEVELOPMENT) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

            return $response;
        }

        if ($this->shouldCache($request, $response)) {
            $ttl = max(0, (int) config('site-mode.response_cache.ttl', 300));
            $visibility = $response->headers->has('Set-Cookie') ? 'private' : 'public';

            $response->headers->set(
                'Cache-Control',
                sprintf('%s, max-age=%d, stale-while-revalidate=%d', $visibility, $ttl, min($ttl, 60))
            );
        }

        return $response;
    }

    private function shouldCache(Request $request, Response $response): bool
    {
        if (! (bool) config('site-mode.response_cache.enabled', true)
            || ! in_array($request->method(), ['GET', 'HEAD'], true)
            || $response->getStatusCode() !== Response::HTTP_OK
            || $request->user() !== null) {
            return false;
        }

        $contentType = Str::lower((string) $response->headers->get('Content-Type'));
        if (! Str::startsWith($contentType, 'text/html')) {
            return false;
        }

        $path = trim($request->path(), '/');

        return ! collect(config('site-mode.response_cache.excluded_paths', []))
            ->contains(fn (mixed $pattern): bool => Str::is(trim((string) $pattern, '/'), $path));
    }

    private function appendVary(string $current, string $header): string
    {
        return collect(explode(',', $current))
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->push($header)
            ->unique(fn (string $value): string => Str::lower($value))
            ->implode(', ');
    }
}
