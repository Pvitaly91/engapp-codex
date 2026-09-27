<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M12LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M12';
    protected const PRIVATE_DIRECTORY = 'seo-m12-local';
    protected const ENDPOINT = 'm12-target-';
}
