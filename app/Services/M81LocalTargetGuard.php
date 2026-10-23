<?php

namespace App\Services;

use App\Support\M81TransformationsCommonMistakes as Package;

final class M81LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='M81';
    protected const PRIVATE_DIRECTORY='seo-m81-local';
    protected const ENDPOINT='m81-target-';
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
