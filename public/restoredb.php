<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(0);

try {
    $basePath = dirname(__DIR__);
    $envPath = $basePath . '/.env';

    if (! is_readable($envPath)) {
        throw new RuntimeException('.env file is missing or not readable.');
    }

    $env = loadEnv($envPath);
    $isCli = PHP_SAPI === 'cli';

    if (! $isCli) {
        enforceFormAuth(
            getEnvValue($env, 'RESTORE_AUTH_USERNAME'),
            getEnvValue($env, 'RESTORE_AUTH_PASSWORD')
        );

        header('Content-Type: text/plain; charset=utf-8');
    }

    $dumpPath = resolvePath(
        $basePath,
        getEnvValue($env, 'DB_DUMP_PATH', 'database/dumps/site-database.sql') ?? 'database/dumps/site-database.sql'
    );

    if (! is_file($dumpPath) || ! is_readable($dumpPath)) {
        throw new RuntimeException('Database dump is missing or not readable: ' . $dumpPath);
    }

    $pdo = createMysqlPdo($env);
    $result = restoreSqlDump($pdo, $dumpPath);

    echo 'Database restore completed successfully.' . PHP_EOL . PHP_EOL;
    echo ' - Dump: ' . $dumpPath . PHP_EOL;
    echo ' - Dump size: ' . number_format((int) filesize($dumpPath)) . ' bytes' . PHP_EOL;
    echo ' - SQL statements executed: ' . $result['statements'] . PHP_EOL;
    echo ' - Lines read: ' . $result['lines'] . PHP_EOL;
} catch (Throwable $exception) {
    if (PHP_SAPI !== 'cli') {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
    }

    echo 'Database restore failed: ' . $exception->getMessage() . PHP_EOL;
}

function loadEnv(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new RuntimeException('Unable to read .env file.');
    }

    $values = [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || $line[0] === '#') {
            continue;
        }

        if (! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = normalizeEnvKey($key);
        $value = normalizeEnvValue($value);

        if ($key === '') {
            continue;
        }

        $values[$key] = $value;
    }

    return $values;
}

function normalizeEnvKey(string $key): string
{
    $key = preg_replace('/^\xEF\xBB\xBF/', '', $key) ?? $key;

    return preg_replace('/^\s+|\s+$/u', '', $key) ?? trim($key);
}

function normalizeEnvValue(string $value): string
{
    $value = preg_replace('/^\xEF\xBB\xBF|\xEF\xBB\xBF$/', '', $value) ?? $value;
    $value = preg_replace('/^\s+|\s+$/u', '', $value) ?? trim($value);

    if ($value !== '' && ($value[0] === '"' || $value[0] === '\'')) {
        $quote = $value[0];

        if (substr($value, -1) === $quote) {
            $value = substr($value, 1, -1);
        }
    }

    return preg_replace('/^\s+|\s+$/u', '', $value) ?? trim($value);
}

function getEnvValue(array $env, string $key, ?string $default = null): ?string
{
    if (array_key_exists($key, $env)) {
        return $env[$key];
    }

    $value = getenv($key);

    if ($value === false) {
        return $default;
    }

    return $value;
}

function createMysqlPdo(array $env): PDO
{
    $connection = strtolower((string) getEnvValue($env, 'DB_CONNECTION', 'mysql'));
    if ($connection !== 'mysql') {
        throw new RuntimeException('restoredb.php supports only DB_CONNECTION=mysql.');
    }

    $database = (string) getEnvValue($env, 'DB_DATABASE', '');
    if ($database === '') {
        throw new RuntimeException('DB_DATABASE is not configured in .env.');
    }

    $host = (string) getEnvValue($env, 'DB_HOST', '127.0.0.1');
    $port = (string) getEnvValue($env, 'DB_PORT', '3306');
    $username = (string) getEnvValue($env, 'DB_USERNAME', '');
    $password = (string) getEnvValue($env, 'DB_PASSWORD', '');
    $socket = (string) getEnvValue($env, 'DB_SOCKET', '');
    $charset = sanitizeIdentifierPart((string) getEnvValue($env, 'DB_CHARSET', 'utf8mb4'), 'utf8mb4');
    $collation = sanitizeIdentifierPart((string) getEnvValue($env, 'DB_COLLATION', 'utf8mb4_unicode_ci'), 'utf8mb4_unicode_ci');

    $baseDsn = $socket !== ''
        ? 'mysql:unix_socket=' . $socket . ';charset=' . $charset
        : 'mysql:host=' . $host . ';port=' . $port . ';charset=' . $charset;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
        $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . $charset;
    }

    try {
        return new PDO($baseDsn . ';dbname=' . $database, $username, $password, $options);
    } catch (PDOException $exception) {
        if (! str_contains($exception->getMessage(), 'Unknown database')) {
            throw $exception;
        }
    }

    $pdo = new PDO($baseDsn, $username, $password, $options);
    $pdo->exec(
        'CREATE DATABASE IF NOT EXISTS ' . quoteIdentifier($database)
        . ' CHARACTER SET ' . $charset
        . ' COLLATE ' . $collation
    );
    $pdo->exec('USE ' . quoteIdentifier($database));

    return $pdo;
}

