<?php

namespace App\Modules\GitDeployment\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class NativeGitDeploymentService
{
    private NativeGitFilesystem $filesystem;
    private ?GitHubApiClient $github = null;

    public function __construct()
    {
        $this->filesystem = new NativeGitFilesystem(base_path());
    }

    public function currentBranch(): ?string
    {
        return $this->filesystem->getCurrentBranch();
    }

    public function headCommit(): ?string
    {
        return $this->filesystem->getHeadCommit();
    }

    /**
     * @return array{logs: array<int, string>, message: string}
     */
    public function deploy(string $branch): array
    {
        $logs = [];
        $branch = $this->sanitizeBranch($branch);

        $currentCommit = $this->headCommit();
        if ($currentCommit) {
            $this->storeBackup([
                'timestamp' => now()->toIso8601String(),
                'commit' => $currentCommit,
                'branch' => $branch,
            ]);
            $logs[] = "Збережено резервну копію коміту {$currentCommit}.";
        } else {
            $logs[] = 'Не вдалося визначити поточний коміт для резервного копіювання.';
        }

        $logs[] = "Отримуємо останній стан гілки {$branch} через GitHub API.";
        $branchInfo = $this->github()->getBranch($branch);
        $remoteCommit = Arr::get($branchInfo, 'object.sha');

        if (! $remoteCommit) {
            throw new RuntimeException('GitHub не повернув останній коміт гілки.');
        }

        $extracted = $this->fetchAndExtract($branch, $logs, "гілки {$branch}");
        $logs[] = 'Очищуємо локальне дерево від файлів, яких немає в архіві.';
        $this->filesystem->replaceWorkingTree($extracted);
        File::deleteDirectory(dirname($extracted));

        $currentBranch = $this->filesystem->getCurrentBranch();
        if ($currentBranch) {
            $this->filesystem->writeRef('refs/heads/' . $currentBranch, $remoteCommit);
        }

        $logs[] = "Робоче дерево оновлено до коміту {$remoteCommit}.";

        return [
            'logs' => $logs,
            'message' => 'Сайт успішно оновлено до останнього стану гілки через GitHub API.',
        ];
    }

    /**
     * @return array{logs: array<int, string>, message: string}
     */
    public function deployPartial(string $branch, array $paths): array
    {
        $logs = [];
        $branch = $this->sanitizeBranch($branch);
        $paths = $this->sanitizePaths($paths);

        if ($paths === []) {
            throw new RuntimeException('Вкажіть хоча б один коректний шлях для часткового деплою.');
        }

        $currentCommit = $this->headCommit();
        if ($currentCommit) {
            $this->storeBackup([
                'timestamp' => now()->toIso8601String(),
                'commit' => $currentCommit,
                'branch' => $branch,
            ]);
            $logs[] = "Збережено резервну копію коміту {$currentCommit}.";
        } else {
            $logs[] = 'Не вдалося визначити поточний коміт для резервного копіювання.';
        }

        $logs[] = "Отримуємо вибрані шляхи з гілки {$branch} через GitHub API.";
        $branchInfo = $this->github()->getBranch($branch);
        $remoteCommit = Arr::get($branchInfo, 'object.sha');

        if (! $remoteCommit) {
            throw new RuntimeException('GitHub не повернув останній коміт гілки.');
        }

        $extracted = $this->fetchAndExtract($branch, $logs, "гілки {$branch}");
        $logs[] = 'Застосовуємо вибрані шляхи: ' . implode(', ', $paths);
        $stats = $this->filesystem->replacePaths($extracted, $paths);
        File::deleteDirectory(dirname($extracted));

        if ($stats) {
            $logs[] = 'Скопійовано: ' . ($stats['copied'] ?? 0) . '; видалено: ' . ($stats['deleted'] ?? 0) . '.';
        }

        $logs[] = "Частковий деплой завершено. Віддалений коміт: {$remoteCommit}.";

        return [
            'logs' => $logs,
            'message' => 'Частковий деплой виконано через GitHub API.',
        ];
    }

