<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M16LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M16';

    protected const PRIVATE_DIRECTORY = 'seo-m16-local';

    protected const ENDPOINT = 'm16-target-';
}
