<?php

namespace App\Services;

use App\Support\M68ComparisonBasics as Package;

final class M68LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M68';
    protected const PRIVATE_DIRECTORY = 'seo-m68-local';
    protected const ENDPOINT = 'm68-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
