<?php

namespace App\Services;

use App\Support\M65PurposeGerundInfinitivePackage as Package;

final class M65LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M65';
    protected const PRIVATE_DIRECTORY = 'seo-m65-local';
    protected const ENDPOINT = 'm65-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
