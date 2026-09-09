<?php

namespace Tests\Support;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;

trait UsesDisposableMySql
{
    public function createApplication(): Application
    {
        return MySqlCompatibilityEnvironment::application();
    }

    protected function assertComposeTestDatabase(): void
    {
        MySqlCompatibilityEnvironment::assertSafeDatabase(DB::connection());
    }
}
