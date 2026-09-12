<?php

namespace App\Http\Controllers;

use App\Support\Diagnostics\LocalCurlTlsProbe;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Temporary local-only endpoint. Its route is deliberately removed after M9.4
 * acceptance; the reusable probe has no public route at rest.
 */
class LocalCurlTlsProbeController extends Controller
{
    public function __invoke(LocalCurlTlsProbe $probe): JsonResponse
    {
        try {
            $result = $probe->run();
        } catch (Throwable) {
            $result = ['ok' => false, 'failure' => 'probe-failed'];
        }

        return response()->json($result, ($result['ok'] ?? false) === true ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE, [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }
}
