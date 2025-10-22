<?php

namespace App\Http\Controllers;

use App\Models\BackupBranch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class DeploymentController extends Controller
{
    private const BACKUP_FILE = 'deployment_backups.json';

    public function index()
    {
        $backups = array_reverse($this->loadBackups());
        $feedback = session('deployment');

        $backupBranches = BackupBranch::query()
            ->orderByDesc('created_at')
            ->get();

        return view('deployment.index', [
            'backups' => $backups,
            'feedback' => $feedback,
            'backupBranches' => $backupBranches,
        ]);
    }

    public function deploy(Request $request): RedirectResponse
    {
        $branch = $request->input('branch', 'main');
        $branch = Str::of($branch)->trim()->value() ?: 'main';
        $branch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branch) ?: 'main';

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

        $message = 'Сайт успішно оновлено до останнього стану гілки.';
        if (! $backupStored) {
            $message .= ' Увага: резервну копію не збережено.';
        }

        return $this->redirectWithFeedback('success', $message, $commandsOutput);
    }

    public function rollback(Request $request): RedirectResponse
    {
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
            return $this->redirectWithFeedback('error', 'Не вдалося перевірити наявні гілки.', $commandsOutput);
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

        $message = "Резервну гілку \"{$branchName}\" створено на коміті {$resolvedCommit}.";
        $message .= ' Ви можете запушити її на GitHub із цього інтерфейсу.';

        return $this->redirectWithFeedback('success', $message, $commandsOutput);
    }

    public function pushBackupBranch(BackupBranch $backupBranch): RedirectResponse
    {
        $repoPath = base_path();
        $commandsOutput = [];

        $branchCheck = $this->runCommand(['git', 'branch', '--list', $backupBranch->name], $repoPath);
        $commandsOutput[] = $this->formatProcess("git branch --list {$backupBranch->name}", $branchCheck);

        if (! $branchCheck->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося перевірити наявність гілки.', $commandsOutput);
        }

        if (trim($branchCheck->getOutput()) === '') {
            return $this->redirectWithFeedback('error', 'Резервної гілки не знайдено у локальному репозиторії.', $commandsOutput);
        }

        $pushProcess = $this->runCommand(['git', 'push', 'origin', $backupBranch->name], $repoPath);
        $commandsOutput[] = $this->formatProcess("git push origin {$backupBranch->name}", $pushProcess);

        if (! $pushProcess->isSuccessful()) {
            return $this->redirectWithFeedback('error', 'Не вдалося запушити резервну гілку на GitHub.', $commandsOutput);
        }

        $backupBranch->forceFill(['pushed_at' => now()])->save();

        return $this->redirectWithFeedback('success', 'Резервну гілку успішно запушено на віддалений репозиторій.', $commandsOutput);
    }

    private function runCommand(array $command, string $workingDirectory): Process
    {
        $process = new Process($command, $workingDirectory);
        $process->run();

        return $process;
    }

    private function formatProcess(string $command, ?Process $process): array
    {
        if (! $process) {
            return [
                'command' => $command,
                'successful' => false,
                'output' => 'Команда не була виконана.',
            ];
        }

        $output = trim($process->getOutput());
        $errorOutput = trim($process->getErrorOutput());
        $combined = trim($output . ($errorOutput !== '' ? PHP_EOL . $errorOutput : ''));

        return [
            'command' => $command,
            'successful' => $process->isSuccessful(),
            'output' => $combined !== '' ? $combined : 'Без виводу',
        ];
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
        $backups = array_slice($backups, -10);

        $path = storage_path('app/' . self::BACKUP_FILE);
        File::put($path, json_encode($backups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function redirectWithFeedback(string $status, string $message, array $commands): RedirectResponse
    {
        return redirect()
            ->route('deployment.index')
            ->with('deployment', [
                'status' => $status,
                'message' => $message,
                'commands' => $commands,
            ]);
    }
}
