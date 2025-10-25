<?php

namespace App\Services\Git;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class GitHubRepositoryManager
{
    private const TEMP_PREFIX = 'deployment-github-';

    private string $basePath;

    private ?string $token;

    public function __construct(?string $token = null)
    {
        $this->basePath = base_path();
        $this->token = $token;
    }

    public function getCurrentBranch(): ?string
    {
        $headPath = $this->basePath . '/.git/HEAD';
        if (! File::exists($headPath)) {
            return null;
        }

        $content = trim(File::get($headPath));
        if (Str::startsWith($content, 'ref:')) {
            $ref = trim(Str::after($content, 'ref:'));
            if (Str::startsWith($ref, 'refs/heads/')) {
                return Str::after($ref, 'refs/heads/');
            }
        }

        return null;
    }

    public function getCurrentCommit(?string $branch = null): ?string
    {
        $branch = $branch ?: $this->getCurrentBranch();
        if (! $branch) {
            return null;
        }

        $refPath = $this->basePath . '/.git/refs/heads/' . $branch;
        if (File::exists($refPath)) {
            return trim(File::get($refPath));
        }

        $packedRefs = $this->basePath . '/.git/packed-refs';
        if (File::exists($packedRefs)) {
            $target = 'refs/heads/' . $branch;
            foreach (preg_split('/\r?\n/', File::get($packedRefs)) as $line) {
                if ($line === '' || $line[0] === '#') {
                    continue;
                }

                if (Str::contains($line, ' ')) {
                    [$sha, $ref] = explode(' ', trim($line));
                    if ($ref === $target) {
                        return trim($sha);
                    }
                }
            }
        }

        return null;
    }

    public function getRepositoryInfo(): ?array
    {
        $configPath = $this->basePath . '/.git/config';
        if (! File::exists($configPath)) {
            return null;
        }

        $content = File::get($configPath);
        if (! preg_match('/\[remote\s+"origin"\](.*?)\n\[/s', $content . "\n[", $matches)) {
            return null;
        }

        $block = $matches[1];
        if (! preg_match('/url\s*=\s*(.+)/', $block, $urlMatch)) {
            return null;
        }

        $url = trim($urlMatch[1]);
        $parsed = $this->parseGitHubUrl($url);

        return $parsed;
    }

    private function parseGitHubUrl(string $url): ?array
    {
        if (Str::startsWith($url, 'git@github.com:')) {
            $path = Str::after($url, 'git@github.com:');
        } elseif (Str::startsWith($url, 'https://github.com/')) {
            $path = Str::after($url, 'https://github.com/');
        } elseif (Str::startsWith($url, 'ssh://git@github.com/')) {
            $path = Str::after($url, 'ssh://git@github.com/');
        } else {
            return null;
        }

        $path = Str::before($path, '.git');
        $segments = explode('/', trim($path, '/'));
        if (count($segments) < 2) {
            return null;
        }

        return [
            'owner' => $segments[0],
            'repo' => $segments[1],
        ];
    }

    public function ensureGitHubSupport(): void
    {
        if (! $this->getRepositoryInfo()) {
            throw new RuntimeException('Не вдалося визначити GitHub-репозиторій з конфігурації origin.');
        }
    }

    public function deployBranch(string $branch, array &$steps): void
    {
        $info = $this->getRepositoryInfo();
        if (! $info) {
            throw new RuntimeException('У .git/config не знайдено посилання на origin GitHub.');
        }

        $branchInfo = $this->request("GET", "/repos/{$info['owner']}/{$info['repo']}/branches/{$branch}");
        $commitSha = data_get($branchInfo, 'commit.sha');
        if (! $commitSha) {
            throw new RuntimeException('Не вдалося отримати інформацію про віддалену гілку.');
        }

        $steps[] = $this->step('Отримання zip-архіву гілки', function () use ($info, $commitSha, $branch) {
            $archiveResponse = $this->request(
                'GET',
                "/repos/{$info['owner']}/{$info['repo']}/zipball/" . rawurlencode($branch),
                accept: 'application/zip'
            );

            $tempZip = $this->createTempPath('zip');
            File::put($tempZip, $archiveResponse->body());

            return $tempZip;
        });

        $zipPath = Arr::last($steps)['output'];
        $extractDirectory = $this->extractArchive($zipPath);
        $this->synchroniseWorkingTree($extractDirectory, $steps);
        File::delete($zipPath);
        File::deleteDirectory(dirname($extractDirectory));

        $this->updateHeadReference($branch, $commitSha);
    }

