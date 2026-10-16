<?php

namespace App\Services;

use App\Support\M61ReportedSpeechChangesPackage as Package;

final class M61LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M61';
    protected const PRIVATE_DIRECTORY = 'seo-m61-local';
    protected const ENDPOINT = 'm61-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