    /**
     * @return array{logs: array<int, string>, message: string}
     */
    public function push(string $targetBranch): array
    {
        $targetBranch = $this->sanitizeBranch($targetBranch);
        $logs = [];

        $currentBranch = $this->filesystem->getCurrentBranch();
        $logs[] = 'Підготовка локального дерева файлів.';
        $localFiles = $this->filesystem->scanWorkingTree();

        $logs[] = 'Отримуємо інформацію про віддалену гілку.';
        $remoteBranch = null;
        try {
            $remoteBranch = $this->github()->getBranch($targetBranch);
        } catch (RuntimeException $exception) {
            $logs[] = 'Віддалену гілку не знайдено. Буде створено нову.';
        }

        $parentCommit = $remoteBranch ? Arr::get($remoteBranch, 'object.sha') : null;

        if (! $parentCommit) {
            $repo = $this->github()->getRepository();
            $defaultBranch = Arr::get($repo, 'default_branch', 'main');
            $default = $this->github()->getBranch($defaultBranch);
            $parentCommit = Arr::get($default, 'object.sha');
        }

        if (! $parentCommit) {
            throw new RuntimeException('Не вдалося визначити батьківський коміт для створення нового коміту.');
        }

        $parentCommitData = $this->github()->getCommit($parentCommit);
        $baseTreeSha = Arr::get($parentCommitData, 'tree.sha', '');
        $remoteTree = $baseTreeSha !== '' ? $this->github()->getTree($baseTreeSha) : ['tree' => []];
        $remoteMap = collect($remoteTree['tree'] ?? [])
            ->where('type', 'blob')
            ->mapWithKeys(fn ($item) => [$item['path'] => $item['sha']])
            ->all();

        $logs[] = 'Визначаємо список файлів для оновлення на GitHub.';
        $updates = [];
        $deletions = [];

        foreach ($localFiles as $path => $file) {
            if (! isset($remoteMap[$path]) || $remoteMap[$path] !== $file['sha']) {
                $updates[$path] = $file;
            }
        }

        foreach ($remoteMap as $path => $sha) {
            if (! isset($localFiles[$path])) {
                $deletions[] = $path;
            }
        }

        if ($updates === [] && $deletions === []) {
            return [
                'logs' => array_merge($logs, ['Зміни не виявлені. Коміт не створено.']),
                'message' => 'Локальні та віддалені файли вже синхронізовані.',
            ];
        }

        $treeEntries = [];

        foreach ($updates as $path => $file) {
            $blobSha = $this->github()->createBlob($file['content'], $file['binary']);
            $treeEntries[] = [
                'path' => $path,
                'mode' => $file['mode'],
                'type' => 'blob',
                'sha' => $blobSha,
            ];
        }

        foreach ($deletions as $path) {
            $treeEntries[] = [
                'path' => $path,
                'mode' => '100644',
                'type' => 'blob',
                'sha' => null,
            ];
        }

        $logs[] = 'Створюємо нове дерево для коміту.';
        $newTreeSha = $this->github()->createTree($baseTreeSha, $treeEntries);

        $message = 'Deploy from admin panel (' . Carbon::now()->toIso8601String() . ')';
        $logs[] = 'Формуємо коміт з новим деревом.';
        $commitSha = $this->github()->createCommit($message, $newTreeSha, [$parentCommit]);

        $ref = 'heads/' . $targetBranch;

        if ($remoteBranch) {
            $logs[] = 'Оновлюємо існуючу гілку на GitHub.';
            $this->github()->updateRef($ref, $commitSha, true);
        } else {
            $logs[] = 'Створюємо нову гілку на GitHub.';
            $this->github()->createRef($ref, $commitSha);
        }

        if ($currentBranch) {
            $this->filesystem->writeRef('refs/heads/' . $currentBranch, $commitSha);
        }

        $logs[] = "Віддалена гілка {$targetBranch} оновлена комітом {$commitSha}.";

        return [
            'logs' => $logs,
            'message' => "Поточний стан успішно відправлено на GitHub гілку {$targetBranch} через GitHub API.",
        ];
    }

    /**
     * @return array{logs: array<int, string>, message: string}
     */
    public function rollback(string $commit): array
    {
        $commit = trim($commit);
        $logs = [];

        if ($commit === '') {
            throw new RuntimeException('Вкажіть коміт для відкату.');
        }

        $extracted = $this->fetchAndExtract($commit, $logs, "коміту {$commit}");
        $this->filesystem->replaceWorkingTree($extracted);
        File::deleteDirectory(dirname($extracted));

        $currentBranch = $this->filesystem->getCurrentBranch();
        if ($currentBranch) {
            $this->filesystem->writeRef('refs/heads/' . $currentBranch, $commit);
        }

        $logs[] = "Відкат виконано до коміту {$commit}.";

        return [
            'logs' => $logs,
            'message' => 'Робочий стан успішно відновлено з резервного коміту через GitHub API без shell-команд.',
        ];
    }

