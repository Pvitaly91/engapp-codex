<?php

namespace App\Services;

use App\Support\M58PastFuturePassivesPackage as Package;

final class M58LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M58';
    protected const PRIVATE_DIRECTORY = 'seo-m58-local';
    protected const ENDPOINT = 'm58-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
