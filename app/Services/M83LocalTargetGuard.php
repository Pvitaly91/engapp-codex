<?php

namespace App\Services;

use App\Support\M83B2RevisionBasicGrammar as Package;

final class M83LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='M83';
    protected const PRIVATE_DIRECTORY='seo-m83-local';
    protected const ENDPOINT='m83-target-';
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