    public function rollbackToCommit(string $commit, array &$steps): void
    {
        $info = $this->getRepositoryInfo();
        if (! $info) {
            throw new RuntimeException('У .git/config не знайдено посилання на origin GitHub.');
        }

        $steps[] = $this->step('Завантаження zip-архіву коміту', function () use ($info, $commit) {
            $archiveResponse = $this->request(
                'GET',
                "/repos/{$info['owner']}/{$info['repo']}/zipball/" . rawurlencode($commit),
                accept: 'application/zip'
            );

            $tempZip = $this->createTempPath('zip');
            File::put($tempZip, $archiveResponse->body());

            return $tempZip;
        });

        $zipPath = Arr::last($steps)['output'];
        $extractDirectory = $this->extractArchive($zipPath);
        $this->synchroniseWorkingTree($extractDirectory, $steps);
        File::delete($zipPath);
        File::deleteDirectory(dirname($extractDirectory));

        $currentBranch = $this->getCurrentBranch();
        if ($currentBranch) {
            $this->updateHeadReference($currentBranch, $commit);
        }
    }

    public function pushCurrent(string $targetBranch, array &$steps): void
    {
        $info = $this->getRepositoryInfo();
        if (! $info) {
            throw new RuntimeException('У .git/config не знайдено посилання на origin GitHub.');
        }

        if (! $this->token) {
            throw new RuntimeException('Для пушу потрібен токен GitHub (змінна середовища GITHUB_TOKEN).');
        }

        $branchInfo = $this->request("GET", "/repos/{$info['owner']}/{$info['repo']}/branches/{$targetBranch}");
        $baseCommit = data_get($branchInfo, 'commit.sha');
        $baseTree = data_get($branchInfo, 'commit.commit.tree.sha');

        if (! $baseCommit || ! $baseTree) {
            throw new RuntimeException('Не вдалося отримати базовий коміт або дерево віддаленої гілки.');
        }

        $files = $this->collectRepositoryFiles();
        if (count($files) > 1000) {
            throw new RuntimeException('Надто багато файлів для пушу через GitHub API (обмеження 1000 елементів за один раз).');
        }

        $treeEntries = [];
        foreach ($files as $relative => $fullPath) {
            $blob = $this->createBlob($fullPath);
            $treeEntries[] = [
                'path' => $relative,
                'mode' => $this->determineMode($fullPath),
                'type' => 'blob',
                'sha' => $blob,
            ];
        }

        $steps[] = [
            'command' => 'Створення дерева файлів у GitHub',
            'successful' => true,
            'output' => 'Передано ' . count($treeEntries) . ' файлів.',
        ];

        $treeResponse = $this->request('POST', "/repos/{$info['owner']}/{$info['repo']}/git/trees", [
            'base_tree' => $baseTree,
            'tree' => $treeEntries,
        ]);
        $treeSha = $treeResponse['sha'] ?? null;
        if (! $treeSha) {
            throw new RuntimeException('GitHub не повернув SHA створеного дерева.');
        }

        $commitMessage = 'Push from deployment panel (' . now()->toDateTimeString() . ')';
        $commitResponse = $this->request('POST', "/repos/{$info['owner']}/{$info['repo']}/git/commits", [
            'message' => $commitMessage,
            'tree' => $treeSha,
            'parents' => [$baseCommit],
        ]);
        $commitSha = $commitResponse['sha'] ?? null;
        if (! $commitSha) {
            throw new RuntimeException('GitHub не повернув SHA створеного коміту.');
        }

        $this->request('PATCH', "/repos/{$info['owner']}/{$info['repo']}/git/refs/heads/{$targetBranch}", [
            'sha' => $commitSha,
            'force' => true,
        ]);

        $steps[] = [
            'command' => 'Оновлення віддаленої гілки',
            'successful' => true,
            'output' => "Оновлено origin/{$targetBranch} до {$commitSha}.",
        ];
    }

