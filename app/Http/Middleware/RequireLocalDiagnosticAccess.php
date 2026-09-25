<?php

namespace App\Http\Middleware;

use App\Support\Diagnostics\LocalCurlTlsControl;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards a temporary local diagnostic endpoint.
 *
 * REMOTE_ADDR comes from Apache's server variables. Deliberately do not use
 * Request::ip(), X-Forwarded-For, Host, or query input as the authority.
 */
class RequireLocalDiagnosticAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->method() !== 'GET'
            || $request->getQueryString() !== null
            || $request->getContent() !== ''
            || ! in_array((string) $request->server->get('REMOTE_ADDR'), ['127.0.0.1', '::1'], true)) {
            return $this->notFound();
        }

        $nonce = LocalCurlTlsControl::nonce();
        $provided = $request->headers->get('X-Gramlyze-M94-Probe');

        if (! is_string($nonce) || ! is_string($provided) || ! hash_equals($nonce, $provided)) {
            return $this->notFound();
        }

        return $next($request);
    }

    private function notFound(): JsonResponse
    {
        return response()->json(['message' => 'Not Found'], Response::HTTP_NOT_FOUND, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }
}
