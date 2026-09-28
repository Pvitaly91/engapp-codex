<?php

namespace App\Services;

/** Reuse the physical M11 proof with M24-only private evidence and endpoint. */
class M24LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M24';

    protected const PRIVATE_DIRECTORY = 'seo-m24-local';

    protected const ENDPOINT = 'm24-target-';
}
