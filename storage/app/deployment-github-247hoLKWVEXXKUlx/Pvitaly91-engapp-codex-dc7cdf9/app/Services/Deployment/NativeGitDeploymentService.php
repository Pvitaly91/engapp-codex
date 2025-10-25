<?php

namespace App\Services\Deployment;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PharData;
use RuntimeException;

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

        $logs[] = 'Завантажуємо архів віддаленої гілки.';
        $archive = $this->github()->downloadTarball($branch);

        $logs[] = 'Розпаковуємо архів і оновлюємо робоче дерево без використання shell.';
        $extracted = $this->extractTarball($archive);
        $this->filesystem->replaceWorkingTree($extracted);
        File::deleteDirectory(dirname($extracted));

        $currentBranch = $this->filesystem->getCurrentBranch();
        if ($currentBranch) {
            $this->filesystem->writeRef('refs/heads/' . $currentBranch, $remoteCommit);
        }

        $logs[] = "Робоче дерево оновлено до коміту {$remoteCommit}.";

        return [
            'logs' => $logs,
            'message' => 'Сайт успішно оновлено до останнього стану гілки без використання shell.',
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
            'message' => "Поточний стан успішно відправлено на GitHub гілку {$targetBranch} без використання shell.",
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

        $logs[] = "Завантажуємо архів коміту {$commit} з GitHub.";
        $archive = $this->github()->downloadTarball($commit);

        $logs[] = 'Розпаковуємо архів і відновлюємо файли.';
        $extracted = $this->extractTarball($archive);
        $this->filesystem->replaceWorkingTree($extracted);
        File::deleteDirectory(dirname($extracted));

        $currentBranch = $this->filesystem->getCurrentBranch();
        if ($currentBranch) {
            $this->filesystem->writeRef('refs/heads/' . $currentBranch, $commit);
        }

        $logs[] = "Відкат виконано до коміту {$commit}.";

        return [
            'logs' => $logs,
            'message' => 'Робочий стан успішно відновлено з резервного коміту без shell-команд.',
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
            'message' => "Гілку {$branch} успішно створено без використання shell.",
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

    private function storeBackup(array $backup): void
    {
        $path = storage_path('app/deployment_backups.json');
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

    private function extractTarball(string $tarGzPath): string
    {
        $tmpDir = storage_path('app/native_deploy_' . Str::random(8));
        File::ensureDirectoryExists($tmpDir);

        $tarPath = $tarGzPath . '.tar';

        try {
            $phar = new PharData($tarGzPath);
            $phar->decompress();
            $tar = new PharData($tarPath);
            $tar->extractTo($tmpDir, null, true);
        } finally {
            @unlink($tarGzPath);
            if (File::exists($tarPath)) {
                @unlink($tarPath);
            }
        }

        $directories = File::directories($tmpDir);
        if ($directories === []) {
            throw new RuntimeException('Не вдалося розпакувати архів GitHub.');
        }

        return $directories[0];
    }

    private function github(): GitHubApiClient
    {
        if ($this->github === null) {
            $this->github = new GitHubApiClient();
        }

        return $this->github;
    }
}
