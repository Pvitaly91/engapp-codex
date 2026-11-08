<?php

namespace App\Services;

use App\Support\M75PronounForms as Package;

final class M75LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M75';
    protected const PRIVATE_DIRECTORY = 'seo-m75-local';
    protected const ENDPOINT = 'm75-target-';
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