    public function createBackupBranch(string $branchName, string $commitSha, array &$steps): void
    {
        $info = $this->getRepositoryInfo();
        if (! $info) {
            throw new RuntimeException('У .git/config не знайдено посилання на origin GitHub.');
        }

        if (! $this->token) {
            throw new RuntimeException('Для роботи з гілками GitHub потрібен токен (GITHUB_TOKEN).');
        }

        $this->request('POST', "/repos/{$info['owner']}/{$info['repo']}/git/refs", [
            'ref' => 'refs/heads/' . $branchName,
            'sha' => $commitSha,
        ]);

        $steps[] = [
            'command' => 'Створення гілки у GitHub',
            'successful' => true,
            'output' => "Гілку {$branchName} вказано на {$commitSha}.",
        ];
    }

    public function pushBackupBranch(string $branchName, string $commitSha, array &$steps): void
    {
        $info = $this->getRepositoryInfo();
        if (! $info) {
            throw new RuntimeException('У .git/config не знайдено посилання на origin GitHub.');
        }

        if (! $this->token) {
            throw new RuntimeException('Для пушу потрібен токен GitHub (GITHUB_TOKEN).');
        }

        $this->request('PATCH', "/repos/{$info['owner']}/{$info['repo']}/git/refs/heads/{$branchName}", [
            'sha' => $commitSha,
            'force' => true,
        ]);

        $steps[] = [
            'command' => 'Оновлення резервної гілки',
            'successful' => true,
            'output' => "Віддалена гілка {$branchName} оновлена до {$commitSha}.",
        ];
    }

    private function request(string $method, string $url, array $payload = [], ?string $accept = null)
    {
        $client = Http::withHeaders([
            'User-Agent' => 'engapp-deployment-panel',
            'Accept' => $accept ?: 'application/vnd.github+json',
        ]);

        if ($this->token) {
            $client = $client->withToken($this->token);
        }

        $response = $client->{strtolower($method)}('https://api.github.com' . $url, $payload);

        if ($response->failed()) {
            throw new RuntimeException('Помилка GitHub API: ' . $response->body());
        }

        if ($accept === 'application/zip') {
            return $response;
        }

        return $response->json();
    }

    private function createTempPath(string $extension): string
    {
        $path = storage_path('app/' . self::TEMP_PREFIX . Str::random(16) . '.' . $extension);
        File::ensureDirectoryExists(dirname($path));

        return $path;
    }

