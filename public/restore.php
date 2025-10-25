<?php
declare(strict_types=1);


header('Content-Type: text/plain; charset=utf-8');

$basePath = dirname(__DIR__);
$envPath = $basePath . '/.env';
$logs = [];

try {
    if (!file_exists($envPath)) {
        throw new RuntimeException('Файл .env не знайдено.');
    }

    $env = loadEnv($envPath);

    $branch = trim($env['DEPLOYMENT_RESTORE_BRANCH'] ?? '');
    if ($branch === '') {
        throw new RuntimeException('Не вказано гілку для відновлення (DEPLOYMENT_RESTORE_BRANCH).');
    }

    $owner = trim($env['DEPLOYMENT_GITHUB_OWNER'] ?? '');
    $repo = trim($env['DEPLOYMENT_GITHUB_REPO'] ?? '');
    $token = trim($env['DEPLOYMENT_GITHUB_TOKEN'] ?? '');
    $userAgent = trim($env['DEPLOYMENT_GITHUB_USER_AGENT'] ?? 'EngappDeploymentBot/1.0');

    if ($owner === '' || $repo === '') {
        throw new RuntimeException('Налаштування GitHub (DEPLOYMENT_GITHUB_OWNER/REPO) відсутні.');
    }

    $logs[] = "Запитуємо інформацію про гілку {$branch}.";
    $branchInfo = githubJsonRequest(
        "https://api.github.com/repos/{$owner}/{$repo}/branches/" . rawurlencode($branch),
        $token,
        $userAgent
    );

    $commitSha = $branchInfo['commit']['sha'] ?? null;
    if (!is_string($commitSha) || !preg_match('/^[0-9a-f]{7,40}$/i', $commitSha)) {
        throw new RuntimeException('GitHub не повернув коректний коміт для вказаної гілки.');
    }

    $logs[] = "Останній коміт у гілці {$branch}: {$commitSha}.";
    $zipUrl = "https://api.github.com/repos/{$owner}/{$repo}/zipball/" . rawurlencode($branch);
    $logs[] = 'Завантажуємо архів гілки.';
    $zipData = githubBinaryRequest($zipUrl, $token, $userAgent);

    $tmpZip = tempnam(sys_get_temp_dir(), 'restore_zip_');
    if ($tmpZip === false) {
        throw new RuntimeException('Не вдалося створити тимчасовий файл.');
    }

    file_put_contents($tmpZip, $zipData);

    $zip = new ZipArchive();
    if ($zip->open($tmpZip) !== true) {
        throw new RuntimeException('Не вдалося відкрити завантажений архів.');
    }

    $extractDir = sys_get_temp_dir() . '/restore_' . bin2hex(random_bytes(8));
    if (!mkdir($extractDir) && !is_dir($extractDir)) {
        throw new RuntimeException('Не вдалося створити тимчасову директорію для розпакування.');
    }

    if (!$zip->extractTo($extractDir)) {
        $zip->close();
        throw new RuntimeException('Не вдалося розпакувати архів.');
    }

    $zip->close();
    unlink($tmpZip);

    $root = findFirstDirectory($extractDir);
    if ($root === null) {
        deleteDirectory($extractDir);
        throw new RuntimeException('Не вдалося знайти розпаковані файли у архіві.');
    }

    $logs[] = 'Очищуємо робочу директорію (без захищених шляхів).';
    replaceWorkingTree($basePath, $root);

    $logs[] = 'Оновлюємо локальний ref поточної гілки.';
    updateHeadRef($basePath, $commitSha);

    deleteDirectory($extractDir);

    $logs[] = 'Відновлення завершено.';

    http_response_code(200);
    echo "STATUS: success\n";
    echo "BRANCH: {$branch}\n";
    echo "COMMIT: {$commitSha}\n";
    echo "--- LOGS ---\n";
    foreach ($logs as $log) {
        echo $log . "\n";
    }
} catch (Throwable $exception) {
    http_response_code(500);
    echo "STATUS: error\n";
    echo 'MESSAGE: ' . $exception->getMessage() . "\n";
    if ($logs !== []) {
        echo "--- LOGS ---\n";
        foreach ($logs as $log) {
            echo $log . "\n";
        }
    }
}

function loadEnv(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new RuntimeException('Не вдалося прочитати файл .env.');
    }

    $env = [];

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            $quote = $value[0];
            if ($value[strlen($value) - 1] === $quote) {
                $value = substr($value, 1, -1);
            }
        }

        $env[$key] = $value;
    }

    return $env;
}

