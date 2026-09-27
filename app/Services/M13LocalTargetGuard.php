<?php

namespace App\Services;

/** Same physical local proof as M11, with separate package evidence and endpoint. */
class M13LocalTargetGuard extends M11LocalTargetGuard
{
    protected const LABEL = 'M13';
    protected const PRIVATE_DIRECTORY = 'seo-m13-local';
    protected const ENDPOINT = 'm13-target-';
}
