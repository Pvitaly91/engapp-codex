<?php

namespace App\Services;

use App\Support\M70IntensifiersNounBasics as Package;

final class M70LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M70';
    protected const PRIVATE_DIRECTORY = 'seo-m70-local';
    protected const ENDPOINT = 'm70-target-';

    public static function scope(): array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
