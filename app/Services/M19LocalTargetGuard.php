<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M19LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M19';

    protected const PRIVATE_DIRECTORY = 'seo-m19-local';

    protected const ENDPOINT = 'm19-target-';
}
