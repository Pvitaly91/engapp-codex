<?php

namespace App\Services;

use App\Support\M67VerbPatternsCompletion as Package;

final class M67LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M67';
    protected const PRIVATE_DIRECTORY = 'seo-m67-local';
    protected const ENDPOINT = 'm67-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity' => $binding['identity'], 'locale' => 'uk',
            'path' => parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
