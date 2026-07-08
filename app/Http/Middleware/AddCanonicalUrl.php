<?php

namespace App\Http\Middleware;

use App\Modules\LanguageManager\Services\LocaleService;
use App\Support\HtmlResponseContent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddCanonicalUrl
{
    /** @var array<int, string> */
    private const PUBLIC_ROUTE_PATTERNS = [
        'home',
        'pages.*',
        'theory.*',
        'catalog.*',
        'courses.*',
        'site.search',
        'dynamic-pages.*',
        'words.search',
        'words.test*',
        'verbs.test*',
        'test.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldAddCanonical($request, $response)) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || ! preg_match('/<\/head\s*>/i', $content)) {
            return $response;
        }

        $canonical = htmlspecialchars($this->canonicalUrl($request), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $tag = '<link rel="canonical" href="'.$canonical.'">';

        // The middleware is the single source of truth if a legacy layout already has a tag.
        $content = preg_replace('/\s*<link\b[^>]*\brel\s*=\s*(["\'])canonical\1[^>]*>/i', '', $content) ?? $content;
        $content = preg_replace('/<\/head\s*>/i', "    {$tag}\n</head>", $content, 1) ?? $content;
        HtmlResponseContent::replace($response, $content);

        return $response;
    }

    private function shouldAddCanonical(Request $request, Response $response): bool
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)
            || $response->getStatusCode() !== Response::HTTP_OK
            || ! Str::startsWith(Str::lower((string) $response->headers->get('Content-Type')), 'text/html')) {
            return false;
        }

        $routeName = $request->route()?->getName();

        return is_string($routeName)
            && collect(self::PUBLIC_ROUTE_PATTERNS)->contains(
                fn (string $pattern): bool => Str::is($pattern, $routeName)
            );
    }

    private function canonicalUrl(Request $request): string
    {
        $origin = rtrim((string) config('site-mode.production_origin', 'https://gramlyze.com'), '/');
        $routeName = (string) $request->route()?->getName();

        if (Str::is('test.*', $routeName)
            && $routeName !== 'test.show'
            && is_string($request->route('slug'))) {
            return $origin.'/test/'.$this->encodePath($request->route('slug'));
        }

        $segments = $request->segments();
        $supportedLocales = array_map(
            static fn (mixed $locale): string => Str::lower((string) $locale),
            LocaleService::getSupportedLocaleCodes()
        );

        if ($segments !== [] && in_array(Str::lower($segments[0]), $supportedLocales, true)) {
            array_shift($segments);
        }

        $path = collect($segments)->map('rawurlencode')->implode('/');

        return $origin.($path === '' ? '/' : '/'.$path);
    }

    private function encodePath(string $path): string
    {
        return collect(explode('/', trim($path, '/')))->map('rawurlencode')->implode('/');
    }
}
