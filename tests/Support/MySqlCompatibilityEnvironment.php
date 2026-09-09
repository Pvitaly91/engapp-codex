<?php

namespace Tests\Support;

use Illuminate\Database\Connection;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/** Explicit disposable-instance profile. Never used by the ordinary test bootstrap. */
final class MySqlCompatibilityEnvironment
{
    public static function value(string $name): string
    {
        $value = getenv('M42_'.$name);
        if (! is_string($value) || $value === '') {
            throw new RuntimeException('Missing disposable MySQL profile field: '.$name);
        }

        return $value;
    }

    public static function application(): Application
    {
        if (self::value('OPT_IN') !== 'disposable-local-mysql') {
            throw new RuntimeException('Explicit disposable MySQL opt-in required.');
        }
        IsolatedTestEnvironment::prepare();
        $app = require dirname(__DIR__, 2).'/bootstrap/app.php';
        IsolatedTestEnvironment::configure($app);
        $app->afterBootstrapping(LoadConfiguration::class, static function (Application $app): void {
            $app['config']->set('database.default', 'm42');
            $app['config']->set('database.connections.m42', [
                'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => self::value('PORT'),
                'database' => self::value('DATABASE'), 'username' => self::value('USER'),
                'password' => self::value('PASSWORD'), 'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci', 'prefix' => '',
                // No strict/modes override: exercise the real server's session defaults.
            ]);
        });
        $app->afterBootstrapping(RegisterProviders::class, static function (Application $app): void {
            $connection = $app['db']->connection();
            self::assertSafeDatabase($connection);
            $connection->beforeExecuting(static function (string $sql, array $bindings, Connection $db): void {
                // Guard every mutation, including schema changes inside inherited fixtures.
                if (! preg_match('/^\s*(select|show|explain)\b/i', $sql)) {
                    self::assertSafeDatabase($db);
                }
            });
        });
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public static function assertSafeDatabase(Connection $db): void
    {
        $database = self::value('DATABASE');
        $user = self::value('USER');
        if (self::value('OPT_IN') !== 'disposable-local-mysql'
            || ! preg_match('/^m42[a-f0-9]{32}$/D', $database)
            || ! preg_match('/^u[a-f0-9]{16}$/D', $user)
            || $db->getDriverName() !== 'mysql' || $db->getConfig('host') !== '127.0.0.1'
            || (string) $db->getConfig('port') !== self::value('PORT')
            || $db->getDatabaseName() !== $database || ! app()->environment('testing')) {
            throw new RuntimeException('Refusing a non-disposable database connection.');
        }
        // PDO reads bypass callbacks, so the guard cannot recurse or write anything.
        $row = $db->getPdo()->query('SELECT DATABASE() db, CURRENT_USER() usr, @@datadir datadir, @@server_uuid uuid, @@port port, VERSION() version')->fetch(\PDO::FETCH_ASSOC);
        $expectedPath = realpath(self::value('DATADIR'));
        $parent = realpath(dirname(__DIR__, 2).'/storage/app/seo-m4-2-local');
        if (! $expectedPath || ! $parent || ! str_starts_with($expectedPath, $parent.DIRECTORY_SEPARATOR)
            || realpath($row['datadir']) !== $expectedPath
            || $row['db'] !== $database || $row['usr'] !== $user.'@127.0.0.1'
            || $row['uuid'] !== self::value('SERVER_UUID') || (string) $row['port'] !== self::value('PORT')
            || str_contains($row['version'], 'MariaDB')) {
            throw new RuntimeException('Actual MySQL instance/database ownership differs from the run.');
        }
        $marker = $db->getPdo()->query('SELECT run_id FROM m42_ownership')->fetchColumn();
        if ($marker !== self::value('RUN_ID')) {
            throw new RuntimeException('Missing database ownership marker.');
        }
        foreach ($db->getPdo()->query('SHOW GRANTS')->fetchAll(\PDO::FETCH_COLUMN) as $grant) {
            if (! preg_match('/^GRANT USAGE ON \*\.\* TO /', $grant)
                && ! str_starts_with($grant, 'GRANT ALL PRIVILEGES ON `'.$database.'`.* TO ')) {
                throw new RuntimeException('Test user has unexpected privileges.');
            }
            if (str_contains($grant, 'WITH GRANT OPTION')) {
                throw new RuntimeException('Test user must not delegate privileges.');
            }
        }
    }

    public static function metadata(): array
    {
        $db = DB::connection();
        self::assertSafeDatabase($db);

        return (array) $db->selectOne('SELECT VERSION() version, @@version_comment version_comment, @@session.sql_mode sql_mode, @@global.sql_mode server_sql_mode, @@character_set_connection charset, @@collation_connection collation, @@collation_server server_collation') + [
            'php' => PHP_VERSION, 'pdo_client' => $db->getPdo()->getAttribute(\PDO::ATTR_CLIENT_VERSION),
            'laravel' => app()->version(), 'driver' => $db->getDriverName(),
            'option_reserved' => (int) $db->selectOne("SELECT RESERVED reserved_flag FROM INFORMATION_SCHEMA.KEYWORDS WHERE WORD = 'OPTION'")->reserved_flag,
        ];
    }
}
