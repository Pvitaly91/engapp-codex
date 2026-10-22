<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\Support\IsolatedTestEnvironment;

/** Explicit discovery wrapper: the original legacy @test method is not renamed or weakened. */
class M76SavedTestRetryRegressionTest extends SavedTestStepWrongAnswerTest
{
    public function test_existing_saved_step_wrong_answer_regression_explicitly_runs(): void
    {
        // The inherited method creates/drops tables. It is permitted only in this private in-memory runtime.
        IsolatedTestEnvironment::assertSafeDatabase(DB::connection());
        self::assertSame('testing', app()->environment());
        self::assertSame('sqlite', DB::connection()->getDriverName());
        self::assertSame(':memory:', DB::connection()->getDatabaseName());
        self::assertNotSame(realpath(base_path()), realpath(app()->environmentPath()));
        parent::step_mode_shows_user_answer_when_wrong();
    }
}
