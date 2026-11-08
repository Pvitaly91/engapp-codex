<?php

namespace App\Services;

use App\Support\M71ArticlesQuantifiers as Package;

final class M71LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M71';
    protected const PRIVATE_DIRECTORY = 'seo-m71-local';
    protected const ENDPOINT = 'm71-target-';

    public static function scope(): array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
