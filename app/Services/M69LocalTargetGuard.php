<?php

namespace App\Services;

use App\Support\M69AdjectivesAdverbs as Package;

final class M69LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M69';
    protected const PRIVATE_DIRECTORY = 'seo-m69-local';
    protected const ENDPOINT = 'm69-target-';

    public static function scope(): array
    {
        return array_map(static fn ($binding) => ['identity'=>$binding['identity'], 'locale'=>'uk',
            'path'=>parse_url($binding['local_url'], PHP_URL_PATH)], Package::profile()['bindings']);
    }
}
