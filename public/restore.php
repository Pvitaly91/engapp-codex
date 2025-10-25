<?php

declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);
set_time_limit(0);

header('Content-Type: text/plain; charset=utf-8');

try {
    $basePath = dirname(__DIR__);
    $envPath = $basePath . '/.env';

    if (! is_readable($envPath)) {
        throw new RuntimeException('.env file is missing or not readable.');
    }

    $env = loadEnv($envPath);

    enforceBasicAuth(
        getEnvValue($env, 'RESTORE_AUTH_USERNAME'),
        getEnvValue($env, 'RESTORE_AUTH_PASSWORD')
    );

    $branch = getEnvValue($env, 'DEPLOYMENT_RESTORE_BRANCH');
    $owner = getEnvValue($env, 'DEPLOYMENT_GITHUB_OWNER');
    $repo = getEnvValue($env, 'DEPLOYMENT_GITHUB_REPO');
    $token = getEnvValue($env, 'DEPLOYMENT_GITHUB_TOKEN');
    $userAgent = getEnvValue($env, 'DEPLOYMENT_GITHUB_USER_AGENT', 'EngappDeploymentBot/1.0');

    if ($branch === null || $branch === '') {
        throw new RuntimeException('DEPLOYMENT_RESTORE_BRANCH is not configured in .env.');
    }

    foreach ([
        'DEPLOYMENT_GITHUB_OWNER' => $owner,
        'DEPLOYMENT_GITHUB_REPO' => $repo,
    ] as $key => $value) {
        if ($value === null || $value === '') {
            throw new RuntimeException($key . ' is not configured in .env.');
        }
    }

    $logs = [];
    $logs[] = 'Starting restore process for branch: ' . $branch;

    $branchInfo = githubApiRequest(
        sprintf('https://api.github.com/repos/%s/%s/branches/%s', rawurlencode($owner), rawurlencode($repo), rawurlencode($branch)),
        $token,
        $userAgent
    );

    $commitSha = $branchInfo['commit']['sha'] ?? null;
    if (! is_string($commitSha) || $commitSha === '') {
        throw new RuntimeException('Unable to determine latest commit SHA for branch ' . $branch . '.');
    }

    $logs[] = 'Latest commit on branch ' . $branch . ': ' . $commitSha;

    $downloadUrl = sprintf('https://api.github.com/repos/%s/%s/zipball/%s', rawurlencode($owner), rawurlencode($repo), rawurlencode($branch));
    $logs[] = 'Downloading archive from ' . $downloadUrl;

    $zipFile = downloadArchive($downloadUrl, $token, $userAgent);
    $logs[] = 'Archive downloaded to ' . $zipFile;

    $extractPath = extractArchive($zipFile);
    $logs[] = 'Archive extracted to ' . $extractPath;

    $sourceRoot = findFirstDirectory($extractPath);
    if ($sourceRoot === null) {
        throw new RuntimeException('Unable to determine extracted repository directory.');
    }

    $logs[] = 'Using extracted directory: ' . $sourceRoot;

    replaceWorkingTree($sourceRoot, $basePath);
    $logs[] = 'Working tree successfully replaced.';

    updateHeadCommit($basePath, $commitSha);
    $logs[] = 'HEAD reference updated to commit ' . $commitSha;

    cleanup([$zipFile, $extractPath]);

    $logs[] = 'Temporary files removed.';

    echo "Restore completed successfully." . PHP_EOL . PHP_EOL;
    foreach ($logs as $line) {
        echo ' - ' . $line . PHP_EOL;
    }
} catch (Throwable $exception) {
    http_response_code(500);
    echo 'Restore failed: ' . $exception->getMessage() . PHP_EOL;
}

function loadEnv(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' && ($value[0] === '"' || $value[0] === '\'')) {
            $quote = $value[0];
            $value = trim($value, $quote);
        }

        $values[$key] = $value;
    }

    return $values;
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

function enforceBasicAuth(?string $expectedUser, ?string $expectedPassword): void
{
    if ($expectedUser === null || $expectedUser === '' || $expectedPassword === null) {
        throw new RuntimeException('RESTORE_AUTH_USERNAME or RESTORE_AUTH_PASSWORD is not configured in .env.');
    }

    $providedUser = $_SERVER['PHP_AUTH_USER'] ?? null;
    $providedPassword = $_SERVER['PHP_AUTH_PW'] ?? null;

    if (($providedUser === null || $providedPassword === null)) {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
        if (is_string($header) && stripos($header, 'basic ') === 0) {
            $decoded = base64_decode(substr($header, 6), true);
            if ($decoded !== false) {
                [$providedUser, $providedPassword] = array_pad(explode(':', $decoded, 2), 2, null);
            }
        }
    }

    $userMatches = is_string($providedUser) && timingSafeEquals($expectedUser, $providedUser);
    $passwordMatches = is_string($providedPassword) && timingSafeEquals($expectedPassword, $providedPassword);

    if (! ($userMatches && $passwordMatches)) {
        header('WWW-Authenticate: Basic realm="Restore"');
        http_response_code(401);
        echo 'Authentication required.' . PHP_EOL;
        exit;
    }
}