/**
 * @return array{statements: int, lines: int}
 */
function restoreSqlDump(PDO $pdo, string $dumpPath): array
{
    $handle = fopen($dumpPath, 'rb');
    if ($handle === false) {
        throw new RuntimeException('Unable to open dump file: ' . $dumpPath);
    }

    $statement = '';
    $statementCount = 0;
    $lineNumber = 0;

    try {
        while (($line = fgets($handle)) !== false) {
            $lineNumber++;

            if ($statement === '' && shouldSkipLine($line)) {
                continue;
            }

            $statement .= $line;

            if (! statementIsComplete($statement)) {
                continue;
            }

            executeSqlStatement($pdo, $statement, $lineNumber);
            $statementCount++;
            $statement = '';
        }

        if (trim($statement) !== '') {
            executeSqlStatement($pdo, $statement, $lineNumber);
            $statementCount++;
        }
    } finally {
        fclose($handle);
    }

    return [
        'statements' => $statementCount,
        'lines' => $lineNumber,
    ];
}

function shouldSkipLine(string $line): bool
{
    $trimmed = trim($line);

    return $trimmed === '' || str_starts_with($trimmed, '--');
}

function statementIsComplete(string $statement): bool
{
    $trimmed = rtrim($statement);

    return $trimmed !== '' && substr($trimmed, -1) === ';';
}

function executeSqlStatement(PDO $pdo, string $statement, int $lineNumber): void
{
    $sql = normalizePortableCreateTableSql(trim($statement));

    if ($sql === '') {
        return;
    }

    try {
        $pdo->exec($sql);
    } catch (Throwable $exception) {
        throw new RuntimeException('SQL restore failed near line ' . $lineNumber . ': ' . $exception->getMessage(), 0, $exception);
    }
}

function normalizePortableCreateTableSql(string $sql): string
{
    if (preg_match('/^\s*CREATE\s+TABLE\b/i', $sql) !== 1) {
        return $sql;
    }

    $textColumns = textColumnsFromCreateSql($sql);
    if ($textColumns === []) {
        return $sql;
    }

    $lines = explode("\n", $sql);

    foreach ($lines as $index => $line) {
        if (! isPortableIndexCandidate($line)) {
            continue;
        }

        $lines[$index] = addTextIndexPrefixes($line, $textColumns);
    }

    return implode("\n", $lines);
}

/**
 * @return array<string, true>
 */
function textColumnsFromCreateSql(string $sql): array
{
    $columns = [];

    foreach (explode("\n", $sql) as $line) {
        if (preg_match('/^\s*`([^`]+)`\s+((?:tiny|medium|long)?text|(?:tiny|medium|long)?blob|json)\b/i', $line, $matches) === 1) {
            $columns[$matches[1]] = true;
        }
    }

    return $columns;
}

function isPortableIndexCandidate(string $line): bool
{
    return preg_match('/^\s*(?:UNIQUE\s+)?KEY\s+`[^`]+`\s*\(/i', $line) === 1
        && preg_match('/^\s*(?:FULLTEXT|SPATIAL)\s+/i', $line) !== 1;
}

/**
 * @param  array<string, true>  $textColumns
 */
