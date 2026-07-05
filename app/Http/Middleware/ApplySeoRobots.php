<?php

namespace App\Http\Middleware;

use App\Support\HtmlResponseContent;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApplySeoRobots
{
    private const DUPLICATE_ROUTE_PATTERNS = [
        'site.search',
        'test.step*',
        'test.input',
        'test.manual',
        'test.select',
        'test.drag-drop',
        'test.match',
        'test.dialogue',
    ];

    private const TECHNICAL_ROUTE_PATTERNS = [
        'login.*',
        'locale.set',
        'health',
        'dev.*',
        'admin.*',
        'courses.progress.*',
        'test.js.*',
        'saved-test.js*',
        'words.test.state*',
        'verbs.test.data',
        'theory.navigation',
    ];

    private const TECHNICAL_PATH_PATTERNS = [
        'admin',
        'admin/*',
        'api',
        'api/*',
        'livewire/*',
        '_debugbar/*',
        '_ignition/*',
        'sanctum/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $directive = $this->directive($request);

        if ($directive === null) {
            return $response;
        }

        $response->headers->set('X-Robots-Tag', $directive);

        if (! Str::startsWith(Str::lower((string) $response->headers->get('Content-Type')), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if (! is_string($content) || ! preg_match('/<\/head\s*>/i', $content)) {
            return $response;
        }

        $tag = '<meta name="robots" content="'.$directive.'">';
        $content = preg_replace('/\s*<meta\b[^>]*\bname\s*=\s*(["\'])robots\1[^>]*>/i', '', $content) ?? $content;
        $content = preg_replace('/<\/head\s*>/i', "    {$tag}\n</head>", $content, 1) ?? $content;
        HtmlResponseContent::replace($response, $content);

        return $response;
    }

    private function directive(Request $request): ?string
    {
        $routeName = $request->route()?->getName();
        $path = trim($request->path(), '/');

        if ($this->matches($routeName, self::TECHNICAL_ROUTE_PATTERNS)
            || collect(self::TECHNICAL_PATH_PATTERNS)->contains(
                fn (string $pattern): bool => Str::is($pattern, $path)
            )) {
            return 'noindex, nofollow, noarchive';
        }

        if ($this->matches($routeName, self::DUPLICATE_ROUTE_PATTERNS)) {
            return 'noindex, follow';
        }

        return null;
    }

    private function matches(?string $routeName, array $patterns): bool
    {
        return is_string($routeName) && collect($patterns)->contains(
            fn (string $pattern): bool => Str::is($pattern, $routeName)
        );
    }
}
