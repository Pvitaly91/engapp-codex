<?php

namespace App\Services;

use App\Support\M80NegativesTransformations as Package;

final class M80LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='M80';
    protected const PRIVATE_DIRECTORY='seo-m80-local';
    protected const ENDPOINT='m80-target-';
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