function addTextIndexPrefixes(string $line, array $textColumns): string
{
    $open = strpos($line, '(');
    $close = strrpos($line, ')');

    if ($open === false || $close === false || $close <= $open) {
        return $line;
    }

    $columnList = substr($line, $open + 1, $close - $open - 1);

    foreach (array_keys($textColumns) as $column) {
        $columnList = preg_replace(
            '/`' . preg_quote($column, '/') . '`(?!\s*\()/',
            '`' . str_replace('`', '``', $column) . '`(100)',
            $columnList
        ) ?? $columnList;
    }

    $suffix = preg_replace('/\s+USING\s+HASH\b/i', '', substr($line, $close));

    return substr($line, 0, $open + 1) . $columnList . $suffix;
}

function resolvePath(string $basePath, string $path): string
{
    $path = trim($path);

    if ($path === '') {
        $path = 'database/dumps/site-database.sql';
    }

    if (isAbsolutePath($path)) {
        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
    }

    return $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($path, '/\\'));
}

function isAbsolutePath(string $path): bool
{
    return preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1
        || str_starts_with($path, '/')
        || str_starts_with($path, '\\');
}

function sanitizeIdentifierPart(string $value, string $default): string
{
    $value = preg_replace('/[^A-Za-z0-9_]/', '', $value) ?? '';

    return $value !== '' ? $value : $default;
}

function quoteIdentifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function enforceFormAuth(?string $expectedUser, ?string $expectedPassword): void
{
    if ($expectedUser === null || $expectedUser === '' || $expectedPassword === null || $expectedPassword === '') {
        throw new RuntimeException('RESTORE_AUTH_USERNAME or RESTORE_AUTH_PASSWORD is not configured in .env.');
    }

    ensureSession();

    if (! empty($_SESSION['restoredb_authenticated'])) {
        return;
    }

    $error = null;
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    if ($method === 'POST') {
        $providedUser = (string) ($_POST['username'] ?? '');
        $providedPassword = (string) ($_POST['password'] ?? '');

        if (timingSafeEquals($expectedUser, $providedUser) && timingSafeEquals($expectedPassword, $providedPassword)) {
            session_regenerate_id(true);
            $_SESSION['restoredb_authenticated'] = true;

            return;
        }

        $error = 'Invalid username or password.';
    }

    renderLoginForm($error);
}

function ensureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function renderLoginForm(?string $error = null): void
{
    header('Content-Type: text/html; charset=utf-8');
    http_response_code(401);

    $errorMarkup = '';
    if ($error !== null) {
        $errorMarkup = '<p class="error">' . escapeHtml($error) . '</p>';
    }

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '    <meta charset="utf-8">';
    echo '    <meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '    <title>Database Restore Authentication</title>';
    echo '    <style>';
    echo '        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f2f2f2; margin: 0; padding: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }';
    echo '        .container { background: #fff; padding: 32px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); width: 100%; max-width: 360px; }';
    echo '        h1 { font-size: 1.5rem; margin-bottom: 1rem; text-align: center; }';
    echo '        label { display: block; font-weight: 600; margin-bottom: 0.5rem; }';
    echo '        input[type="text"], input[type="password"] { width: 100%; padding: 10px 12px; margin-bottom: 1rem; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 1rem; box-sizing: border-box; }';
    echo '        button { width: 100%; padding: 10px 12px; border: none; border-radius: 6px; background-color: #2563eb; color: #fff; font-size: 1rem; font-weight: 600; cursor: pointer; }';
    echo '        button:hover { background-color: #1d4ed8; }';
    echo '        .error { background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 1rem; text-align: center; }';
    echo '    </style>';
    echo '</head>';
    echo '<body>';
    echo '    <div class="container">';
    echo '        <h1>Database Restore Access</h1>';
    echo          $errorMarkup;
    echo '        <form method="post" action="">';
    echo '            <label for="username">Username</label>';
    echo '            <input type="text" id="username" name="username" autocomplete="username" required>';
    echo '            <label for="password">Password</label>';
    echo '            <input type="password" id="password" name="password" autocomplete="current-password" required>';
    echo '            <button type="submit">Restore database</button>';
    echo '        </form>';
    echo '    </div>';
    echo '</body>';
    echo '</html>';

    exit;
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function timingSafeEquals(string $knownString, string $userString): bool
{
    if (function_exists('hash_equals')) {
        return hash_equals($knownString, $userString);
    }

    if (strlen($knownString) !== strlen($userString)) {
        return false;
    }

    $result = 0;
    $length = strlen($knownString);

    for ($i = 0; $i < $length; $i++) {
        $result |= ord($knownString[$i]) ^ ord($userString[$i]);
    }

    return $result === 0;
}
