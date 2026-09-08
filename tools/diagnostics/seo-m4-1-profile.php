<?php

/** Opt-in local CLI only. No bindings, credentials or learning content in output. */
use App\Http\Controllers\SitemapController;
use App\Services\CourseSitemapMetadataService;
use App\Services\TheoryPagePromptLinkedTestsService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\Support\IsolatedTestEnvironment;

if (PHP_SAPI !== 'cli' || ($argv[1] ?? '') !== '--local-read-only'
    || ! preg_match('/^[a-zA-Z0-9_-]+$/', $argv[2] ?? '')) {
    fwrite(STDERR, "Usage: php -d opcache.enable_cli=0 tools/diagnostics/seo-m4-1-profile.php --local-read-only LABEL\n");
    exit(2);
}
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents('.env'));
if (($environment['DB_CONNECTION'] ?? '') !== 'mysql'
    || ! in_array($environment['DB_HOST'] ?? '', ['localhost', '127.0.0.1', '::1'], true)
    || filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN)) {
    throw new RuntimeException('Requires loopback MySQL/MariaDB and CLI OPcache disabled.');
}
$output = 'storage/app/seo-m4-1-local';
if (! is_dir($output)) {
    mkdir($output, 0777, true);
}
$handle = fopen($output.'/'.$argv[2].'-profile.json', 'x');
if ($handle === false) {
    throw new RuntimeException('Evidence label already exists.');
}
fwrite($handle, json_encode(['status' => 'started', 'time_utc' => gmdate('c')])."\n");
fflush($handle);
$completed = false;
register_shutdown_function(static function () use (&$completed, $handle): void {
    if (! $completed) {
        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, json_encode(['status' => 'incomplete', 'time_utc' => gmdate('c'),
            'php_error_type' => error_get_last()['type'] ?? null])."\n");
        fclose($handle);
        exit(1);
    }
});
require 'tests/bootstrap.php';
$app = require 'bootstrap/app.php';
IsolatedTestEnvironment::configure($app);
$app->instance('request', Request::create('http://gramlyze.loc/sitemap.xml'));
$kernel = $app->make(Kernel::class);
$start = hrtime(true);
$kernel->bootstrap();
$config = config('database.connections.mysql');
foreach (['host', 'port', 'database', 'username', 'password'] as $key) {
    $config[$key] = $environment['DB_'.strtoupper($key)] ?? $config[$key];
}
$config['url'] = null;
config(['database.default' => 'mysql', 'database.connections.mysql' => $config,
    'coming-soon.enabled' => filter_var($environment['COMING_SOON_ENABLED'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
    'site-mode.production_origin' => $environment['SITE_PRODUCTION_ORIGIN'] ?? 'https://gramlyze.com',
    'site-mode.production_locales' => explode(',', $environment['SITE_PRODUCTION_LOCALES'] ?? 'uk')]);
unset($environment, $config);
$db = DB::connection();
$pdo = $db->getPdo();
$pdo->exec('SET SESSION TRANSACTION READ ONLY');
$pdo->exec('SET SESSION max_statement_time=40');
$pdo->beginTransaction();
$version = $pdo->query('SELECT VERSION()')->fetchColumn();
$db->beforeExecuting(static function (string $sql): void {
    if (! preg_match('/^\s*(select|\(select|show)\b/i', $sql)
        || preg_match('/\b(into\s+(outfile|dumpfile)|for\s+update|lock\s+in)\b/i', $sql)) {
        throw new RuntimeException('Read-only profiler rejected a non-SELECT statement.');
    }
});
$phase = 'middleware_before';
$last = hrtime(true);
$phases = [];
$queries = [];
$checkpoint = static function (string $next, int $rows) use (&$phase, &$last, &$phases): void {
    $now = hrtime(true);
    $phases[$phase] = ['wall_ms' => round(($now - $last) / 1e6, 3),
        'checkpoint_rows' => $rows, 'memory_bytes' => memory_get_usage(true)];
    $phase = $next;
    $last = $now;
};
DB::listen(static function ($event) use (&$queries, &$phase): void {
    // Kept only in this short-lived process for EXPLAIN; never serialized.
    $queries[] = ['phase' => $phase, 'sql' => $event->sql, 'bindings' => $event->bindings, 'ms' => $event->time];
});
$app->instance(SitemapController::class, new class($checkpoint) extends SitemapController
{
    public function __construct(private Closure $checkpoint) {}

    public function __invoke(TheoryPagePromptLinkedTestsService $theoryTests, CourseSitemapMetadataService $courses): Response
    {
        return $this->renderSitemap($theoryTests, $courses, $this->checkpoint);
    }
});
$requestStart = hrtime(true);
$response = $kernel->handle(Request::create('http://gramlyze.loc/sitemap.xml', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/xml']));
$checkpoint('end', 0);
$requestMs = (hrtime(true) - $requestStart) / 1e6;
$body = $response->getContent();
$summary = [];
$groups = [];
foreach ($queries as $query) {
    $key = $query['phase'];
    $groups[$key]['count'] = ($groups[$key]['count'] ?? 0) + 1;
    $groups[$key]['ms'] = ($groups[$key]['ms'] ?? 0) + $query['ms'];
    $summary[] = ['phase' => $key, 'ms' => $query['ms'], 'shape_sha256' => hash('sha256', $query['sql']),
        'sql_bytes' => strlen($query['sql']), 'bindings_count' => count($query['bindings']),
        'case_count' => substr_count(strtolower($query['sql']), 'case when'),
        'exists_count' => substr_count(strtolower($query['sql']), 'exists (')];
}
// EXPLAIN only: summarize row estimates/index access, never attached conditions.
$plans = [];
usort($queries, fn ($a, $b) => $b['ms'] <=> $a['ms']);
$planned = [];
foreach ($queries as $query) {
    if (isset($planned[$query['phase']]) || ! preg_match('/^\s*(select|\(select)\b/i', $query['sql'])) {
        continue;
    }
    $planned[$query['phase']] = true;
    $statement = $pdo->prepare('EXPLAIN FORMAT=JSON '.$query['sql']);
    $statement->execute($query['bindings']);
    $planText = $statement->fetchColumn();
    try {
        $plan = json_decode($planText, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $error) {
        $fallback = $pdo->prepare('EXPLAIN '.$query['sql']);
        $fallback->execute($query['bindings']);
        $plans[] = ['phase' => $query['phase'], 'json_error' => $error->getMessage(),
            'json_bytes' => strlen($planText), 'json_sha256' => hash('sha256', $planText),
            'tables' => array_map(fn ($row) => array_intersect_key($row,
                array_flip(['id', 'select_type', 'table', 'type', 'possible_keys', 'key', 'rows'])),
                $fallback->fetchAll(PDO::FETCH_ASSOC))];

        continue;
    }
    $tables = [];
    $walk = static function (array $node) use (&$walk, &$tables): void {
        if (isset($node['table_name'])) {
            $tables[] = array_intersect_key($node, array_flip(['table_name', 'access_type', 'key', 'possible_keys', 'rows', 'filtered']));
        }
        foreach ($node as $child) {
            if (is_array($child)) {
                $walk($child);
            }
        }
    };
    $walk($plan);
    $plans[] = ['phase' => $query['phase'], 'measured_ms' => $query['ms'], 'tables' => $tables];
}
$pdo->rollBack();
$result = ['time_utc' => gmdate('c'), 'engine' => $version, 'local_only' => true,
    'read_only_transaction' => true, 'private_storage' => true, 'ready_xml_cache' => false,
    'runtime' => storage_path(), 'status' => $response->getStatusCode(),
    'x_site_mode' => $response->headers->get('X-Site-Mode'), 'x_robots_tag' => $response->headers->get('X-Robots-Tag'),
    'coming_soon' => config('coming-soon.enabled'), 'request_ms' => round($requestMs, 3),
    'bootstrap_ms' => round(($requestStart - $start) / 1e6, 3), 'sql_ms' => array_sum(array_column($queries, 'ms')),
    'sql_count' => count($queries), 'peak_memory_bytes' => memory_get_peak_usage(true),
    'bytes' => strlen($body), 'body_sha256' => hash('sha256', $body), 'url_count' => substr_count($body, '<loc>'),
    'phases' => $phases, 'sql_groups' => $groups, 'queries' => $summary, 'plans' => $plans];
rewind($handle);
ftruncate($handle, 0);
fwrite($handle, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
fclose($handle);
$completed = true;
echo json_encode(array_diff_key($result, array_flip(['queries', 'plans', 'phases'])), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
exit($response->getStatusCode() === 200 ? 0 : 1);
