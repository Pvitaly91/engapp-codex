<?php

namespace App\Modules\GitDeployment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Modules\GitDeployment\Models\BackupBranch;
use App\Modules\GitDeployment\Models\BranchUsageHistory;
use App\Modules\GitDeployment\Services\NativeGitDeploymentService;
use App\Modules\GitDeployment\Http\Concerns\ParsesDeploymentPaths;

class NativeDeploymentController extends BaseController
{
    use ParsesDeploymentPaths;

    private const BACKUP_FILE = 'deployment_backups.json';

    public function __construct(private readonly NativeGitDeploymentService $deployment)
    {
    }

    public function index()
    {
        $backups = array_reverse($this->loadBackups());
        $feedback = session('deployment_native');

        $backupBranches = BackupBranch::query()
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $recentUsage = BranchUsageHistory::getRecentUsage(10);
        $availableFolders = $this->getAvailableFolders();

        return view('git-deployment::deployment.native', [
            'backups' => $backups,
            'feedback' => $feedback,
            'backupBranches' => $backupBranches,
            'currentBranch' => $this->deployment->currentBranch(),
            'currentCommit' => $this->deployment->headCommit(),
            'supportsShell' => $this->supportsShellCommands(),
            'recentUsage' => $recentUsage,
            'availableFolders' => $availableFolders,
        ]);
    }

