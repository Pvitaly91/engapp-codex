<?php

namespace App\Services;

use App\Support\M56GerundSimplePassivesPackage as Package;

final class M56LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M56';
    protected const PRIVATE_DIRECTORY = 'seo-m56-local';
    protected const ENDPOINT = 'm56-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
