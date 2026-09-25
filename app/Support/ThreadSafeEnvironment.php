<?php

namespace App\Support;

use Illuminate\Support\Env;

final class ThreadSafeEnvironment
{
    public static function configure(bool $threaded, string $sapi): void
    {
        // mod_php worker threads share putenv/getenv, unlike request-local
        // $_SERVER/$_ENV. Do not let another request's .env values look like
        // immutable external overrides and then disappear during bootstrap.
        if ($threaded && $sapi === 'apache2handler') {
            Env::disablePutenv();
        }
    }
}