function githubJsonRequest(string $url, string $token, string $userAgent): array
{
    $response = githubRequest($url, $token, $userAgent, true);

    $data = json_decode($response, true);
    if (!is_array($data)) {
        throw new RuntimeException('Отримано некоректну відповідь від GitHub.');
    }

    if (isset($data['message']) && !isset($data['commit'])) {
        throw new RuntimeException('GitHub API: ' . $data['message']);
    }

    return $data;
}

function githubBinaryRequest(string $url, string $token, string $userAgent): string
{
    return githubRequest($url, $token, $userAgent, false);
}

function githubRequest(string $url, string $token, string $userAgent, bool $expectJson): string
{
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Не вдалося ініціалізувати cURL.');
    }

    $headers = [
        'User-Agent: ' . ($userAgent !== '' ? $userAgent : 'EngappDeploymentBot/1.0'),
        'Accept: ' . ($expectJson ? 'application/vnd.github+json' : 'application/octet-stream'),
    ];

    if ($token !== '') {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $body = curl_exec($ch);

    if ($body === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('Помилка під час запиту до GitHub: ' . $error);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status >= 400) {
        $decoded = json_decode($body, true);
        $message = is_array($decoded) && isset($decoded['message'])
            ? $decoded['message']
            : 'HTTP ' . $status;
        throw new RuntimeException('GitHub API повернув помилку: ' . $message);
    }

    return $body;
}

function findFirstDirectory(string $path): ?string
{
    $entries = scandir($path);
    if ($entries === false) {
        return null;
    }

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }

        $fullPath = $path . '/' . $entry;
        if (is_dir($fullPath)) {
            return $fullPath;
        }
    }

    return null;
}

function replaceWorkingTree(string $target, string $source): void
{
    $preserve = ['.git', '.env', 'storage', 'vendor', 'node_modules'];

    $directories = glob($target . '/*', GLOB_ONLYDIR);
    if ($directories !== false) {
        foreach ($directories as $directory) {
            $name = basename($directory);
            if (in_array($name, $preserve, true)) {
                continue;
            }
            deleteDirectory($directory);
        }
    }

    $files = glob($target . '/*');
    if ($files !== false) {
        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }
            $name = basename($file);
            if (in_array($name, $preserve, true)) {
                continue;
            }
            unlink($file);
        }
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $sourceLength = strlen(rtrim($source, '/'));

    foreach ($iterator as $item) {
        /** @var SplFileInfo $item */
        $relative = ltrim(substr($item->getPathname(), $sourceLength), '/');

        if ($relative === '') {
            continue;
        }

        $segments = explode('/', $relative);
        if (isset($segments[0]) && in_array($segments[0], $preserve, true)) {
            continue;
        }

        $destination = $target . '/' . $relative;

        if ($item->isDir()) {
            if (!is_dir($destination) && !mkdir($destination, 0777, true) && !is_dir($destination)) {
                throw new RuntimeException('Не вдалося створити директорію: ' . $destination);
            }
        } else {
            if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0777, true) && !is_dir(dirname($destination))) {
                throw new RuntimeException('Не вдалося створити директорію: ' . dirname($destination));
            }

            $contents = file_get_contents($item->getPathname());
            if ($contents === false) {
                throw new RuntimeException('Не вдалося прочитати файл: ' . $item->getPathname());
            }

            if (file_put_contents($destination, $contents) === false) {
                throw new RuntimeException('Не вдалося записати файл: ' . $destination);
            }
        }
    }
}

function deleteDirectory(string $path): void
{
    if (!is_dir($path)) {
        if (file_exists($path)) {
            unlink($path);
        }
        return;
    }

    $items = scandir($path);
    if ($items === false) {
        return;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $full = $path . '/' . $item;
        if (is_dir($full)) {
            deleteDirectory($full);
        } else {
            unlink($full);
        }
    }

    rmdir($path);
}

function updateHeadRef(string $repositoryPath, string $sha): void
{
    $headFile = $repositoryPath . '/.git/HEAD';
    if (!file_exists($headFile)) {
        return;
    }

    $headContent = trim((string) file_get_contents($headFile));
    if ($headContent === '') {
        return;
    }

    if (!str_starts_with($headContent, 'ref:')) {
        file_put_contents($headFile, $sha . "\n");
        return;
    }

    $ref = trim(substr($headContent, strlen('ref:')));
    if ($ref === '') {
        return;
    }

    $refPath = $repositoryPath . '/.git/' . ltrim($ref, '/');
    $refDir = dirname($refPath);
    if (!is_dir($refDir) && !mkdir($refDir, 0777, true) && !is_dir($refDir)) {
        throw new RuntimeException('Не вдалося створити директорію для ref: ' . $refDir);
    }

    file_put_contents($refPath, $sha . "\n");
}
