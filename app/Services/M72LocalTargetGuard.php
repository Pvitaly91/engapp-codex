<?php

namespace App\Services;

use App\Support\M72PartitivesNegativesGeography as Package;

final class M72LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M72';
    protected const PRIVATE_DIRECTORY = 'seo-m72-local';
    protected const ENDPOINT = 'm72-target-';

    public static function scope(): array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
