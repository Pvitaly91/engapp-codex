<?php

namespace App\Services;

use App\Support\M74OtherPronounsBasics as Package;

final class M74LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M74';
    protected const PRIVATE_DIRECTORY = 'seo-m74-local';
    protected const ENDPOINT = 'm74-target-';
    public static function scope():array
    {
        return array_map(static fn($b)=>['identity'=>$b['identity'],'locale'=>'uk',
            'path'=>parse_url($b['local_url'],PHP_URL_PATH)],Package::profile()['bindings']);
    }
}
