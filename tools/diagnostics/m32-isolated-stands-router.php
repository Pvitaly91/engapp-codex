<?php

// Diagnostic wrapper only: the archived application source is not modified.
if (!in_array(PHP_SAPI, ['cli-server', 'cli'], true)) exit(2);
$stand = realpath(getenv('M32_STAND') ?: '');
if (!$stand || $stand !== realpath(__DIR__)
    || !preg_match('~/storage/app/seo-m3-2-local/stands-[a-f0-9]{32}/[AB]$~', str_replace('\\', '/', $stand))) exit(3);
$source = $stand.'/source';
$runtime = $stand.'/runtime';
$base = getenv('M32_BASE') ?: '';
if (!preg_match('~^http://127\.0\.0\.1:[1-9][0-9]{3,4}$~', $base)) exit(4);
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if (PHP_SAPI === 'cli-server') {
    if (($_SERVER['HTTP_HOST'] ?? '') !== substr($base, 7) || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
        http_response_code(403); exit('Isolated GET-only stand');
    }
    $file = realpath($source.'/public'.$path);
    if ($file && is_file($file) && str_starts_with(str_replace('\\', '/', $file), str_replace('\\', '/', realpath($source.'/public')).'/')
        && preg_match('/\.(css|js|map|png|jpg|jpeg|svg|webp|ico|woff2?|ttf)$/i', $file)) return false;
    if (!preg_match('~^/theory(?:/|$)~', $path) && !preg_match('~^/livewire/livewire(?:\.min)?\.js(?:\.map)?$~', $path)) {
        http_response_code(403); exit('Outside isolated theory allowlist');
    }
}
$settings = ['APP_ENV' => 'local', 'APP_DEBUG' => 'false', 'DEBUGBAR_ENABLED' => 'false',
    'APP_URL' => $base, 'ASSET_URL' => $base, 'APP_NAME' => 'Gramlyze', 'APP_BETA' => 'false',
    'APP_KEY' => 'base64:'.base64_encode(hash('sha256', 'M32 disposable diagnostic key, not a working key', true)),
    'APP_BASE_PATH' => $source, 'APP_STORAGE_PATH' => $runtime.'/storage',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $stand.'/data.sqlite', 'DATABASE_URL' => '', 'DB_URL' => '',
    'CACHE_DRIVER' => 'file', 'SESSION_DRIVER' => 'file', 'SESSION_COOKIE' => 'm32_'.basename($stand),
    'SESSION_DOMAIN' => '', 'QUEUE_CONNECTION' => 'sync', 'MAIL_MAILER' => 'array',
    'LOG_CHANNEL' => 'single', 'LOG_LEVEL' => 'error', 'FILESYSTEM_DISK' => 'local',
    'COMING_SOON_ENABLED' => 'false', 'SITE_DEVELOPMENT_ORIGIN' => $base, 'PULSE_ENABLED' => 'false',
    'TELESCOPE_ENABLED' => 'false', 'VIEW_COMPILED_PATH' => $runtime.'/storage/framework/views'];
foreach (['CONFIG', 'ROUTES', 'PACKAGES', 'SERVICES', 'EVENTS'] as $kind) {
    // Relative paths avoid Laravel 10's Unix-only absolute-path prefix detection.
    $settings['APP_'.($kind === 'CONFIG' ? 'CONFIG_CACHE' : $kind.'_CACHE')] = '../runtime/bootstrap/'.strtolower($kind).'.php';
}
foreach ($settings as $key => $value) { putenv($key.'='.$value); $_ENV[$key] = $_SERVER[$key] = $value; }
chdir($source);
require $source.'/vendor/autoload.php';
$app = require $source.'/bootstrap/app.php';
$app->useStoragePath($runtime.'/storage');
$app->useEnvironmentPath($runtime.'/environment');
$app->loadEnvironmentFrom('absent-isolated.env');
$app->afterBootstrapping(Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) use ($stand, $runtime, $base): void {
    $data = realpath($stand.'/data.sqlite');
    if (!$data || dirname(str_replace('\\', '/', $data)) !== str_replace('\\', '/', $stand)) throw new RuntimeException('Unsafe snapshot');
    $config = $app['config'];
    $config->set('database.default', 'sqlite');
    $config->set('database.connections', ['sqlite' => ['driver' => 'sqlite', 'database' => $data, 'prefix' => '', 'foreign_key_constraints' => true]]);
    // The DB provider has not registered yet. Install the resolver before any
    // provider can request a connection, without resolving a missing service.
    $app->afterResolving('db', function ($manager) use ($data): void {
        $manager->extend('sqlite', function ($cfg) use ($data) {
            $pdo = new PDO('sqlite:file:'.str_replace('\\', '/', $data).'?mode=ro', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec('PRAGMA query_only=ON');
            if ((int) $pdo->query('PRAGMA query_only')->fetchColumn() !== 1) throw new RuntimeException('SQLite is not query-only');
            return new Illuminate\Database\SQLiteConnection($pdo, $data, '', $cfg);
        });
    });
    $config->set('filesystems.disks', ['local' => ['driver' => 'local', 'root' => $runtime.'/storage/app']]);
    $config->set('filesystems.links', []);
    $config->set('questions.export_path', $runtime.'/storage/question-exports');
    $config->set('session.files', $runtime.'/storage/framework/sessions');
    $config->set('session.domain', null);
    $config->set('site-mode.development_origin', $base);
});
$request = PHP_SAPI === 'cli' ? Illuminate\Http\Request::create($base) : Illuminate\Http\Request::capture();
$app->instance('request', $request);
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
Illuminate\Support\Facades\Http::preventStrayRequests();
if (PHP_SAPI === 'cli') {
    $db = $app['db']->connection();
    $pdo = $db->getPdo();
    $blocked = false;
    try { $pdo->exec('CREATE TABLE __m32_write_probe (id INTEGER)'); } catch (PDOException $error) { $blocked = str_contains($error->getMessage(), 'readonly'); }
    if (!$blocked) throw new RuntimeException('Read-only write proof failed');
    echo json_encode(['php' => PHP_VERSION, 'sapi' => 'cli-server', 'preflightSapi' => PHP_SAPI,
        'dataReadOnly' => true, 'queryOnly' => (int) $pdo->query('PRAGMA query_only')->fetchColumn(),
        'workingEnvLoaded' => $app->environmentPath() === $source,
        'opcacheCli' => filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOLEAN),
        'pageCount' => $db->table('pages')->count(), 'storage' => storage_path(), 'views' => config('view.compiled'),
        'session' => config('session.files'), 'cache' => config('cache.stores.file.path')]);
    exit;
}
$response = $kernel->handle($request);
// LocaleService currently drops port numbers while rebuilding localized URLs.
// This common transport shim keeps both archived variants on their own loopback
// stand; it is not an application change or part of Apache/.loc acceptance.
$normalize = static fn (string $text): string => str_replace(
    ['http://127.0.0.1/', 'http:\/\/127.0.0.1\/'],
    [$base.'/', str_replace('/', '\/', $base). '\/'],
    $text
);
if (is_string($response->getContent())) $response->setContent($normalize($response->getContent()));
if ($response->headers->has('Location')) $response->headers->set('Location', $normalize($response->headers->get('Location')));
$response->headers->set('X-M32-SAPI', PHP_SAPI);
$response->headers->set('X-M32-PHP', PHP_VERSION);
$response->headers->set('X-M32-Data-ReadOnly', 'sqlite-mode-ro-query-only');
$response->send();
$kernel->terminate($request, $response);
