<?php

namespace App\Services;

use App\Support\M78PhrasalQuestionBasics as Package;

final class M78LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='M78';
    protected const PRIVATE_DIRECTORY='seo-m78-local';
    protected const ENDPOINT='m78-target-';
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}

