<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M17LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M17';

    protected const PRIVATE_DIRECTORY = 'seo-m17-local';

    protected const ENDPOINT = 'm17-target-';
}
