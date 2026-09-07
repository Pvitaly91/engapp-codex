<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Server-resolved learning identity for the legacy /{slug}/questions HTML fallback. */
final class ResolvedHtmlTest
{
    private function __construct(private readonly string $slug) {}

    public static function remember(Request $request, string $slug): void
    {
        $request->attributes->set(self::class, new self(trim($slug, '/')));
    }

    public static function slugFor(Request $request, Response $response): ?string
    {
        $resolved = $request->attributes->get(self::class);

        if (! $resolved instanceof self
            || $request->route()?->getName() !== 'test.js.questions'
            || ! in_array($request->method(), ['GET', 'HEAD'], true)
            || $response->getStatusCode() !== Response::HTTP_OK
            || ! str_starts_with(strtolower((string) $response->headers->get('Content-Type')), 'text/html')) {
            return null;
        }

        return $resolved->slug !== '' ? $resolved->slug : null;
    }
}
