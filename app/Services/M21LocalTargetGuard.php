<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M21LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M21';

    protected const PRIVATE_DIRECTORY = 'seo-m21-local';

    protected const ENDPOINT = 'm21-target-';
}
