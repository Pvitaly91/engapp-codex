<?php

declare(strict_types=1);

/**
 * PERF-31 private FastCGI entrypoint; never install as a public index.
 * A dedicated Nginx loopback listener must pass a constant PERF31_GATE
 * FastCGI parameter (not a client-header mapping) to a dedicated FPM pool.
 */
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\MySqlConnection;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Http\Request;

function perf31Check(bool $ok, string $tag): void
{
    if (!$ok) {
        throw new LogicException($tag);
    }
}

function perf31Error(int $status, string $reason): void
{
    http_response_code($status);
    header('Content-Type: text/plain; charset=UTF-8');
    header('Cache-Control: no-store');
    echo "PERF-31 shadow: $reason\n";
}

function perf31Physical(string $path): bool
{
    return $path !== '' && realpath($path) === $path;
}

function perf31Receipt(string $scratch, string $id, string $stage, array $data): void
{
    $path = "$scratch/receipts/$id.$stage.json";
    $tmp = "$path.".bin2hex(random_bytes(6));
    $handle = fopen($tmp, 'xb');
    if ($handle === false) {
        throw new RuntimeException('private-receipt-create');
    }
    try {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)."\n";
        perf31Check(chmod($tmp, 0600), 'private-receipt-mode');
        perf31Check(fwrite($handle, $json) === strlen($json), 'private-receipt-write');
        perf31Check(fflush($handle) && fsync($handle), 'private-receipt-flush');
        // No replacing an older receipt, even if a correlation ID is reused.
        perf31Check(link($tmp, $path), 'private-receipt-link');
    } finally {
        fclose($handle);
        unlink($tmp);
    }
}

function perf31CpuUs(): ?int
{
    if (!function_exists('getrusage')) {
        return null;
    }
    $r = getrusage();
    if (!is_array($r) || !isset($r['ru_utime.tv_sec'], $r['ru_utime.tv_usec'], $r['ru_stime.tv_sec'], $r['ru_stime.tv_usec'])) {
        return null;
    }
    return ($r['ru_utime.tv_sec'] + $r['ru_stime.tv_sec']) * 1000000 + $r['ru_utime.tv_usec'] + $r['ru_stime.tv_usec'];
}

ini_set('display_errors', '0');
// Do not emit raw query messages, DSNs, request data, stack traces or .env.
ini_set('log_errors', '0');
$nonce = (string)getenv('PERF31_NONCE', true);
$port = (string)getenv('PERF31_PORT', true);
$variant = (string)getenv('PERF31_VARIANT', true);
$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
$gate = (string)($_SERVER['PERF31_GATE'] ?? '');

if (PHP_SAPI !== 'fpm-fcgi'
    || PHP_VERSION_ID < 80500 || PHP_VERSION_ID >= 90000
    || ($_SERVER['REMOTE_ADDR'] ?? null) !== '127.0.0.1'
    || !preg_match('/^[a-f0-9]{64}$/D', $nonce)
    || !hash_equals($nonce, $gate)
    || !preg_match('/^(?:18|28)\d{3}$/D', $port)
    || (string)($_SERVER['SERVER_PORT'] ?? '') !== $port
    || !in_array($variant, ['a', 'b', 'c'], true)
    || !in_array($host, ['gramlyze.ub', 'gramlyze.ub:'.$port], true)
    || isset($_SERVER['HTTP_AUTHORIZATION']) || isset($_SERVER['PHP_AUTH_USER'])
) {
    perf31Error(403, 'private listener only');
    return;
}

$cookie = 'perf31_'.$variant;
foreach (explode(';', (string)($_SERVER['HTTP_COOKIE'] ?? '')) as $pair) {
    if (trim($pair) === '') {
        continue;
    }
    $name = trim(explode('=', $pair, 2)[0]);
    if (!in_array($name, [$cookie, 'XSRF-TOKEN'], true)) {
        perf31Error(403, 'non-shadow cookie rejected');
        return;
    }
}

$method = (string)($_SERVER['REQUEST_METHOD'] ?? '');
$uri = (string)($_SERVER['REQUEST_URI'] ?? '');
$path = parse_url($uri, PHP_URL_PATH);
$card = '/test/passive-voice/theory-passive-voice-present-simple';
$paths = [
    '/', '/en', '/pl', '/en/', '/pl/',
    '/test/present-simple/forms', '/test/past-simple/forms', '/test/past-perfect/forms',
    '/test/word-order/theory-basic-word-order/step', $card,
    '/theory/passive-voice/theory-passive-voice-present-simple',
    '/test/sentence-builder-to-be-a1/step/compose',
];
foreach (['/en', '/pl'] as $prefix) {
    foreach (array_slice($paths, 5, 7) as $candidate) {
        $paths[] = $prefix.$candidate;
    }
}
$dictionary = in_array($uri, ['/api/word-search/uk?q=go', '/api/word-search/en?q=go', '/api/word-search/pl?q=go'], true);
$state = $method === 'POST' && $uri === $card.'/state'
    && getenv('PERF31_STATE', true) === '1';
