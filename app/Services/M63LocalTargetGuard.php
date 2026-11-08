<?php

namespace App\Services;

use App\Support\M63RelativeCompletionLinkingPackage as Package;

final class M63LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M63';
    protected const PRIVATE_DIRECTORY = 'seo-m63-local';
    protected const ENDPOINT = 'm63-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
