<?php

namespace App\Services;

use App\Support\M53PassiveConstructionsPackage as Package;

final class M53LocalTargetGuard extends TheoryLocalReleaseGuard
{
    protected const LABEL = 'M53';
    protected const PRIVATE_DIRECTORY = 'seo-m53-local';
    protected const ENDPOINT = 'm53-target-';

    public static function scope(): array
    {
        return array_map(static fn ($owner, $slug) => ['identity' => $owner, 'locale' => 'uk',
            'path' => '/theory/passive-voice/'.$slug], Package::OWNERS, Package::SLUGS);
    }
}
