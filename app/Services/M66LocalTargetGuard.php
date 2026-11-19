<?php

namespace App\Services;

use App\Support\M66VerbPatternsCorePackage as Package;

final class M66LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M66';
    protected const PRIVATE_DIRECTORY = 'seo-m66-local';
    protected const ENDPOINT = 'm66-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
