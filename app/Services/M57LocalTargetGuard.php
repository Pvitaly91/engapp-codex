<?php

namespace App\Services;

use App\Support\M57ContinuousPerfectPassivesPackage as Package;

final class M57LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M57';
    protected const PRIVATE_DIRECTORY = 'seo-m57-local';
    protected const ENDPOINT = 'm57-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
