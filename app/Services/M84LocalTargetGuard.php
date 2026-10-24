<?php

namespace App\Services;

use App\Support\M84WordOrderHaveGot as Package;
use Illuminate\Http\Request;

/** Release-specific finite scope on the unchanged loopback-only proof policy. */
final class M84LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M84';
    protected const PRIVATE_DIRECTORY = 'seo-m84-local';
    protected const ENDPOINT = 'm84-target-';

    /** The temporary M84 proof must not be callable with an ambient browser Origin. */
    public static function allowsProofRequest(Request $request, string $nonce, string $applicationRoot, string $documentRoot): bool
    {
        return !$request->headers->has('origin')
            && parent::allowsProofRequest($request, $nonce, $applicationRoot, $documentRoot);
    }

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
