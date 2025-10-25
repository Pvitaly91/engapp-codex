<?php

namespace App\Http\Controllers;

use App\Models\BackupBranch;
use App\Services\Git\GitHubRepositoryManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class DeploymentGitHubController extends Controller
{
    private const BACKUP_FILE = 'deployment_backups.json';

    public function index(): View
    {
        $service = $this->manager();
        $backups = array_reverse($this->loadBackups());
        $feedback = session('deployment_github');
        $backupBranches = BackupBranch::query()->orderByDesc('created_at')->get();

        return view('deployment.github', [
            'backups' => $backups,
            'feedback' => $feedback,
            'backupBranches' => $backupBranches,
            'currentBranch' => $service->getCurrentBranch(),
            'repositoryInfo' => $service->getRepositoryInfo(),
        ]);
    }

    public function deploy(Request $request): RedirectResponse
    {
        $branch = $this->sanitizeBranch($request->input('branch', 'main'));
        $service = $this->manager();
        $steps = [];

        $currentCommit = $service->getCurrentCommit();
        if ($currentCommit) {
            $this->storeBackup([
                'timestamp' => now()->toIso8601String(),
                'commit' => $currentCommit,
                'branch' => $branch,
            ]);

            $steps[] = [
                'command' => 'Збереження резервної копії',
                'successful' => true,
                'output' => 'Поточний коміт: ' . $currentCommit,
            ];
        }

        try {
            $service->deployBranch($branch, $steps);
        } catch (Throwable $e) {
            return $this->redirectWithFeedback('error', $e->getMessage(), $steps);
        }

        $message = 'Сайт синхронізовано з GitHub без використання shell.';
        if (! $currentCommit) {
            $message .= ' Увага: резервну копію не вдалося створити.';
        }

        return $this->redirectWithFeedback('success', $message, $steps);
    }

    public function pushCurrent(Request $request): RedirectResponse
    {
        $branch = $this->sanitizeBranch($request->input('branch', 'master'));
        $service = $this->manager();
        $steps = [];

        try {
            $service->pushCurrent($branch, $steps);
        } catch (Throwable $e) {
            return $this->redirectWithFeedback('error', $e->getMessage(), $steps);
        }

        return $this->redirectWithFeedback('success', "Зміни репозиторію передано до origin/{$branch} через GitHub API.", $steps);
    }

    public function rollback(Request $request): RedirectResponse
    {
        $commit = Str::of($request->input('commit', ''))->trim()->value();
        if ($commit === '') {
            return $this->redirectWithFeedback('error', 'Необхідно вибрати коміт для відкату.', []);
        }

        $backups = $this->loadBackups();
        $selected = collect($backups)->firstWhere('commit', $commit);
        if (! $selected) {
            return $this->redirectWithFeedback('error', 'Обраний коміт відсутній у резервних копіях.', []);
        }

        $service = $this->manager();
        $steps = [];

        try {
            $service->rollbackToCommit($commit, $steps);
        } catch (Throwable $e) {
            return $this->redirectWithFeedback('error', $e->getMessage(), $steps);
        }

        return $this->redirectWithFeedback('success', 'Розгортання відновлено до обраного коміту.', $steps);
    }

    public function createBackupBranch(Request $request): RedirectResponse
    {
        $branchName = Str::of($request->input('branch_name', ''))->trim()->value();
        $branchName = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branchName);
        if ($branchName === '') {
            return $this->redirectWithFeedback('error', 'Вкажіть коректну назву гілки для резервного бекапу.', []);
        }

        $commitInput = Str::of($request->input('commit', 'current'))->trim()->value();
        $service = $this->manager();

        if ($commitInput !== 'current' && ! preg_match('/^[0-9a-f]{7,40}$/i', $commitInput)) {
            return $this->redirectWithFeedback('error', 'Невірний формат коміту для створення гілки.', []);
        }

        $steps = [];
        $commitSha = $commitInput === 'current'
            ? $service->getCurrentCommit()
            : $commitInput;

        if (! $commitSha) {
            return $this->redirectWithFeedback('error', 'Не вдалося визначити коміт для створення гілки.', []);
        }

        try {
            $service->createBackupBranch($branchName, $commitSha, $steps);
        } catch (Throwable $e) {
            return $this->redirectWithFeedback('error', $e->getMessage(), $steps);
        }

        BackupBranch::updateOrCreate(
            ['name' => $branchName],
            [
                'commit_hash' => $commitSha,
                'pushed_at' => now(),
            ]
        );

        return $this->redirectWithFeedback('success', 'Резервну гілку створено без використання shell та одразу опубліковано у GitHub.', $steps);
    }

    public function pushBackupBranch(BackupBranch $backupBranch): RedirectResponse
    {
        $service = $this->manager();
        $steps = [];

        try {
            $service->pushBackupBranch($backupBranch->name, $backupBranch->commit_hash, $steps);
        } catch (Throwable $e) {
            return $this->redirectWithFeedback('error', $e->getMessage(), $steps);
        }

        $backupBranch->forceFill(['pushed_at' => now()])->save();

        return $this->redirectWithFeedback('success', 'Резервну гілку синхронізовано з GitHub.', $steps);
    }

    private function manager(): GitHubRepositoryManager
    {
        return new GitHubRepositoryManager(config('services.github.token'));
    }

    private function sanitizeBranch(string $branch): string
    {
        $branch = Str::of($branch)->trim()->value() ?: 'main';
        $branch = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $branch) ?: 'main';

        return $branch;
    }

    private function redirectWithFeedback(string $status, string $message, array $commands): RedirectResponse
    {
        return redirect()
            ->route('deployment.github.index')
            ->with('deployment_github', [
                'status' => $status,
                'message' => $message,
                'commands' => $commands,
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
        $backups = array_slice($backups, -10);

        $path = storage_path('app/' . self::BACKUP_FILE);
        File::put($path, json_encode($backups, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
