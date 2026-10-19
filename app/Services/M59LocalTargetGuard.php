<?php

namespace App\Services;

use App\Support\M59PassiveFutureAgentReviewPackage as Package;

final class M59LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M59';
    protected const PRIVATE_DIRECTORY = 'seo-m59-local';
    protected const ENDPOINT = 'm59-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