    /**
     * @return array{logs: array<int, string>, message: string}
     */
    public function createBranch(string $branch, string $commit): array
    {
        $branch = $this->sanitizeBranch($branch);
        $logs = [];

        if ($branch === '') {
            throw new RuntimeException('Назва гілки не може бути порожньою.');
        }

        if ($this->filesystem->branchExists($branch)) {
            throw new RuntimeException('Гілка з такою назвою вже існує.');
        }

        if ($commit === '' || $commit === 'current') {
            $commit = $this->filesystem->getHeadCommit() ?? throw new RuntimeException('Не вдалося визначити поточний коміт.');
        }

        if (! preg_match('/^[0-9a-f]{7,40}$/i', $commit)) {
            throw new RuntimeException('Некоректний формат коміту.');
        }

        $this->filesystem->writeRef('refs/heads/' . $branch, $commit);
        $logs[] = "Гілку {$branch} створено на коміті {$commit}.";

        return [
            'logs' => $logs,
            'message' => "Гілку {$branch} успішно створено через GitHub API.",
        ];
    }

    /**
     * @return array{logs: array<int, string>, message: string}
     */
    public function pushBranch(string $branch): array
    {
        $branch = $this->sanitizeBranch($branch);
        $logs = [];

        if (! $this->filesystem->branchExists($branch)) {
            throw new RuntimeException('Вказаної гілки не існує локально.');
        }

        $commit = $this->filesystem->readRef('refs/heads/' . $branch);

        if (! $commit) {
            throw new RuntimeException('Не вдалося визначити коміт гілки.');
        }

        $logs[] = "Оновлюємо віддалений ref гілки {$branch}.";

        try {
            $this->github()->updateRef('heads/' . $branch, $commit, true);
        } catch (RuntimeException $exception) {
            $this->github()->createRef('heads/' . $branch, $commit);
        }

        return [
            'logs' => $logs,
            'message' => "Гілку {$branch} успішно опубліковано на GitHub.",
        ];
    }

    private function sanitizeBranch(string $branch): string
    {
        $branch = Str::of($branch)->trim()->value();
        $branch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branch ?? '') ?? '';

