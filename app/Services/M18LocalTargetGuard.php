<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M18LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M18';

    protected const PRIVATE_DIRECTORY = 'seo-m18-local';

    protected const ENDPOINT = 'm18-target-';
}
