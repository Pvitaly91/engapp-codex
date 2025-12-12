<?php

namespace App\Modules\GitDeployment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Modules\GitDeployment\Models\BackupBranch;
use App\Modules\GitDeployment\Models\BranchUsageHistory;
use App\Modules\GitDeployment\Http\Concerns\ParsesDeploymentPaths;
use Symfony\Component\Process\Process;
use ZipArchive;

class DeploymentController extends BaseController
{
    use ParsesDeploymentPaths;

    private const BACKUP_FILE = 'deployment_backups.json';

    public function index()
    {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        $backups = array_reverse($this->loadBackups());
        $feedback = session('deployment');

        $backupBranches = BackupBranch::query()
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        $branchProcess = $this->runCommand(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], base_path());
        $currentBranch = $branchProcess->isSuccessful()
            ? trim($branchProcess->getOutput())
            : null;

        $recentUsage = BranchUsageHistory::getRecentUsage(10);

        return view('git-deployment::deployment.index', [
            'backups' => $backups,
            'feedback' => $feedback,
            'backupBranches' => $backupBranches,
            'currentBranch' => $currentBranch,
            'supportsShell' => $this->supportsShellCommands(),
            'recentUsage' => $recentUsage,
        ]);
    }

    public function deploy(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $branch = Str::of($branch)->trim()->value() ?: 'main';
        $branch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branch) ?: 'main';

        $autoPushBranch = $request->input('auto_push_branch', '');
        $autoPushBranch = Str::of($autoPushBranch)->trim()->value();
        $autoPushBranch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $autoPushBranch);

        if ($redirect = $this->redirectIfShellUnavailable($branch)) {
            return $redirect;
        }

        $repoPath = base_path();
        $commandsOutput = [];

        $currentCommitProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
        $currentCommit = trim($currentCommitProcess->getOutput());
        $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $currentCommitProcess);

        if (! $currentCommitProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося зчитати поточний коміт.', $commandsOutput);
        }

        $backupStored = false;
        if ($currentCommit !== '') {
            $this->storeBackup([
                'timestamp' => now()->toIso8601String(),
                'commit' => $currentCommit,
                'branch' => $branch,
            ]);
            $backupStored = true;
        }

        $fetchProcess = $this->runCommand(['git', 'fetch', 'origin'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git fetch origin', $fetchProcess);

        if (! $fetchProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Команда "git fetch" завершилась з помилкою.', $commandsOutput);
        }

        $resetProcess = $this->runCommand(['git', 'reset', '--hard', "origin/{$branch}"], $repoPath);
        $commandsOutput[] = $this->formatProcess("git reset --hard origin/{$branch}", $resetProcess);

        if (! $resetProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося оновити код до останнього коміту.', $commandsOutput);
        }

        $cleanProcess = $this->runCommand(['git', 'clean', '-fd'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git clean -fd', $cleanProcess);

        if (! $cleanProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося видалити локальні файли, яких немає в репозиторії.', $commandsOutput);
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $branch,
            'deploy',
            'Оновлення сайту до останнього стану гілки'
        );

        $message = 'Сайт успішно оновлено до останнього стану гілки.';
        if (! $backupStored) {
            $message .= ' Увага: резервну копію не збережено.';
        }

        // Auto-push to branch if specified
        if ($autoPushBranch !== '') {
            // Get current commit
            $currentCommitProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
            $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $currentCommitProcess);

            if ($currentCommitProcess->isSuccessful()) {
                $currentCommit = trim($currentCommitProcess->getOutput());

                // Check if branch exists locally
                $branchListProcess = $this->runCommand(['git', 'branch', '--list', $autoPushBranch], $repoPath);
                $commandsOutput[] = $this->formatProcess("git branch --list {$autoPushBranch}", $branchListProcess);

                $branchExists = trim($branchListProcess->getOutput()) !== '';

                if (! $branchExists) {
                    // Create branch locally if it doesn't exist
                    $createProcess = $this->runCommand(['git', 'branch', $autoPushBranch, $currentCommit], $repoPath);
                    $commandsOutput[] = $this->formatProcess("git branch {$autoPushBranch} {$currentCommit}", $createProcess);

                    if (! $createProcess->isSuccessful()) {
                        $message .= " Проте не вдалося створити гілку {$autoPushBranch} локально.";
                        return $this->redirectWithFeedback('success', $message, $commandsOutput);
                    }
                }

                // Push branch to remote
                $pushProcess = $this->runCommand(['git', 'push', '--force', 'origin', $autoPushBranch], $repoPath);
                $commandsOutput[] = $this->formatProcess("git push --force origin {$autoPushBranch}", $pushProcess);

                if ($pushProcess->isSuccessful()) {
                    $message .= " Поточний стан також запушено на origin/{$autoPushBranch}.";
                    
                    // Track auto-push usage
                    BranchUsageHistory::trackUsage(
                        $autoPushBranch,
                        'auto_push',
                        "Автоматичний пуш після оновлення на віддалену гілку {$autoPushBranch}"
                    );

                    // Update backup branch record
                    BackupBranch::updateOrCreate(
                        ['name' => $autoPushBranch],
                        [
                            'commit_hash' => $currentCommit,
                            'pushed_at' => now(),
                        ]
                    );
                } else {
                    $message .= " Проте не вдалося запушити на origin/{$autoPushBranch}.";
                }
            }
        }

        return $this->redirectWithFeedback('success', $message, $commandsOutput);
    }

    public function deployPartial(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $branch = Str::of($branch)->trim()->value() ?: 'main';
        $branch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branch) ?: 'main';

        $pathsInput = $request->input('paths', '');

        if ($redirect = $this->redirectIfShellUnavailable($branch)) {
            return $redirect;
        }

        // Парсимо та валідуємо шляхи
        $parsed = $this->parseAndValidatePaths($pathsInput);
        $paths = $parsed['valid'];
        $pathErrors = $parsed['errors'];

        if ($paths === []) {
            $errorMessage = 'Не вказано жодного валідного шляху для часткового деплою.';
            if ($pathErrors !== []) {
                $errorMessage .= ' Помилки: ' . implode('; ', $pathErrors);
            }
            return $this->redirectWithFeedback('error', $errorMessage, []);
        }

        $repoPath = base_path();
        $commandsOutput = [];

        // Зберігаємо поточний коміт для резервної копії
        $currentCommitProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
        $currentCommit = trim($currentCommitProcess->getOutput());
        $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $currentCommitProcess);

        if (! $currentCommitProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося зчитати поточний коміт.', $commandsOutput);
        }

        if ($currentCommit !== '') {
            $this->storeBackup([
                'timestamp' => now()->toIso8601String(),
                'commit' => $currentCommit,
                'branch' => $branch,
            ]);
        }

        // Fetch origin
        $fetchProcess = $this->runCommand(['git', 'fetch', 'origin'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git fetch origin', $fetchProcess);

        if (! $fetchProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Команда "git fetch" завершилась з помилкою.', $commandsOutput);
        }

        // Визначаємо remote SHA
        $remoteShaProcess = $this->runCommand(['git', 'rev-parse', "origin/{$branch}"], $repoPath);
        $commandsOutput[] = $this->formatProcess("git rev-parse origin/{$branch}", $remoteShaProcess);

        if (! $remoteShaProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', "Гілку origin/{$branch} не знайдено.", $commandsOutput);
        }

        $remoteSha = trim($remoteShaProcess->getOutput());

        // Створюємо тимчасову директорію
        $tmpDir = storage_path('app/partial_deploy_' . Str::random(8));
        File::ensureDirectoryExists($tmpDir);

        // Перевіряємо які шляхи існують у remote
        $existingPaths = [];
        $missingPaths = [];

        foreach ($paths as $path) {
            $checkProcess = $this->runCommand(['git', 'cat-file', '-e', "origin/{$branch}:{$path}"], $repoPath);
            if ($checkProcess->isSuccessful()) {
                $existingPaths[] = $path;
            } else {
                $missingPaths[] = $path;
            }
        }

        $commandsOutput[] = [
            'command' => 'partial: перевірка шляхів у remote',
            'successful' => true,
            'output' => 'Існують: ' . (empty($existingPaths) ? '—' : implode(', ', $existingPaths)) . 
                        "\nВідсутні: " . (empty($missingPaths) ? '—' : implode(', ', $missingPaths)),
        ];

        // Якщо є шляхи для завантаження — створюємо архів
        $extractDir = $tmpDir . '/extract';
        File::ensureDirectoryExists($extractDir);

        if (! empty($existingPaths)) {
            $archivePath = $tmpDir . '/partial.zip';
            $archiveCommand = array_merge(
                ['git', 'archive', '--format=zip', "--output={$archivePath}", "origin/{$branch}"],
                $existingPaths
            );

            $archiveProcess = $this->runCommand($archiveCommand, $repoPath);
            $commandsOutput[] = $this->formatProcess('git archive ...', $archiveProcess);

            if (! $archiveProcess->isSuccessful()) {
                File::deleteDirectory($tmpDir);
                return $this->redirectWithFeedback('error', 'Не вдалося створити архів вказаних шляхів.', $commandsOutput);
            }

            // Розпаковуємо архів
            if (! class_exists(ZipArchive::class)) {
                File::deleteDirectory($tmpDir);
                return $this->redirectWithFeedback('error', 'Розширення ZipArchive недоступне на сервері.', $commandsOutput);
            }

            $zip = new ZipArchive();
            if ($zip->open($archivePath) !== true) {
                File::deleteDirectory($tmpDir);
                return $this->redirectWithFeedback('error', 'Не вдалося відкрити архів.', $commandsOutput);
            }

            $zip->extractTo($extractDir);
            $zip->close();

            $commandsOutput[] = [
                'command' => 'partial: розпакування архіву',
                'successful' => true,
                'output' => "Розпаковано до {$extractDir}",
            ];
        }

        // Застосовуємо зміни до base_path()
        foreach ($paths as $path) {
            $localPath = $repoPath . '/' . $path;
            $sourcePath = $extractDir . '/' . $path;

            // Видаляємо локальний шлях (якщо існує)
            if (File::exists($localPath)) {
                if (File::isDirectory($localPath)) {
                    File::deleteDirectory($localPath);
                } else {
                    File::delete($localPath);
                }
                $commandsOutput[] = [
                    'command' => "partial: delete {$path}",
                    'successful' => true,
                    'output' => 'OK',
                ];
            }

            // Копіюємо з архіву (якщо шлях існує у remote)
            if (in_array($path, $existingPaths, true) && File::exists($sourcePath)) {
                File::ensureDirectoryExists(dirname($localPath));
                if (File::isDirectory($sourcePath)) {
                    File::copyDirectory($sourcePath, $localPath);
                } else {
                    File::copy($sourcePath, $localPath);
                }
                $commandsOutput[] = [
                    'command' => "partial: copy {$path}",
                    'successful' => true,
                    'output' => 'OK',
                ];
            }
        }

        // Видаляємо тимчасову директорію
        File::deleteDirectory($tmpDir);

        // Track branch usage
        $pathsList = implode(', ', $paths);
        BranchUsageHistory::trackUsage(
            $branch,
            'partial_deploy',
            "Частковий деплой шляхів: {$pathsList}"
        );

        $message = "Частковий деплой виконано успішно з гілки {$branch}. Оновлено шляхи: {$pathsList}.";
        if ($pathErrors !== []) {
            $message .= ' Попередження: ' . implode('; ', $pathErrors);
        }

        return $this->redirectWithFeedback('success', $message, $commandsOutput);
    }

    public function pushCurrent(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'master');
        $branch = Str::of($branch)->trim()->value() ?: 'master';
        $branch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branch) ?: 'master';

        if ($redirect = $this->redirectIfShellUnavailable($branch)) {
            return $redirect;
        }

        $repoPath = base_path();
        $commandsOutput = [];

        $currentBranchProcess = $this->runCommand(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git rev-parse --abbrev-ref HEAD', $currentBranchProcess);

        if (! $currentBranchProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося визначити поточну гілку.', $commandsOutput);
        }

        $pushProcess = $this->runCommand(['git', 'push', '--force', 'origin', "HEAD:{$branch}"], $repoPath);
        $commandsOutput[] = $this->formatProcess("git push --force origin HEAD:{$branch}", $pushProcess);

        if (! $pushProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося запушити поточний стан на віддалену гілку.', $commandsOutput);
        }

        $currentBranch = trim($currentBranchProcess->getOutput());

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $branch,
            'push',
            "Пуш поточного стану на віддалену гілку з {$currentBranch}"
        );

        return $this->redirectWithFeedback(
            'success',
            "Поточний стан гілки {$currentBranch} запушено на origin/{$branch}.",
            $commandsOutput
        );
    }

    public function rollback(Request $request): RedirectResponse
    {
        if ($redirect = $this->redirectIfShellUnavailable()) {
            return $redirect;
        }

        $commit = $request->input('commit');
        $repoPath = base_path();

        $backups = $this->loadBackups();
        $selected = collect($backups)->firstWhere('commit', $commit);

        if (! $selected) {
            return $this->redirectWithFeedback('error', 'Обраного коміту немає в історії резервних копій.', []);
        }

        $commandsOutput = [];
        $resetProcess = $this->runCommand(['git', 'reset', '--hard', $selected['commit']], $repoPath);
        $commandsOutput[] = $this->formatProcess("git reset --hard {$selected['commit']}", $resetProcess);

        if (! $resetProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося виконати відкат до резервного коміту.', $commandsOutput);
        }

        return $this->redirectWithFeedback('success', 'Виконано відкат до вибраного робочого стану.', $commandsOutput);
    }

    public function createBackupBranch(Request $request): RedirectResponse
    {
        $branchName = Str::of($request->input('branch_name', ''))->trim()->value();
        $branchName = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branchName);

        if ($redirect = $this->redirectIfShellUnavailable($branchName ?: null)) {
            return $redirect;
        }

        if ($branchName === '') {
            return $this->redirectWithFeedback('error', 'Вкажіть коректну назву гілки для резервного бекапу.', []);
        }

        $commitInput = Str::of($request->input('commit', 'current'))->trim()->value();
        $useCurrentHead = $commitInput === '' || $commitInput === 'current';

        if (! $useCurrentHead && ! preg_match('/^[0-9a-f]{7,40}$/i', $commitInput)) {
            return $this->redirectWithFeedback('error', 'Невірний формат коміту для створення гілки.', []);
        }

        $repoPath = base_path();
        $commandsOutput = [];

        if ($useCurrentHead) {
            $revParseProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
            $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $revParseProcess);

            if (! $revParseProcess->isSuccessful()) {
                return $this->redirectWithFeedback('error', 'Не вдалося визначити поточний коміт.', $commandsOutput);
            }

            $resolvedCommit = trim($revParseProcess->getOutput());
        } else {
            $resolvedCommit = $commitInput;
        }

        $branchListProcess = $this->runCommand(['git', 'branch', '--list', $branchName], $repoPath);
        $commandsOutput[] = $this->formatProcess("git branch --list {$branchName}", $branchListProcess);

        if (! $branchListProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося перевірити наявність існуючої гілки.', $commandsOutput);
        }

        if (trim($branchListProcess->getOutput()) !== '') {
            return $this->redirectWithFeedback('error', 'Гілка з такою назвою вже існує.', $commandsOutput);
        }

        $createProcess = $this->runCommand(['git', 'branch', $branchName, $resolvedCommit], $repoPath);
        $commandsOutput[] = $this->formatProcess("git branch {$branchName} {$resolvedCommit}", $createProcess);

        if (! $createProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося створити резервну гілку.', $commandsOutput);
        }

        BackupBranch::updateOrCreate(
            ['name' => $branchName],
            [
                'commit_hash' => $resolvedCommit,
                'pushed_at' => null,
            ]
        );

        return $this->redirectWithFeedback('success', 'Резервну гілку створено.', $commandsOutput);
    }

    public function pushBackupBranch(BackupBranch $backupBranch): RedirectResponse
    {
        if ($redirect = $this->redirectIfShellUnavailable($backupBranch->name)) {
            return $redirect;
        }

        $repoPath = base_path();
        $commandsOutput = [];

        $branchCheck = $this->runCommand(['git', 'branch', '--list', $backupBranch->name], $repoPath);
        $commandsOutput[] = $this->formatProcess("git branch --list {$backupBranch->name}", $branchCheck);

        if (! $branchCheck->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося перевірити наявність резервної гілки.', $commandsOutput);
        }

        if (trim($branchCheck->getOutput()) === '') {
            return $this->redirectWithFeedback('error', 'Резервну гілку не знайдено.', $commandsOutput);
        }

        $pushProcess = $this->runCommand(['git', 'push', 'origin', $backupBranch->name], $repoPath);
        $commandsOutput[] = $this->formatProcess("git push origin {$backupBranch->name}", $pushProcess);

        if (! $pushProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося запушити резервну гілку.', $commandsOutput);
        }

        $backupBranch->forceFill(['pushed_at' => now()])->save();

        return $this->redirectWithFeedback('success', 'Резервну гілку надіслано на віддалений репозиторій.', $commandsOutput);
    }

    public function createAndPushBranch(Request $request): RedirectResponse
    {
        $branchName = Str::of($request->input('quick_branch_name', ''))->trim()->value();
        $branchName = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branchName);

        if ($redirect = $this->redirectIfShellUnavailable($branchName ?: null)) {
            return $redirect;
        }

        if ($branchName === '') {
            return $this->redirectWithFeedback('error', 'Вкажіть коректну назву гілки.', []);
        }

        $repoPath = base_path();
        $commandsOutput = [];

        // Get current commit
        $revParseProcess = $this->runCommand(['git', 'rev-parse', 'HEAD'], $repoPath);
        $commandsOutput[] = $this->formatProcess('git rev-parse HEAD', $revParseProcess);

        if (! $revParseProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося визначити поточний коміт.', $commandsOutput);
        }

        $currentCommit = trim($revParseProcess->getOutput());

        // Check if branch exists locally
        $branchListProcess = $this->runCommand(['git', 'branch', '--list', $branchName], $repoPath);
        $commandsOutput[] = $this->formatProcess("git branch --list {$branchName}", $branchListProcess);

        $branchExists = trim($branchListProcess->getOutput()) !== '';

        if (! $branchExists) {
            // Create branch
            $createProcess = $this->runCommand(['git', 'branch', $branchName, $currentCommit], $repoPath);
            $commandsOutput[] = $this->formatProcess("git branch {$branchName} {$currentCommit}", $createProcess);

            if (! $createProcess->isSuccessful()) {
                return $this->redirectWithFeedback('error', 'Не вдалося створити гілку.', $commandsOutput);
            }
        }

        // Push branch to remote
        $pushProcess = $this->runCommand(['git', 'push', '--force', 'origin', $branchName], $repoPath);
        $commandsOutput[] = $this->formatProcess("git push --force origin {$branchName}", $pushProcess);

        if (! $pushProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося запушити гілку на віддалений репозиторій.', $commandsOutput);
        }

        // Track branch usage
        BranchUsageHistory::trackUsage(
            $branchName,
            'create_and_push',
            'Швидке створення та пуш гілки з поточним станом сайту'
        );

        // Update or create backup branch record
        BackupBranch::updateOrCreate(
            ['name' => $branchName],
            [
                'commit_hash' => $currentCommit,
                'pushed_at' => now(),
            ]
        );

        $action = $branchExists ? 'оновлено та запушено' : 'створено та запушено';
        return $this->redirectWithFeedback(
            'success',
            "Гілку {$branchName} успішно {$action} на віддалений репозиторій.",
            $commandsOutput
        );
    }

    private function redirectWithFeedback(string $status, string $message, array $commandsOutput): RedirectResponse
    {
        return redirect()
            ->route('deployment.index')
            ->with('deployment', [
                'status' => $status,
                'message' => $message,
                'commands' => $commandsOutput,
            ]);
    }

    private function loadBackups(): array
    {
        $path = $this->ensureBackupFile();

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function storeBackup(array $backup): void
    {
        $path = $this->ensureBackupFile();
        $backups = $this->loadBackups();
        $backups[] = $backup;

        File::put($path, json_encode($backups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
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

    private function redirectIfShellUnavailable(?string $branch = null): ?RedirectResponse
    {
        if ($this->supportsShellCommands()) {
            return null;
        }

        $logs = $branch !== null
            ? ['Shell commands are disabled. Перемкніться на API режим.']
            : [];

        return redirect()
            ->route('deployment.native.index')
            ->with('deployment_native', [
                'status' => 'error',
                'message' => 'Режим через SSH недоступний на цьому сервері. Спробуйте альтернативний режим через GitHub API.',
                'logs' => $logs,
                'branch' => $branch,
            ]);
    }

    private function supportsShellCommands(): bool
    {
        return function_exists('proc_open');
    }

    private function runCommand(array $command, string $workingDirectory): Process
    {
        $process = new Process($command, $workingDirectory);
        $process->run();

        return $process;
    }

    private function formatProcess(string $command, Process $process): array
    {
        return [
            'command' => $command,
            'successful' => $process->isSuccessful(),
            'output' => trim($process->getErrorOutput() . $process->getOutput()),
        ];
    }
}
