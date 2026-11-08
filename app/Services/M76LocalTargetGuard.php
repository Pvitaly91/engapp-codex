<?php

namespace App\Services;

use App\Support\M76PronounRelations as Package;

final class M76LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M76';
    protected const PRIVATE_DIRECTORY = 'seo-m76-local';
    protected const ENDPOINT = 'm76-target-';
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
