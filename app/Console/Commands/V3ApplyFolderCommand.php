<?php

namespace App\Console\Commands;

use App\Services\V3PromptGenerator\V3FolderApplyService;
use Illuminate\Console\Command;
use Throwable;

class V3ApplyFolderCommand extends Command
{
    protected $signature = 'v3:apply-folder
        {target : Folder root, package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--mode=sync : missing | refresh | sync}
        {--dry-run : Execute the planner-ordered folder apply flow in rollback-only package modes}
        {--force : Allow live folder apply}
        {--json : Output the folder apply result as JSON}
        {--write-report : Write a Markdown report into storage/app/folder-apply-reports/v3}
        {--with-release-check : Add per-package release-check summaries to the planner result}
        {--skip-release-check : Skip per-package release-check execution during seed/refresh}
        {--check-profile=release : scaffold | release}
        {--strict : Treat planner or package warnings as failures}';

    protected $description = 'Apply planner-ordered seed/refresh actions for one resolved V3 subtree without scanning outside scope.';

    public function __construct(
        private readonly V3FolderApplyService $folderApplyService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));
        $mode = $this->modePayload();

        try {
            $result = $this->folderApplyService->run($target, [
                'mode' => (string) $this->option('mode'),
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'with_release_check' => (bool) $this->option('with-release-check'),
                'skip_release_check' => (bool) $this->option('skip-release-check'),
                'check_profile' => (string) $this->option('check-profile'),
                'strict' => (bool) $this->option('strict'),
            ]);
        } catch (Throwable $exception) {
            $result = $this->errorResult($target, $mode, $exception);
        }

        if ($this->option('write-report')) {
            $path = $this->folderApplyService->writeReport($result);
            $result['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($this->option('json')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

            return $this->exitCodeForResult($result);
        }

        $this->renderHumanOutput($result);

        return $this->exitCodeForResult($result);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderHumanOutput(array $result): void
    {
        if (! empty($result['error']['message'])) {
            $this->error((string) $result['error']['message']);
            $this->newLine();
        }

        $this->line('Command: v3:apply-folder');
        $this->line('Target: ' . (string) ($result['scope']['input'] ?? 'unknown'));
        $this->line('Scope Root: ' . (string) ($result['scope']['resolved_root_relative_path'] ?? 'unknown'));
        $this->line('Mode: ' . (string) ($result['scope']['mode'] ?? 'sync'));
        $this->line('Execution: ' . ((bool) ($result['execution']['dry_run'] ?? false) ? 'dry-run' : 'live'));
        $this->line('Force: ' . $this->boolString((bool) ($result['execution']['force'] ?? false)));
        $this->line(
            'Planner Release Check: '
            . ((bool) ($result['scope']['with_release_check'] ?? false) ? 'enabled' : 'skipped')
        );
        $this->line(
            'Package Release Check: '
            . ((bool) $this->option('skip-release-check') ? 'skipped' : 'enabled')
        );
        $this->line('Check Profile: ' . (string) ($result['scope']['check_profile'] ?? 'release'));
        $this->line('Package Count: ' . (int) ($result['plan']['summary']['total_packages'] ?? 0));
        $this->newLine();

        $this->line(sprintf(
            'Summary: total=%d; seed=%d; refresh=%d; skipped=%d; blocked=%d; warnings=%d',
            (int) ($result['plan']['summary']['total_packages'] ?? 0),
            (int) ($result['plan']['summary']['seed_candidates'] ?? 0),
            (int) ($result['plan']['summary']['refresh_candidates'] ?? 0),
            (int) ($result['plan']['summary']['skipped'] ?? 0),
            (int) ($result['plan']['summary']['blocked'] ?? 0),
            (int) ($result['plan']['summary']['warnings'] ?? 0)
        ));
        $this->line('Execution Contract: planner-ordered; package-atomic; folder-non-transactional; fail-fast');
        $this->newLine();

        if ((int) ($result['plan']['summary']['blocked'] ?? 0) > 0) {
            $this->line('Planner blockers were detected before execution:');

            foreach ((array) ($result['plan']['packages'] ?? []) as $package) {
                if (($package['recommended_action'] ?? null) !== 'blocked') {
                    continue;
                }

                $this->line('- ' . (string) ($package['relative_path'] ?? 'unknown'));
            }

            $this->newLine();
        }

        if ((array) ($result['execution']['packages'] ?? []) === []) {
            $this->line('No packages discovered inside the resolved subtree.');
        } else {
            foreach ((array) ($result['execution']['packages'] ?? []) as $package) {
                $path = (string) ($package['relative_path'] ?? 'unknown');
                $action = (string) ($package['action'] ?? 'skip');
                $status = (string) ($package['status'] ?? 'pending');

                $this->line($this->executionLine($path, $action, $status, (bool) ($result['execution']['dry_run'] ?? false)));
            }
        }

        if (! empty($result['error']['package'])) {
            $this->newLine();
            $this->line('Stopped On: ' . (string) $result['error']['package']);
            $this->line(sprintf(
                'Completed package executions: %d; remaining packages were not executed.',
                (int) ($result['execution']['completed'] ?? 0)
            ));
        }

        if (! empty($result['artifacts']['report_path'])) {
            $this->newLine();
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }

        if ((bool) ($result['execution']['dry_run'] ?? false) && empty($result['error'])) {
            $this->newLine();
            $this->line('Live Apply Hint:');
            $this->line($this->applyCommandHint($result, true));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function modePayload(): array
    {
        return [
            'mode' => trim((string) ($this->option('mode') ?? 'sync')) ?: 'sync',
            'dry_run' => (bool) $this->option('dry-run'),
            'force' => (bool) $this->option('force'),
            'with_release_check' => (bool) $this->option('with-release-check'),
            'check_profile' => trim((string) ($this->option('check-profile') ?? 'release')) ?: 'release',
        ];
    }

    /**
     * @param  array<string, mixed>  $mode
     * @return array<string, mixed>
     */
    private function errorResult(string $target, array $mode, Throwable $exception): array
    {
        return [
            'scope' => [
                'input' => $target,
                'resolved_root_absolute_path' => null,
                'resolved_root_relative_path' => null,
                'single_package' => false,
                'mode' => $mode['mode'],
                'with_release_check' => $mode['with_release_check'],
                'check_profile' => $mode['check_profile'],
                'strict' => (bool) $this->option('strict'),
            ],
            'plan' => [
                'summary' => $this->emptySummary(),
                'packages' => [],
                'error' => null,
            ],
            'execution' => [
                'dry_run' => $mode['dry_run'],
                'force' => $mode['force'],
                'fail_fast' => true,
                'package_atomic' => true,
                'folder_transactional' => false,
                'started' => 0,
                'completed' => 0,
                'succeeded' => 0,
                'failed' => 0,
                'stopped_on' => null,
                'packages' => [],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'target_resolution',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function exitCodeForResult(array $result): int
    {
        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<string, int>
     */
    private function emptySummary(): array
    {
        return [
            'total_packages' => 0,
            'seed_candidates' => 0,
            'refresh_candidates' => 0,
            'unseed_candidates' => 0,
            'destroy_files_candidates' => 0,
            'destroy_candidates' => 0,
            'skipped' => 0,
            'blocked' => 0,
            'warnings' => 0,
        ];
    }

    private function executionLine(string $path, string $action, string $status, bool $dryRun): string
    {
        return match ($status) {
            'ok' => $dryRun
                ? '[OK] Dry run package passed and rolled back: ' . $path
                : sprintf('[OK] %s: %s', $action === 'refresh' ? 'Refreshed' : 'Seeded', $path),
            'failed' => '[FAIL] ' . $path,
            'blocked' => '[BLOCKED] ' . $path,
            'pending' => '[PENDING] Not executed after earlier failure: ' . $path,
            default => '[SKIP] ' . $path,
        };
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function applyCommandHint(array $result, bool $live): string
    {
        $target = (string) (($result['scope']['resolved_root_relative_path'] ?? null) ?: ($result['scope']['input'] ?? ''));
        $parts = [
            'php artisan v3:apply-folder',
            $this->shellEscapeArgument($target),
            '--mode=' . (string) ($result['scope']['mode'] ?? 'sync'),
        ];

        if ($live) {
            $parts[] = '--force';
        } else {
            $parts[] = '--dry-run';
        }

        if ((bool) ($result['scope']['with_release_check'] ?? false)) {
            $parts[] = '--with-release-check';
        }

        if ((bool) $this->option('skip-release-check')) {
            $parts[] = '--skip-release-check';
        }

        if (($result['scope']['check_profile'] ?? 'release') !== 'release') {
            $parts[] = '--check-profile=' . (string) $result['scope']['check_profile'];
        }

        if ((bool) ($result['scope']['strict'] ?? false)) {
            $parts[] = '--strict';
        }

        return implode(' ', $parts);
    }

    private function shellEscapeArgument(string $value): string
    {
        return preg_match('/\s/', $value) === 1
            ? '"' . str_replace('"', '\"', $value) . '"'
            : $value;
    }

    private function boolString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}
