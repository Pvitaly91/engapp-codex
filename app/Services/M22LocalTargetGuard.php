<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M22LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M22';

    protected const PRIVATE_DIRECTORY = 'seo-m22-local';

    protected const ENDPOINT = 'm22-target-';
}
