<?php

namespace App\Services;

use App\Support\M79QuestionTypesCompletion as Package;

final class M79LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL='M79';
    protected const PRIVATE_DIRECTORY='seo-m79-local';
    protected const ENDPOINT='m79-target-';
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
