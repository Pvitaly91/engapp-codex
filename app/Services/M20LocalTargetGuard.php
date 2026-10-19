<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M20LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M20';

    protected const PRIVATE_DIRECTORY = 'seo-m20-local';

    protected const ENDPOINT = 'm20-target-';
}