    public function deploy(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $sanitized = $this->sanitizeBranchName($branch ?? 'main');

        $autoPushBranch = $request->input('auto_push_branch', '');
        $autoPushBranch = $this->sanitizeBranchName($autoPushBranch ?? '');

        try {
            $result = $this->deployment->deploy($sanitized);
            $logs = $result['logs'];
            $message = $result['message'];
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], $sanitized);
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $sanitized,
            'deploy',
            'Оновлення сайту до останнього стану гілки через GitHub API'
        );

        // Auto-push to branch if specified
        if ($autoPushBranch !== '') {
            try {
                $currentCommit = $this->deployment->headCommit();
                
                // Create branch if it doesn't exist
                $createResult = $this->deployment->createBranch($autoPushBranch, 'current');
                $logs = array_merge($logs, $createResult['logs']);
                
                // Push branch to remote
                $pushResult = $this->deployment->pushBranch($autoPushBranch);
                $logs = array_merge($logs, $pushResult['logs']);
                
                $message .= " Поточний стан також запушено на origin/{$autoPushBranch}.";
                
                // Track auto-push usage
                BranchUsageHistory::trackUsage(
                    $autoPushBranch,
                    'auto_push',
                    "Автоматичний пуш після оновлення на віддалену гілку {$autoPushBranch} через GitHub API"
                );

                // Update backup branch record
                BackupBranch::updateOrCreate(
                    ['name' => $autoPushBranch],
                    [
                        'commit_hash' => $currentCommit,
                        'pushed_at' => now(),
                    ]
                );
            } catch (\Throwable $throwable) {
                $message .= " Проте не вдалося запушити на origin/{$autoPushBranch}: " . $throwable->getMessage();
            }
        }

        return $this->redirectWithFeedback('success', $message, $logs, $sanitized);
    }

    public function deployPartial(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $sanitized = $this->sanitizeBranchName($branch ?? 'main');

        $pathsInput = $request->input('paths', '');

        // Парсимо та валідуємо шляхи
        $parsed = $this->parseAndValidatePaths($pathsInput);
        $paths = $parsed['valid'];
        $pathErrors = $parsed['errors'];

        if ($paths === []) {
            $errorMessage = 'Не вказано жодного валідного шляху для часткового деплою.';
            if ($pathErrors !== []) {
                $errorMessage .= ' Помилки: ' . implode('; ', $pathErrors);
            }
            return $this->redirectWithFeedback('error', $errorMessage, [], $sanitized);
        }

        try {
            $result = $this->deployment->deployPartial($sanitized, $paths);
            $logs = $result['logs'];
            $message = $result['message'];
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], $sanitized);
        }

        // Track branch usage
        $pathsList = implode(', ', $paths);
        BranchUsageHistory::trackUsage(
            $sanitized,
            'partial_deploy',
            "Частковий деплой через GitHub API. Шляхи: {$pathsList}"
        );

        if ($pathErrors !== []) {
            $message .= ' Попередження: ' . implode('; ', $pathErrors);
        }

        return $this->redirectWithFeedback('success', $message, $logs, $sanitized);
    }

    public function pushCurrent(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $sanitized = $this->sanitizeBranchName($branch ?? 'main');

        try {
            $result = $this->deployment->push($sanitized);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], $sanitized);
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $sanitized,
            'push',
            'Пуш поточного стану на віддалену гілку через GitHub API'
        );

        return $this->redirectWithFeedback('success', $result['message'], $result['logs'], $sanitized);
    }

    public function rollback(Request $request): RedirectResponse
    {
        $commit = $request->input('commit');

        try {
            $result = $this->deployment->rollback($commit);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], null);
        }

        return $this->redirectWithFeedback('success', $result['message'], $result['logs'], null);
    }

    public function createBackupBranch(Request $request): RedirectResponse
    {
        $branch = $request->input('branch_name', '');
        $commit = $request->input('commit', 'current');

        $sanitizedBranch = $this->sanitizeBranchName($branch ?? '');
        $resolvedCommit = $commit === 'current' ? ($this->deployment->headCommit() ?? '') : $commit;

        try {
            $result = $this->deployment->createBranch($sanitizedBranch, $commit);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], null);
        }

        BackupBranch::updateOrCreate(
            ['name' => $sanitizedBranch],
            [
                'commit_hash' => $resolvedCommit,
                'pushed_at' => null,
            ]
        );

        return $this->redirectWithFeedback('success', $result['message'], $result['logs'], null);
    }

    public function pushBackupBranch(BackupBranch $backupBranch): RedirectResponse
    {
        try {
            $result = $this->deployment->pushBranch($backupBranch->name);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], null);
        }

        $backupBranch->forceFill(['pushed_at' => now()])->save();

        return $this->redirectWithFeedback('success', $result['message'], $result['logs'], null);
    }

    public function createAndPushBranch(Request $request): RedirectResponse
    {
        $branchName = $request->input('quick_branch_name', '');
        $sanitized = $this->sanitizeBranchName($branchName ?? '');

        if ($sanitized === '') {
            return $this->redirectWithFeedback('error', 'Вкажіть коректну назву гілки.', [], null);
        }

        $currentCommit = $this->deployment->headCommit();
        if (! $currentCommit) {
            return $this->redirectWithFeedback('error', 'Не вдалося визначити поточний коміт.', [], null);
        }

        try {
            // Create branch if it doesn't exist
            $createResult = $this->deployment->createBranch($sanitized, 'current');
            $logs = $createResult['logs'];

            // Push branch to remote
            $pushResult = $this->deployment->pushBranch($sanitized);
            $logs = array_merge($logs, $pushResult['logs']);
        } catch (\Throwable $throwable) {
            return $this->redirectWithFeedback('error', $throwable->getMessage(), [], null);
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $sanitized,
            'create_and_push',
            'Швидке створення та пуш гілки з поточним станом сайту через GitHub API'
        );

        // Update or create backup branch record
        BackupBranch::updateOrCreate(
            ['name' => $sanitized],
            [
                'commit_hash' => $currentCommit,
                'pushed_at' => now(),
            ]
        );

        return $this->redirectWithFeedback(
            'success',
            "Гілку {$sanitized} успішно створено та запушено на віддалений репозиторій через GitHub API.",
            $logs,
            null
        );
    }

    private function redirectWithFeedback(string $status, string $message, array $logs, ?string $branch): RedirectResponse
    {
        return redirect()
            ->route('deployment.native.index')
            ->with('deployment_native', [
                'status' => $status,
                'message' => $message,
                'logs' => $logs,
                'branch' => $branch,
            ]);
    }

    private function loadBackups(): array
    {
        $path = $this->ensureBackupFile();

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function ensureBackupFile(): string
    {
        $path = storage_path('app/' . self::BACKUP_FILE);
        $directory = dirname($path);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        if (! File::exists($path)) {
            File::put($path, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $path;
    }

    private function sanitizeBranchName(string $branch): string
    {
        $normalized = Str::of($branch)->trim()->value();
        $sanitized = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $normalized);

        return $sanitized !== null && $sanitized !== '' ? $sanitized : $normalized;
    }

    private function supportsShellCommands(): bool
    {
        return function_exists('proc_open');
    }

    /**
     * Отримує дерево папок для часткового деплою (до 5 рівнів глибини).
     *
     * @return array
     */
    private function getAvailableFolders(): array
    {
        $preservePaths = config('git-deployment.preserve_paths', []);
        $basePath = base_path();

        return $this->buildFolderTree($basePath, $preservePaths, 5);
    }

    /**
     * Рекурсивно будує дерево папок.
     *
     * @param string $path Шлях до директорії
     * @param array $preservePaths Захищені шляхи
     * @param int $maxDepth Максимальна глибина
     * @param int $currentDepth Поточна глибина
     * @return array
     */
    private function buildFolderTree(string $path, array $preservePaths, int $maxDepth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $maxDepth) {
            return [];
        }

        $tree = [];
        $dirs = File::directories($path);

        foreach ($dirs as $dir) {
            $name = basename($dir);
            
            // Пропускаємо захищені та приховані директорії на першому рівні
            if ($currentDepth === 0 && (in_array($name, $preservePaths, true) || str_starts_with($name, '.'))) {
                continue;
            }
            
            // Пропускаємо приховані директорії на всіх рівнях
            if (str_starts_with($name, '.')) {
                continue;
            }

            $children = $this->buildFolderTree($dir, $preservePaths, $maxDepth, $currentDepth + 1);
            
            $tree[$name] = $children;
        }

        ksort($tree);

        return $tree;
    }
}
