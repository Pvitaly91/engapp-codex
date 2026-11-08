<?php

namespace App\Services;

use App\Support\M60ReportedSpeechBasicsPackage as Package;

final class M60LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M60';
    protected const PRIVATE_DIRECTORY = 'seo-m60-local';
    protected const ENDPOINT = 'm60-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