        return $branch;
    }

    /**
     * @param array<int, string> $paths
     * @return array<int, string>
     */
    private function sanitizePaths(array $paths): array
    {
        $preserve = collect(config('git-deployment.preserve_paths'));
        $sanitized = [];

        foreach ($paths as $path) {
            $raw = trim($path);
            $isAbsolute = str_starts_with($raw, '/');
            $normalized = str_replace('\\', '/', $raw);
            $normalized = ltrim($normalized, '/');
            $normalized = preg_replace('#/+#', '/', $normalized ?? '') ?? '';

            if ($normalized === '' || $isAbsolute) {
                continue;
            }

            if (str_contains($normalized, '..') || str_starts_with($normalized, './') || preg_match('#^[A-Za-z]:#', $normalized)) {
                continue;
            }

            $topLevel = Str::before($normalized, '/') ?: $normalized;

            if ($preserve->contains($topLevel)) {
                continue;
            }

            $sanitized[] = $normalized;
        }

        return array_values(array_unique($sanitized));
    }

    private function storeBackup(array $backup): void
    {
        $path = $this->ensureBackupFile();
        $items = [];

        if (File::exists($path)) {
            $decoded = json_decode(File::get($path), true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        $items[] = $backup;
        $items = array_slice($items, -10);
        File::put($path, json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function ensureBackupFile(): string
    {
        $path = storage_path('app/deployment_backups.json');
        $directory = dirname($path);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (! File::exists($path)) {
            File::put($path, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $path;
    }

    private function fetchAndExtract(string $ref, array &$logs, string $context): string
    {
        $formats = $this->archiveFormatCandidates();
        $lastException = null;

        foreach ($formats as $index => $format) {
            $label = strtoupper($format);
            $logs[] = "Завантажуємо архів {$context} у форматі {$label}.";

            try {
                $archivePath = $this->github()->downloadArchive($ref, $format);
                $logs[] = "Розпаковуємо архів (формат {$label}) без використання shell.";

                return $this->extractArchive($archivePath, $format);
            } catch (RuntimeException $exception) {
                $lastException = $exception;
                $logs[] = 'Помилка: ' . $exception->getMessage();

                if ($index !== array_key_last($formats)) {
                    $logs[] = 'Пробуємо альтернативний формат архіву.';
                }
            }
        }

        throw $lastException ?? new RuntimeException('Не вдалося опрацювати архів GitHub.');
    }

    private function extractArchive(string $archivePath, string $format): string
    {
        $tmpDir = storage_path('app/native_deploy_' . Str::random(8));
        File::ensureDirectoryExists($tmpDir);

        if ($format === 'zip') {
            $this->extractZipArchive($archivePath, $tmpDir);
        } elseif ($format === 'tar') {
            $this->extractTarArchive($archivePath, $tmpDir);
        } else {
            @unlink($archivePath);
            throw new RuntimeException('Непідтримуваний формат архіву GitHub.');
        }

        $directories = File::directories($tmpDir);
        if ($directories === []) {
            throw new RuntimeException('Не вдалося розпакувати архів GitHub.');
        }

        return $directories[0];
    }

    /**
     * @return array<int, string>
     */
    private function archiveFormatCandidates(): array
    {
        $formats = [];

        if (class_exists(ZipArchive::class)) {
            $formats[] = 'zip';
        }

        if (class_exists(\PharData::class) && function_exists('gzopen')) {
            $formats[] = 'tar';
        }

        if ($formats === []) {
            throw new RuntimeException('Сервер не підтримує розпакування ZIP або TAR архівів без shell.');
        }

        if (PHP_OS_FAMILY === 'Windows' && in_array('zip', $formats, true)) {
            $formats = array_values(array_unique(array_merge(['zip'], $formats)));
        }

        return array_values(array_unique($formats));
    }

    private function extractZipArchive(string $archivePath, string $destination): void
    {
        if (! class_exists(ZipArchive::class)) {
            @unlink($archivePath);
            throw new RuntimeException('Розширення ZipArchive недоступне на сервері.');
        }

        $zip = new ZipArchive();
        $opened = false;

        try {
            $openResult = $zip->open($archivePath);

            if ($openResult !== true) {
                throw new RuntimeException('Не вдалося відкрити архів GitHub (код ' . $openResult . ').');
            }

            $opened = true;

            if (! $zip->extractTo($destination)) {
                throw new RuntimeException('Не вдалося розпакувати архів GitHub.');
            }
        } finally {
            if ($opened) {
                $zip->close();
            }

            @unlink($archivePath);
        }
    }

    private function extractTarArchive(string $archivePath, string $destination): void
    {
        if (! class_exists(\PharData::class)) {
            @unlink($archivePath);
            throw new RuntimeException('Розширення Phar недоступне на сервері.');
        }

        if (! function_exists('gzopen')) {
            @unlink($archivePath);
            throw new RuntimeException('Розпакування TAR-архіву потребує розширення zlib.');
        }

        $tarPath = $archivePath . '.tar';
        $gzip = @gzopen($archivePath, 'rb');

        if ($gzip === false) {
            @unlink($archivePath);
            throw new RuntimeException('Не вдалося відкрити gzip-архів GitHub.');
        }

        $tarHandle = @fopen($tarPath, 'wb');

        if ($tarHandle === false) {
            gzclose($gzip);
            @unlink($archivePath);
            throw new RuntimeException('Не вдалося підготувати тимчасовий TAR-файл.');
        }

        try {
            while (! gzeof($gzip)) {
                $chunk = gzread($gzip, 8192);

                if ($chunk === false) {
                    throw new RuntimeException('Помилка читання gzip-архіву GitHub.');
                }

                if (fwrite($tarHandle, $chunk) === false) {
                    throw new RuntimeException('Не вдалося записати тимчасовий TAR-файл.');
                }
            }
        } finally {
            gzclose($gzip);
            fclose($tarHandle);
            @unlink($archivePath);
        }

        try {
            $phar = new \PharData($tarPath);
            $phar->extractTo($destination, null, true);
        } catch (\Exception $exception) {
            @unlink($tarPath);
            throw new RuntimeException('Не вдалося розпакувати архів GitHub: ' . $exception->getMessage(), 0, $exception);
        }

        @unlink($tarPath);
    }

    private function github(): GitHubApiClient
    {
        if ($this->github === null) {
            $this->github = new GitHubApiClient();
        }

        return $this->github;
    }
}