$read = in_array($method, ['GET', 'HEAD'], true) && ($dictionary || in_array($uri, $paths, true));
if (!$read && !$state) {
    perf31Error(404, 'outside test allowlist');
    return;
}
if ($state) {
    $type = (string)($_SERVER['CONTENT_TYPE'] ?? '');
    $bytes = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
    if (!array_key_exists($cookie, $_COOKIE)
        || !preg_match('~^application/json(?:;|$)~i', $type) || $bytes < 2 || $bytes > 2600000) {
        perf31Error(400, 'only isolated JSON state is allowed');
        return;
    }
}

$scratch = (string)getenv('PERF31_SCRATCH', true);
$source = (string)getenv('PERF31_SOURCE', true);
$locales = (string)getenv('PERF31_LOCALES', true);
$id = bin2hex(random_bytes(12));
$begun = hrtime(true);
$cpuBefore = perf31CpuUs();
$stage = 'private-paths';
$pdo = null;
$blocked = 0;
$sqlCount = 0;
$sqlMs = 0.0;
$startedReceipt = false;
$outcome = [];

try {
    $safe = '~^/var/www/gramlyze-perf31[a-zA-Z0-9_.-]*(?:/[a-zA-Z0-9_.-]+)*$~D';
    perf31Check(preg_match($safe, $scratch) === 1 && perf31Physical($scratch) && is_dir($scratch)
        && is_writable($scratch), 'unverified-scratch');
    perf31Check(($source === '/var/www/gramlyze.ub-e7b4c0' || preg_match($safe, $source) === 1)
        && perf31Physical($source) && $source !== $scratch
        && !str_starts_with($scratch.'/', $source.'/') && !str_starts_with($source.'/', $scratch.'/')
        && perf31Physical("$source/public") && perf31Physical("$source/bootstrap/app.php")
        && perf31Physical("$source/vendor/autoload.php") && perf31Physical("$source/.env")
        && is_readable("$source/.env"), 'unverified-source');
    perf31Check(in_array($locales, ['', 'all'], true), 'unknown-locale-profile');
    umask(0077);
    foreach (['bootstrap/cache', 'storage/framework/views', 'storage/framework/sessions',
        'storage/framework/cache/data', 'storage/app/public', 'storage/logs', 'receipts'] as $dir) {
        $leaf = "$scratch/$dir";
        perf31Check((is_dir($leaf) || mkdir($leaf, 0700, true) || is_dir($leaf))
            && perf31Physical($leaf) && is_writable($leaf), 'unverified-private-directory');
    }
    perf31Receipt($scratch, $id, 'start', ['id' => $id, 'variant' => $variant, 'path' => $path, 'method' => $method,
        'utc' => gmdate('c')]);
    $startedReceipt = true;
    unset($_ENV['APP_BASE_PATH'], $_SERVER['APP_BASE_PATH']);
    putenv('APP_BASE_PATH');
    foreach (['APP_CONFIG_CACHE' => 'config', 'APP_ROUTES_CACHE' => 'routes',
        'APP_SERVICES_CACHE' => 'services', 'APP_PACKAGES_CACHE' => 'packages', 'APP_EVENTS_CACHE' => 'events'] as $env => $name) {
        $cache = "$scratch/bootstrap/cache/$name.php";
        perf31Check(!is_link($cache) && (!file_exists($cache) || perf31Physical($cache)), 'unverified-private-cache');
        // Disallow a precompiled config, which could bypass the selected .env.
        perf31Check($name !== 'config' || !file_exists($cache), 'precompiled-config-not-permitted');
        putenv("$env=$cache");
        $_ENV[$env] = $_SERVER[$env] = $cache;
    }
    perf31Check(chdir("$source/public"), 'app-chdir');
    define('LARAVEL_START', microtime(true));
    require "$source/vendor/autoload.php";
    $app = require "$source/bootstrap/app.php";
    perf31Check($app->basePath() === $source, 'unexpected-application-root');
    // Override only writable/runtime paths. Keep original bootstrap definitions.
    $app->useEnvironmentPath($source);
    $app->useStoragePath("$scratch/storage");
    $app->addAbsoluteCachePathPrefix($scratch);
    $baseUrl = 'http://gramlyze.ub:'.$port;
    // Keep generated URLs inside the same private port even when a probe sends
    // the canonical Host without a port. Client-forwarded origins are ignored.
    $_SERVER['HTTP_HOST'] = 'gramlyze.ub:'.$port;
    $_SERVER['SERVER_NAME'] = 'gramlyze.ub';
    $_SERVER['HTTPS'] = 'off';
    unset($_SERVER['HTTP_FORWARDED'], $_SERVER['HTTP_X_FORWARDED_HOST'], $_SERVER['HTTP_X_FORWARDED_PROTO'],
        $_SERVER['HTTP_X_FORWARDED_PORT'], $_SERVER['HTTP_X_FORWARDED_PREFIX'], $_SERVER['HTTP_X_FORWARDED_FOR']);

    $stage = 'private-configuration';
    Connection::resolverFor('mysql', static function ($pdo, $database, $prefix, $config) use (&$blocked): MySqlConnection {
        $connection = new MySqlConnection($pdo, $database, $prefix, $config);
        $connection->beforeExecuting(static function (string $sql) use (&$blocked): void {
            if (!preg_match('/^\s*(select|show|explain)\s/i', $sql)
                || preg_match('/;\s*\S|\binto\s+(?:outfile|dumpfile|@)|\bfor\s+update|\bget_lock\s*\(/i', $sql)) {
                $blocked++;
                throw new LogicException('non-read-sql-denied');
            }
        });
        return $connection;
    });
    $app->afterBootstrapping(LoadConfiguration::class, static function ($app) use ($scratch, $nonce, $variant,
        $cookie, $baseUrl, $locales): void {
        $cfg = $app['config'];
        $db = $cfg->get('database.connections.mysql');
        perf31Check($cfg->get('database.default') === 'mysql' && is_array($db) && ($db['driver'] ?? null) === 'mysql'
            && in_array($db['host'] ?? null, ['localhost', '127.0.0.1', '::1'], true)
            && (int)($db['port'] ?? 0) === 3306 && empty($db['url']) && empty($db['read']) && empty($db['write'])
            && is_string($db['database'] ?? null) && $db['database'] !== '', 'unverified-local-mysql');
        if (!empty($db['unix_socket'])) {
            perf31Check(in_array(realpath($db['unix_socket']), ['/run/mysqld/mysqld.sock'], true), 'unexpected-mysql-socket');
        }
        $db['options'][Pdo\Mysql::ATTR_INIT_COMMAND] = 'SET SESSION TRANSACTION READ ONLY';
        $db['options'][Pdo\Mysql::ATTR_MULTI_STATEMENTS] = false;
        $db['options'][PDO::ATTR_PERSISTENT] = false;
        $cfg->set([
            'database.connections' => ['mysql' => $db],
            'database.redis' => [],
            'app.url' => $baseUrl,
            'app.asset_url' => $baseUrl,
            'app.key' => 'base64:'.base64_encode(hash('sha256', "$nonce:$variant", true)),
            'app.previous_keys' => [],
            'app.debug' => false,
            'debugbar.enabled' => false,
            'telescope.enabled' => false,
            'session.driver' => 'file',
            'session.files' => "$scratch/storage/framework/sessions",
            'session.cookie' => $cookie,
            'session.domain' => null,
            'session.secure' => false,
            'session.store' => null,
            'session.connection' => null,
            'session.lottery' => [0, 1],
            'cache.default' => 'array',
            'cache.stores' => [
                'array' => ['driver' => 'array', 'serialize' => false],
                'file' => ['driver' => 'file', 'path' => "$scratch/storage/framework/cache/data", 'lock_path' => "$scratch/storage/framework/cache/data"],
            ],
            'view.compiled' => "$scratch/storage/framework/views",
            'filesystems.default' => 'local',
            'filesystems.disks' => [
                'local' => ['driver' => 'local', 'root' => "$scratch/storage/app", 'throw' => true],
                'public' => ['driver' => 'local', 'root' => "$scratch/storage/app/public", 'url' => "$baseUrl/storage", 'throw' => true],
            ],
            'theory-menu.path' => "$scratch/storage/app/theory-menu",
            'queue.default' => 'sync',
            'mail.default' => 'array',
            'logging.default' => 'single',
            'logging.channels.single' => ['driver' => 'single', 'path' => "$scratch/storage/logs/laravel.log", 'level' => 'emergency'],
        ]);
        if ($locales === 'all') {
            $cfg->set('site-mode.production_locales', ['uk', 'en', 'pl']);
        }
    });
    $request = Request::capture();
    $app->instance('request', $request);
    $kernel = $app->make(Kernel::class);
    $app['events']->listen(QueryExecuted::class, static function (QueryExecuted $event) use (&$sqlCount, &$sqlMs): void {
        $sqlCount++;
        $sqlMs += $event->time;
    });
    $kernel->bootstrap();
    perf31Check($app['config']->get('session.files') === "$scratch/storage/framework/sessions"
        && $app['config']->get('session.cookie') === $cookie
        && $app['config']->get('session.driver') === 'file'
        && $app['config']->get('cache.default') === 'array', 'private-state-not-active');
    $app['url']->forceRootUrl($baseUrl);
    $app['url']->forceScheme('http');

    $stage = 'read-only-database';
    $connection = $app->make('db')->connection();
    $connection->setReconnector(static function (): never {
        throw new LogicException('db-reconnect-denied');
    });
    $pdo = $connection->getPdo();
    $flags = $pdo->query("SHOW SESSION VARIABLES WHERE Variable_name IN ('tx_read_only','transaction_read_only')")
        ->fetchAll(PDO::FETCH_COLUMN, 1);
    perf31Check($flags !== [] && array_diff($flags, ['ON', '1']) === [], 'db-readonly-unconfirmed');
    $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    $pdo->exec('START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY');
    perf31Check($pdo->inTransaction(), 'db-readonly-snapshot-not-active');
    $connection->setReadPdo($pdo);

    $stage = 'request-kernel';
    $bootEnd = hrtime(true);
    $memoryBefore = memory_get_usage(true);
    memory_reset_peak_usage();
    $response = $kernel->handle($request);
    $kernelEnd = hrtime(true);
    // Terminate while the DB guard is still active, before recording/sending a PASS.
    $kernel->terminate($request, $response);
    perf31Check($pdo->inTransaction() && $blocked === 0, 'database-guard-violation');
    $pdo->rollBack();
    $peak = memory_get_peak_usage(true);
    $cpuAfter = perf31CpuUs();
    $body = $response->getContent();
    $outcome = [
        'id' => $id, 'variant' => $variant, 'path' => $path, 'method' => $method, 'utc' => gmdate('c'),
        'status' => $response->getStatusCode(), 'route' => $request->route()?->getName(), 'locale' => $app->getLocale(),
        'localeProfile' => $locales === 'all' ? 'explicit-shadow-all' : 'source',
        'sapi' => PHP_SAPI, 'php' => PHP_VERSION, 'readonly' => true, 'blockedSql' => $blocked,
        'sqlCount' => $sqlCount, 'sqlMs' => round($sqlMs, 3), 'bootUs' => intdiv($bootEnd - $begun, 1000),
        'kernelUs' => intdiv($kernelEnd - $bootEnd, 1000), 'phpUs' => intdiv(hrtime(true) - $begun, 1000),
        'cpuUs' => $cpuBefore !== null && $cpuAfter !== null ? $cpuAfter - $cpuBefore : null,
        'memoryBefore' => $memoryBefore, 'peakBytes' => $peak, 'decodedBodyBytes' => is_string($body) ? strlen($body) : null,
    ];
    $stage = 'finish-receipt';
    perf31Receipt($scratch, $id, 'finish', $outcome);
    $response->headers->set('X-Gramlyze-Perf31-Isolated', $nonce);
    $response->headers->set('X-Gramlyze-Perf31-Id', $id);
    $response->headers->set('Cache-Control', 'private, no-store');
    $response->send();
} catch (Throwable $error) {
    // Permit only static stage labels and our own short error tags; never dump input.
    $tag = $error instanceof LogicException && preg_match('/^[a-z0-9-]{1,60}$/D', $error->getMessage())
        ? $error->getMessage() : null;
    if ($startedReceipt && $stage !== 'finish-receipt') {
        try {
            perf31Receipt($scratch, $id, 'finish', [
                'id' => $id, 'variant' => $variant, 'path' => $path, 'status' => 500, 'utc' => gmdate('c'),
                'stage' => $stage, 'tag' => $tag, 'errorClass' => $error::class, 'blockedSql' => $blocked,
                'sourceFile' => basename($error->getFile()), 'sourceLine' => $error->getLine(),
            ]);
        } catch (Throwable) {
            // No unsafe secondary report if the private directory is unavailable.
        }
    }
    if (!headers_sent()) {
        perf31Error(500, $stage.($tag === null ? '' : '/'.$tag));
    }
} finally {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
