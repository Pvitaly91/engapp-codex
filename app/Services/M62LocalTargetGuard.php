<?php

namespace App\Services;

use App\Support\M62ReportedReviewRelativeBasicsPackage as Package;

final class M62LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M62';
    protected const PRIVATE_DIRECTORY = 'seo-m62-local';
    protected const ENDPOINT = 'm62-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
