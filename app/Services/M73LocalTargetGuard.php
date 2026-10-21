<?php

namespace App\Services;

use App\Support\M73GenericIrregularCollective as Package;

final class M73LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M73';
    protected const PRIVATE_DIRECTORY = 'seo-m73-local';
    protected const ENDPOINT = 'm73-target-';

    public static function scope(): array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
