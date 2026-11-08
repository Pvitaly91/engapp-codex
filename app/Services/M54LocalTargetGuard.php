<?php

namespace App\Services;

use App\Support\M54PassivePatternsPackage as Package;

final class M54LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M54';
    protected const PRIVATE_DIRECTORY = 'seo-m54-local';
    protected const ENDPOINT = 'm54-target-';

    public static function scope(): array
    {
        return array_map(static fn ($owner, $slug) => ['identity' => $owner, 'locale' => 'uk',
            'path' => '/theory/passive-voice/'.$slug], Package::OWNERS, Package::SLUGS);
    }
}
