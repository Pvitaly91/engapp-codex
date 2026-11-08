<?php

namespace App\Services;

use App\Support\M87ContinuousCompletionPastSimple as Package;
use Illuminate\Http\Request;

/** Exact six-owner scope on the unchanged loopback-only proof policy. */
final class M87LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M87';
    protected const PRIVATE_DIRECTORY = 'seo-m87-local';
    protected const ENDPOINT = 'm87-target-';
    public static function allowsProofRequest(Request $request, string $nonce, string $applicationRoot, string $documentRoot): bool
    {
        return !$request->headers->has('origin') && parent::allowsProofRequest($request, $nonce, $applicationRoot, $documentRoot);
    }
    public static function scope(): array
    {
        return array_map(static fn ($b) => ['identity' => $b['identity'], 'locale' => 'uk',
            'path' => parse_url($b['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
