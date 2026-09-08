<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        \Tests\Support\IsolatedTestEnvironment::prepare();
        $app = require __DIR__.'/../bootstrap/app.php';

        \Tests\Support\IsolatedTestEnvironment::configure($app);

        $app->make(Kernel::class)->bootstrap();
        \Tests\Support\IsolatedTestEnvironment::assertSafeDatabase(\Illuminate\Support\Facades\DB::connection());

        return $app;
    }
}
