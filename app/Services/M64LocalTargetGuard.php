<?php

namespace App\Services;

use App\Support\M64ReasonResultContrastPackage as Package;

final class M64LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M64';
    protected const PRIVATE_DIRECTORY = 'seo-m64-local';
    protected const ENDPOINT = 'm64-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
