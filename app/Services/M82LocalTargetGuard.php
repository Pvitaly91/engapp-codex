<?php

namespace App\Services;

use App\Support\M82CommonMistakesLevelRevision as Package;

final class M82LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='M82';
    protected const PRIVATE_DIRECTORY='seo-m82-local';
    protected const ENDPOINT='m82-target-';
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
