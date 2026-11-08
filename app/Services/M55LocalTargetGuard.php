<?php

namespace App\Services;

use App\Support\M55PassiveReviewInfinitivePackage as Package;

final class M55LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M55';
    protected const PRIVATE_DIRECTORY = 'seo-m55-local';
    protected const ENDPOINT = 'm55-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
