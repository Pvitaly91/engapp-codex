<?php

namespace Tests\Support;

use Illuminate\Database\Connection;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use RuntimeException;

/** Test-process runtime, established before providers can resolve singleton services. */
final class IsolatedTestEnvironment
{
    private static ?string $root = null;

    public static function prepare(): string
    {
        if (self::$root !== null) {
            return self::$root;
        }

        $requested = getenv('GRAMLYZE_TEST_RUNTIME');
        $parent = dirname(__DIR__, 2).'/storage/app/seo-m2-local';
        if (! is_dir($parent) && ! mkdir($parent, 0777, true)) {
            throw new RuntimeException('Cannot create isolated runtime parent.');
        }
        $root = $requested ?: $parent.'/test-runtime-'.bin2hex(random_bytes(16));
        if (! preg_match('/^test-runtime-[a-f0-9]{32}$/', basename($root))
            || realpath(dirname($root)) !== realpath($parent)) {
            throw new RuntimeException('Refusing an unowned test runtime path.');
        }
        if (! is_dir($root) && ! mkdir($root, 0777)) {
            throw new RuntimeException('Cannot create isolated runtime.');
        }
        if (realpath($root) !== realpath($parent).DIRECTORY_SEPARATOR.basename($root)) {
            throw new RuntimeException('Refusing a linked test runtime outside its parent.');
        }
        self::$root = realpath($root);
        foreach (['/app/public', '/app/s3', '/app/question-exports', '/framework/views', '/framework/sessions', '/framework/cache/data', '/framework/testing', '/logs', '/bootstrap', '/environment'] as $part) {
            // Check every existing ancestor before mkdir can follow a junction/symlink.
            $ancestor = $root.$part;
            while (! file_exists($ancestor)) {
                $ancestor = dirname($ancestor);
            }
            self::assertOwnedPath($ancestor);
            if (! is_dir($root.$part) && ! mkdir($root.$part, 0777, true)) {
                throw new RuntimeException('Cannot create isolated runtime directory.');
            }
            self::assertOwnedPath($root.$part);
        }
        // Laravel 10 recognizes leading / or \\ as absolute, not Windows drive prefixes.
        $cache = 'storage/app/seo-m2-local/'.basename($root).'/bootstrap';
        $values = [
            'APP_BASE_PATH' => dirname(__DIR__, 2),
            'APP_ENV' => 'testing', 'APP_DEBUG' => 'false', 'APP_URL' => 'http://gramlyze.loc',
            'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)),
            'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DATABASE_URL' => '', 'DB_URL' => '',
            'CACHE_DRIVER' => 'array', 'SESSION_DRIVER' => 'array', 'QUEUE_CONNECTION' => 'sync',
            'MAIL_MAILER' => 'array', 'LOG_CHANNEL' => 'stderr', 'FILESYSTEM_DISK' => 'local',
            'PULSE_ENABLED' => 'false', 'TELESCOPE_ENABLED' => 'false',
            'LARAVEL_STORAGE_PATH' => self::$root, 'VIEW_COMPILED_PATH' => self::$root.'/framework/views',
            'APP_CONFIG_CACHE' => $cache.'/config.php',
            'APP_ROUTES_CACHE' => $cache.'/routes.php',
            'APP_SERVICES_CACHE' => $cache.'/services.php',
            'APP_PACKAGES_CACHE' => $cache.'/packages.php',
            'APP_EVENTS_CACHE' => $cache.'/events.php',
        ];
        foreach ($values as $key => $value) {
            putenv($key.'='.$value);
            $_ENV[$key] = $_SERVER[$key] = $value;
        }

        // Nothing is removed until process exit: Livewire/Blade may retain compiled paths.
        // The private runtime is retained as diagnostic evidence, never committed.
        return self::$root;
    }

    public static function configure(Application $app): void
    {
        $root = self::prepare();
        $app->useStoragePath($root);
        // Never load the working .env (including admin credentials) in a test kernel.
        $app->useEnvironmentPath($root.'/environment');
        $app->loadEnvironmentFrom('isolated.env');
        $app->afterBootstrapping(LoadConfiguration::class, static function (Application $app) use ($root): void {
            $app['config']->set('filesystems.disks.s3', ['driver' => 'local', 'root' => $root.'/app/s3', 'throw' => true]);
            $app['config']->set('filesystems.links', []);
            $app['config']->set('questions.export_path', $root.'/app/question-exports');
        });
    }

    public static function assertOwnedPath(string $path): void
    {
        $resolved = realpath($path);
        $root = self::prepare();
        if ($resolved === false || ($resolved !== $root && ! str_starts_with($resolved, $root.DIRECTORY_SEPARATOR))) {
            throw new RuntimeException('Refusing a shared filesystem path in isolated tests.');
        }
    }

    public static function assertSafeDatabase(Connection $connection): void
    {
        if (! app()->environment('testing') || $connection->getDriverName() !== 'sqlite') {
            throw new RuntimeException('Refusing schema operations outside testing SQLite.');
        }
        // Validate ownership before a lazy PDO resolver could open/create its file.
        $database = $connection->getDatabaseName();
        if ($database !== ':memory:') {
            self::assertOwnedPath($database);
        }
        if ($connection->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME) !== 'sqlite') {
            throw new RuntimeException('Refusing schema operations outside testing SQLite.');
        }
        foreach ($connection->select('PRAGMA database_list') as $row) {
            if ($row->file !== '') {
                self::assertOwnedPath($row->file);
                if ($row->name === 'main' && realpath($row->file) !== realpath($database)) {
                    throw new RuntimeException('Actual SQLite file differs from configured test database.');
                }
            } elseif ($row->name === 'main' && $database !== ':memory:') {
                throw new RuntimeException('Actual SQLite database differs from configured test file.');
            }
        }
    }
}
