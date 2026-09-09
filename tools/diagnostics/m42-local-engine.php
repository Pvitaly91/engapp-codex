<?php

// Read-only working MariaDB path + private SQLite metadata. No content/credentials output.
if (PHP_SAPI !== 'cli' || ($argv[1] ?? '') !== '--local-read-only'
    || filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN)) {
    exit(2);
}
chdir(dirname(__DIR__, 2));
require 'vendor/autoload.php';
$environment = Dotenv\Dotenv::parse(file_get_contents('.env'));
if (($environment['DB_CONNECTION'] ?? '') !== 'mysql'
    || ! in_array($environment['DB_HOST'] ?? '', ['localhost', '127.0.0.1'], true)) {
    throw new RuntimeException('Only the existing loopback MySQL/MariaDB configuration is permitted.');
}
require 'tests/bootstrap.php';
$app = require 'bootstrap/app.php';
Tests\Support\IsolatedTestEnvironment::configure($app);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$sqlite = Illuminate\Support\Facades\DB::connection();
Tests\Support\IsolatedTestEnvironment::assertSafeDatabase($sqlite);
$record = ['time_utc' => gmdate('c'), 'php' => PHP_VERSION, 'laravel' => $app->version(),
    'sqlite' => ['version' => $sqlite->selectOne('SELECT sqlite_version() version')->version,
        'driver' => $sqlite->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME),
        'charset' => $sqlite->selectOne('PRAGMA encoding')->encoding, 'sql_mode' => 'not applicable']];
$config = config('database.connections.mysql');
foreach (['host', 'port', 'database', 'username', 'password'] as $key) {
    $config[$key] = $environment['DB_'.strtoupper($key)] ?? $config[$key];
}
$config['url'] = null;
config(['database.default' => 'mysql', 'database.connections.mysql' => $config]);
unset($environment, $config);
$db = Illuminate\Support\Facades\DB::connection();
$pdo = $db->getPdo();
$pdo->exec('SET SESSION TRANSACTION READ ONLY');
$pdo->beginTransaction();
$db->beforeExecuting(static function (string $sql): void {
    if (! preg_match('/^\s*(select|show)\b/i', $sql)
        || preg_match('/\b(into\s+(outfile|dumpfile)|for\s+update|lock\s+in)\b/i', $sql)) {
        throw new RuntimeException('Only read-only diagnostic SQL is permitted.');
    }
});
try {
    $record['local_mysql_driver'] = (array) $db->selectOne('SELECT VERSION() version, @@version_comment version_comment, @@session.sql_mode sql_mode, @@global.sql_mode server_sql_mode, @@character_set_connection charset, @@collation_connection collation, @@collation_server server_collation');
    $record['local_mysql_driver']['pdo_client'] = $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);
    $record['usable_builder_exists'] = app(App\Services\GrammarTestFilterService::class)
        ->constrainUsableQuestions(App\Models\Question::query())->exists();
    $record['read_only_transaction'] = true;
} finally {
    $pdo->rollBack();
}
$path = 'storage/app/seo-m4-2-local/local-engine-'.bin2hex(random_bytes(8)).'.json';
$handle = fopen($path, 'x');
fwrite($handle, json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
fclose($handle);
echo json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL.'Evidence: '.$path.PHP_EOL;
