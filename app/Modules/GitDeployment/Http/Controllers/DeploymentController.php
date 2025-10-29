<?php

namespace App\Modules\GitDeployment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Modules\GitDeployment\Models\BackupBranch;
use Symfony\Component\Process\Process;

class DeploymentController extends BaseController
{
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
            ->get();

        $branchProcess = $this->runCommand(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], base_path());
        $currentBranch = $branchProcess->isSuccessful()
            ? trim($branchProcess->getOutput())
            : null;

        return view('git-deployment::deployment.index', [
            'backups' => $backups,
            'feedback' => $feedback,
            'backupBranches' => $backupBranches,
            'currentBranch' => $currentBranch,
            'supportsShell' => $this->supportsShellCommands(),
        ]);
    }

    public function deploy(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $branch = Str::of($branch)->trim()->value() ?: 'main';
        $branch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branch) ?: 'main';

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

        $message = 'Сайт успішно оновлено до останнього стану гілки.';
        if (! $backupStored) {
            $message .= ' Увага: резервну копію не збережено.';
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
        $path = storage_path('app/' . self::BACKUP_FILE);

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function storeBackup(array $backup): void
    {
        $backups = $this->loadBackups();
        $backups[] = $backup;

        File::put(storage_path('app/' . self::BACKUP_FILE), json_encode($backups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function redirectIfShellUnavailable(?string $branch = null): ?RedirectResponse
    {
        if ($this->supportsShellCommands()) {
            return null;
        }

        return $this->redirectWithFeedback(
            'error',
            'Режим через SSH недоступний на цьому сервері. Спробуйте альтернативний режим через GitHub API.',
            $branch !== null ? [['command' => 'git', 'output' => 'Shell commands are disabled.', 'successful' => false]] : []
        );
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
