<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M14LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M14';
    protected const PRIVATE_DIRECTORY = 'seo-m14-local';
    protected const ENDPOINT = 'm14-target-';
}