function githubApiRequest(string $url, ?string $token, string $userAgent): array
{
    $ch = curl_init($url);

    if ($ch === false) {
        throw new RuntimeException('Unable to initialize curl.');
    }

    $headers = [
        'Accept: application/vnd.github+json',
        'User-Agent: ' . $userAgent,
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 120,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('GitHub API request failed: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('GitHub API returned status ' . $status . ' with body: ' . $response);
    }

    $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

    if (! is_array($data)) {
        throw new RuntimeException('Invalid JSON response from GitHub API.');
    }

    return $data;
}

function downloadArchive(string $url, ?string $token, string $userAgent): string
{
    $tempFile = tempnam(sys_get_temp_dir(), 'restore_');

    if ($tempFile === false) {
        throw new RuntimeException('Unable to create temporary file.');
    }

    $fp = fopen($tempFile, 'wb');

    if ($fp === false) {
        throw new RuntimeException('Unable to open temporary file for writing.');
    }

    $ch = curl_init($url);

    if ($ch === false) {
        fclose($fp);
        throw new RuntimeException('Unable to initialize curl for archive download.');
    }

    $headers = [
        'User-Agent: ' . $userAgent,
        'Accept: application/vnd.github+json',
    ];

    if ($token) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt_array($ch, [
        CURLOPT_FILE => $fp,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 300,
    ]);

    $result = curl_exec($ch);

    if ($result === false) {
        $error = curl_error($ch);
        curl_close($ch);
        fclose($fp);
        throw new RuntimeException('Archive download failed: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);

    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('Archive download failed with status ' . $status);
    }

    return $tempFile;
}

function extractArchive(string $zipFile): string
{
    $zip = new ZipArchive();
    $openResult = $zip->open($zipFile);

    if ($openResult !== true) {
        throw new RuntimeException('Unable to open downloaded archive.');
    }

    $extractPath = sys_get_temp_dir() . '/restore_' . uniqid('', true);

    if (! mkdir($extractPath) && ! is_dir($extractPath)) {
        throw new RuntimeException('Unable to create extract directory.');
    }

    if (! $zip->extractTo($extractPath)) {
        $zip->close();
        throw new RuntimeException('Failed to extract archive.');
    }

    $zip->close();

    return $extractPath;
}

function findFirstDirectory(string $path): ?string
{
    $items = scandir($path);

    if ($items === false) {
        return null;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $candidate = $path . DIRECTORY_SEPARATOR . $item;

        if (is_dir($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function replaceWorkingTree(string $source, string $destination): void
{
    $preserve = ['.git', '.env', 'storage', 'vendor', 'node_modules'];

    purgeDirectory($destination, $preserve);
    copyDirectory($source, $destination, $preserve);
}

function purgeDirectory(string $path, array $preserve): void
{
    $items = scandir($path);

    if ($items === false) {
        throw new RuntimeException('Unable to list directory: ' . $path);
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        if (in_array($item, $preserve, true)) {
            continue;
        }

        $fullPath = $path . DIRECTORY_SEPARATOR . $item;

        if (is_dir($fullPath)) {
            deleteDirectory($fullPath);
        } else {
            if (! unlink($fullPath)) {
                throw new RuntimeException('Unable to delete file: ' . $fullPath);
            }
        }
    }
}

function deleteDirectory(string $directory): void
{
    $items = scandir($directory);
    if ($items === false) {
        throw new RuntimeException('Unable to list directory: ' . $directory);
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $fullPath = $directory . DIRECTORY_SEPARATOR . $item;
        if (is_dir($fullPath)) {
            deleteDirectory($fullPath);
        } else {
            if (! unlink($fullPath)) {
                throw new RuntimeException('Unable to delete file: ' . $fullPath);
            }
        }
    }

    if (! rmdir($directory)) {
        throw new RuntimeException('Unable to remove directory: ' . $directory);
    }
}

function copyDirectory(string $source, string $destination, array $preserve, string $prefix = ''): void
{
    $items = scandir($source);

    if ($items === false) {
        throw new RuntimeException('Unable to list directory: ' . $source);
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $relative = ltrim($prefix . '/' . $item, '/');
        $topLevel = explode('/', $relative, 2)[0];

        if (in_array($topLevel, $preserve, true)) {
            continue;
        }

        $sourcePath = $source . DIRECTORY_SEPARATOR . $item;
        $targetPath = $destination . DIRECTORY_SEPARATOR . $relative;

        if (is_dir($sourcePath)) {
            if (! is_dir($targetPath) && ! mkdir($targetPath, 0777, true) && ! is_dir($targetPath)) {
                throw new RuntimeException('Unable to create directory: ' . $targetPath);
            }
            copyDirectory($sourcePath, $destination, $preserve, $relative);
        } else {
            $dir = dirname($targetPath);
            if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
                throw new RuntimeException('Unable to create directory: ' . $dir);
            }

            if (! copy($sourcePath, $targetPath)) {
                throw new RuntimeException('Unable to copy file to ' . $targetPath);
            }
        }
    }
}

function updateHeadCommit(string $repositoryPath, string $commitSha): void
{
    $headFile = $repositoryPath . '/.git/HEAD';

    if (! is_file($headFile)) {
        return;
    }

    $headContent = trim((string) file_get_contents($headFile));

    if ($headContent === '') {
        return;
    }

    if (str_starts_with($headContent, 'ref:')) {
        $ref = trim(substr($headContent, 4));
        $refPath = $repositoryPath . '/.git/' . ltrim($ref, '/');
        $dir = dirname($refPath);
        if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new RuntimeException('Unable to create directory for ref: ' . $dir);
        }
        file_put_contents($refPath, $commitSha . PHP_EOL);
    } else {
        file_put_contents($headFile, $commitSha . PHP_EOL);
    }
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

if (! function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (! function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) === 0;
    }
}

function cleanup(array $paths): void
{
    foreach ($paths as $path) {
        if ($path === null) {
            continue;
        }

        if (is_dir($path)) {
            deleteDirectory($path);
        } elseif (is_file($path)) {
            @unlink($path);
        }
    }
}
