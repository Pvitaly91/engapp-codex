<?php

namespace App\Services;

/** Same physical local proof as M11 with separate M23 evidence and endpoint. */
class M23LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M23';

    protected const PRIVATE_DIRECTORY = 'seo-m23-local';

    protected const ENDPOINT = 'm23-target-';
}
