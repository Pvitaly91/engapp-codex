<?php

namespace Tests\Unit;

use Illuminate\Database\MySqlConnection;
use RuntimeException;
use Tests\Support\IsolatedTestEnvironment;
use Tests\Support\MySqlCompatibilityEnvironment;
use Tests\TestCase;

class MySqlCompatibilityIsolationTest extends TestCase
{
    public function test_ordinary_schema_guard_still_rejects_mysql_before_connecting(): void
    {
        $connection = new MySqlConnection(static fn () => throw new RuntimeException('PDO must not be opened'), 'not-owned', '', ['driver' => 'mysql']);
        $this->expectExceptionMessage('Refusing schema operations outside testing SQLite.');
        IsolatedTestEnvironment::assertSafeDatabase($connection);
    }

    public function test_mysql_profile_requires_explicit_opt_in(): void
    {
        $old = getenv('M42_OPT_IN');
        putenv('M42_OPT_IN');
        try {
            $this->expectExceptionMessage('Missing disposable MySQL profile field: OPT_IN');
            MySqlCompatibilityEnvironment::application();
        } finally {
            $old === false ? putenv('M42_OPT_IN') : putenv('M42_OPT_IN='.$old);
        }
    }

    public function test_loopback_alone_does_not_authorize_schema_operations(): void
    {
        $values = ['OPT_IN' => 'disposable-local-mysql', 'DATABASE' => 'working-database', 'USER' => 'u'.str_repeat('a', 16)];
        $old = [];
        foreach ($values as $key => $value) {
            $old[$key] = getenv('M42_'.$key);
            putenv('M42_'.$key.'='.$value);
        }
        try {
            $connection = new MySqlConnection(static fn () => throw new RuntimeException('PDO must not be opened'), 'working-database', '', ['driver' => 'mysql', 'host' => '127.0.0.1']);
            $this->expectExceptionMessage('Refusing a non-disposable database connection.');
            MySqlCompatibilityEnvironment::assertSafeDatabase($connection);
        } finally {
            foreach ($old as $key => $value) {
                $value === false ? putenv('M42_'.$key) : putenv('M42_'.$key.'='.$value);
            }
        }
    }
}