    private function extractArchive(string $zipPath): string
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new RuntimeException('Не вдалося відкрити zip-архів від GitHub.');
        }

        $extractTo = storage_path('app/' . self::TEMP_PREFIX . Str::random(16));
        File::ensureDirectoryExists($extractTo);

        if (! $zip->extractTo($extractTo)) {
            throw new RuntimeException('Не вдалося розпакувати zip-архів.');
        }
        $zip->close();

        $directories = collect(File::directories($extractTo));
        $root = $directories->first();
        if (! $root) {
            throw new RuntimeException('Структура архіву неочікувана: відсутня коренева директорія.');
        }

        return $root;
    }

    private function synchroniseWorkingTree(string $sourceDir, array &$steps): void
    {
        $base = $this->basePath;

        $steps[] = $this->step('Очищення робочої директорії', function () use ($base) {
            $this->cleanDirectory($base);
            return 'Робочу директорію очищено.';
        });

        $steps[] = $this->step('Копіювання файлів із архіву', function () use ($sourceDir, $base) {
            $this->copyDirectory($sourceDir, $base);
            return 'Новий код розгорнуто.';
        });
    }

    private function cleanDirectory(string $directory): void
    {
        $preserve = [
            '.git',
            'storage',
            'vendor',
            'node_modules',
        ];

        foreach (File::directories($directory) as $dir) {
            $name = basename($dir);
            if (in_array($name, $preserve, true)) {
                continue;
            }

            File::deleteDirectory($dir);
        }

        foreach (File::files($directory) as $file) {
            $name = basename($file);
            if ($name === '.env') {
                continue;
            }

            File::delete($file);
        }
    }

    private function copyDirectory(string $from, string $to): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            $relative = Str::after($item->getPathname(), $from . DIRECTORY_SEPARATOR);
            if ($relative === '' || Str::startsWith($relative, '.git')) {
                continue;
            }

            $target = $to . DIRECTORY_SEPARATOR . $relative;

            if ($item->isDir()) {
                File::ensureDirectoryExists($target);
            } else {
                File::ensureDirectoryExists(dirname($target));
                File::put($target, File::get($item->getPathname()));
                @chmod($target, $item->getPerms());
            }
        }
    }

    private function updateHeadReference(string $branch, string $commitSha): void
    {
        $refPath = $this->basePath . '/.git/refs/heads/' . $branch;
        if (File::exists($refPath)) {
            File::put($refPath, $commitSha . "\n");
        }
    }

    private function collectRepositoryFiles(): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->basePath, \FilesystemIterator::SKIP_DOTS)
        );

        $files = [];
        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            if ($item->isDir()) {
                continue;
            }

            $relative = Str::after($item->getPathname(), $this->basePath . DIRECTORY_SEPARATOR);
            if ($this->shouldSkipPath($relative)) {
                continue;
            }

            $files[$relative] = $item->getPathname();
        }

        ksort($files);

        return $files;
    }

    private function shouldSkipPath(string $relativePath): bool
    {
        $relativePath = str_replace('\\', '/', $relativePath);
        $ignored = [
            '.git/',
            'storage/app/',
            'storage/framework/',
            'storage/logs/',
            'vendor/',
            'node_modules/',
            'public/storage/',
            'bootstrap/cache/',
        ];

        foreach ($ignored as $prefix) {
            if (Str::startsWith($relativePath, $prefix)) {
                $basename = basename($relativePath);
                if (in_array($basename, ['.gitignore', '.gitkeep'], true)) {
                    return false;
                }

                return true;
            }
        }

        return false;
    }

    private function createBlob(string $path): string
    {
        $content = File::get($path);
        $encoding = $this->isBinary($content) ? 'base64' : 'utf-8';

        $payload = [
            'content' => $encoding === 'base64' ? base64_encode($content) : $content,
            'encoding' => $encoding,
        ];

        $info = $this->getRepositoryInfo();
        $response = $this->request('POST', "/repos/{$info['owner']}/{$info['repo']}/git/blobs", $payload);
        $sha = $response['sha'] ?? null;
        if (! $sha) {
            throw new RuntimeException('GitHub не повернув SHA створеного блобу.');
        }

        return $sha;
    }

    private function determineMode(string $path): string
    {
        return is_executable($path) ? '100755' : '100644';
    }

    private function isBinary(string $content): bool
    {
        return strpos($content, "\0") !== false || ! mb_check_encoding($content, 'UTF-8');
    }

    private function step(string $description, callable $callback): array
    {
        try {
            $result = $callback();

            return [
                'command' => $description,
                'successful' => true,
                'output' => is_string($result) ? $result : json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        } catch (\Throwable $e) {
            throw new RuntimeException($description . ': ' . $e->getMessage(), previous: $e);
        }
    }
}
