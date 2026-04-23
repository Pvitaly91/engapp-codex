<?php

namespace App\Console\Commands;

use App\Services\V3PromptGenerator\V3ChangedPackagesApplyService;
use Illuminate\Console\Command;
use Throwable;

class V3ApplyChangedCommand extends Command
{
    protected $signature = 'v3:apply-changed
        {target? : Optional folder root, package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--base= : Base git ref for ref-diff mode}
        {--head= : Head git ref for ref-diff mode}
        {--staged : Diff staged changes against HEAD}
        {--working-tree : Diff the working tree against HEAD}
        {--include-untracked : Include untracked files as added package signals}
        {--dry-run : Execute full changed-package preflight without live writes}
        {--force : Allow live changed-package apply after a clean full-scope preflight}
        {--json : Output the changed-package apply result as JSON}
        {--write-report : Write a Markdown report into storage/app/changed-package-apply-reports/v3}
        {--with-release-check : Add release-check summaries to the changed-package planner output}
        {--skip-release-check : Skip release-check execution during seed/refresh package phases}
        {--check-profile=release : scaffold | release}
        {--strict : Treat planner or preflight warnings as failures}';

    protected $description = 'Apply git-diff-aware V3 changed packages by cleaning deleted packages first, then seeding or refreshing current ones.';

    public function __construct(
        private readonly V3ChangedPackagesApplyService $changedPackagesApplyService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));

        try {
            $result = $this->changedPackagesApplyService->run($target !== '' ? $target : null, [
                'base' => (string) ($this->option('base') ?? ''),
                'head' => (string) ($this->option('head') ?? ''),
                'staged' => (bool) $this->option('staged'),
                'working_tree' => (bool) $this->option('working-tree'),
                'include_untracked' => (bool) $this->option('include-untracked'),
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'with_release_check' => (bool) $this->option('with-release-check'),
                'skip_release_check' => (bool) $this->option('skip-release-check'),
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
                'strict' => (bool) $this->option('strict'),
            ]);
        } catch (Throwable $exception) {
            $result = $this->errorResult($target, $exception);
        }

        if ($this->option('write-report')) {
            $path = $this->changedPackagesApplyService->writeReport($result);
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

        $this->line('Command: v3:apply-changed');
        $this->line('Diff Mode: ' . (string) ($result['diff']['mode'] ?? 'working_tree'));
        $this->line('Target Scope: ' . (string) (($result['scope']['input'] ?? null) ?: 'database/seeders/V3'));
        $this->line('Scope Root: ' . (string) ($result['scope']['resolved_root_relative_path'] ?? 'unknown'));
        $this->line('Execution: ' . ((bool) ($result['execution']['dry_run'] ?? false) ? 'dry-run' : 'live'));
        $this->line('Force: ' . $this->boolString((bool) ($result['execution']['force'] ?? false)));
        $this->line('Planner Release Check: ' . (((bool) ($result['scope']['with_release_check'] ?? false)) ? 'enabled' : 'skipped'));
        $this->line('Package Release Check: ' . (((bool) ($result['scope']['skip_release_check'] ?? false)) ? 'skipped' : 'enabled'));
        $this->line('Check Profile: ' . (string) ($result['scope']['check_profile'] ?? 'release'));
        $this->newLine();

        $this->line(sprintf(
            'Summary: changed=%d; deleted-cleanup=%d; seed=%d; refresh=%d; skipped=%d; blocked=%d; warnings=%d',
            (int) ($result['plan']['summary']['changed_packages'] ?? 0),
            (int) ($result['plan']['summary']['deleted_cleanup_candidates'] ?? 0),
            (int) ($result['plan']['summary']['seed_candidates'] ?? 0),
            (int) ($result['plan']['summary']['refresh_candidates'] ?? 0),
            (int) ($result['plan']['summary']['skipped'] ?? 0),
            (int) ($result['plan']['summary']['blocked'] ?? 0),
            (int) ($result['plan']['summary']['warnings'] ?? 0)
        ));
        $this->line(sprintf(
            'Preflight: candidates=%d; ok=%d; warn=%d; fail=%d; skipped=%d',
            (int) ($result['preflight']['summary']['candidates'] ?? 0),
            (int) ($result['preflight']['summary']['ok'] ?? 0),
            (int) ($result['preflight']['summary']['warn'] ?? 0),
            (int) ($result['preflight']['summary']['fail'] ?? 0),
            (int) ($result['preflight']['summary']['skipped'] ?? 0)
        ));
        $this->newLine();

        if ((bool) ($result['execution']['dry_run'] ?? false) && empty($result['error'])) {
            $this->line('[OK] Dry-run changed-package preflight passed: scope is clean for live execution.');
            $this->newLine();
        } elseif (! empty($result['error']) && in_array(($result['error']['reason'] ?? null), ['preflight_failed', 'blocked_packages', 'warnings_are_fatal'], true)) {
            $this->line('[FAIL] Live execution did not start because full-scope preflight was not clean.');
            $this->newLine();
        }

        $this->line('Deleted Packages Cleanup Phase');
        $this->renderPhasePackages((array) ($result['execution']['cleanup_deleted']['packages'] ?? []), 'cleanup_deleted');
        $this->newLine();

        $this->line('Current Packages Upsert Phase');
        $this->renderPhasePackages((array) ($result['execution']['upsert_present']['packages'] ?? []), 'upsert_present');

        if (! empty($result['error']['phase']) && ! empty($result['error']['package'])) {
            $this->newLine();
            $this->line('Stopped On Phase: ' . (string) $result['error']['phase']);
            $this->line('Stopped On Package: ' . (string) $result['error']['package']);

            $execution = (string) ($result['error']['phase'] ?? '') === 'cleanup_deleted'
                ? (array) ($result['execution']['cleanup_deleted'] ?? [])
                : (array) ($result['execution']['upsert_present'] ?? []);

            $this->line(sprintf(
                'Completed package executions in phase: %d; remaining packages were not executed.',
                (int) ($execution['completed'] ?? 0)
            ));
        }

        if (! empty($result['artifacts']['report_path'])) {
            $this->newLine();
            $this->line('Report: ' . (string) $result['artifacts']['report_path']);
        }

        if ((bool) ($result['execution']['dry_run'] ?? false) && empty($result['error'])) {
            $this->newLine();
            $this->line('Live Apply Hint:');
            $this->line($this->commandHint($result, true));
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     */
    private function renderPhasePackages(array $packages, string $phase): void
    {
        if ($packages === []) {
            $this->line('- None.');

            return;
        }

        foreach ($packages as $package) {
            $path = (string) ($package['relative_path'] ?? $package['package_key'] ?? 'unknown');
            $status = (string) ($package['status'] ?? 'pending');
            $action = (string) ($package['action'] ?? 'skip');

            $line = match ($status) {
                'ok' => $phase === 'cleanup_deleted'
                    ? '[OK] Cleaned up deleted package: ' . $path
                    : sprintf('[OK] %s package: %s', $action === 'refresh' ? 'Refreshed' : 'Seeded', $path),
                'failed' => sprintf('[FAIL] %s: %s', $phase === 'cleanup_deleted' ? 'Cleanup failed' : 'Upsert failed', $path),
                'pending' => '[PENDING] Not executed after earlier failure: ' . $path,
                default => '[SKIP] ' . $path,
            };

            $this->line($line);

            if ((bool) ($package['seed_run_removed'] ?? false)) {
                $this->line('  [OK] Removed seed_runs entry: ' . $path);
            }

            if ((bool) ($package['seed_run_logged'] ?? false)) {
                $this->line('  [OK] Logged seed_runs entry: ' . $path);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function errorResult(string $target, Throwable $exception): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => $this->option('base') ?: null,
                'head' => $this->option('head') ?: null,
                'include_untracked' => (bool) $this->option('include-untracked'),
            ],
            'scope' => [
                'input' => $target !== '' ? $target : 'database/seeders/V3',
                'resolved_root_absolute_path' => null,
                'resolved_root_relative_path' => null,
                'single_package' => false,
                'with_release_check' => (bool) $this->option('with-release-check'),
                'skip_release_check' => (bool) $this->option('skip-release-check'),
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
                'strict' => (bool) $this->option('strict'),
            ],
            'plan' => [
                'summary' => $this->emptySummary(),
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'error' => null,
            ],
            'preflight' => [
                'executed' => false,
                'summary' => $this->emptyPreflightSummary(),
                'packages' => [],
            ],
            'execution' => [
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'fail_fast' => true,
                'folder_transactional' => false,
                'cleanup_deleted' => $this->emptyExecutionPhase(),
                'upsert_present' => $this->emptyExecutionPhase(),
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'planning',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptySummary(): array
    {
        return [
            'changed_packages' => 0,
            'seed_candidates' => 0,
            'refresh_candidates' => 0,
            'deleted_cleanup_candidates' => 0,
            'skipped' => 0,
            'blocked' => 0,
            'warnings' => 0,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function emptyPreflightSummary(): array
    {
        return [
            'candidates' => 0,
            'ok' => 0,
            'warn' => 0,
            'fail' => 0,
            'skipped' => 0,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyExecutionPhase(): array
    {
        return [
            'started' => 0,
            'completed' => 0,
            'succeeded' => 0,
            'failed' => 0,
            'stopped_on' => null,
            'packages' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function commandHint(array $result, bool $live): string
    {
        $target = (string) (($result['scope']['resolved_root_relative_path'] ?? null) ?: ($result['scope']['input'] ?? 'database/seeders/V3'));
        $parts = [
            'php artisan v3:apply-changed',
            $this->shellEscapeArgument($target),
        ];

        $parts = array_merge($parts, $this->diffModeParts($result));
        $parts[] = $live ? '--force' : '--dry-run';

        if ((bool) ($result['scope']['with_release_check'] ?? false)) {
            $parts[] = '--with-release-check';
        }

        if ((bool) ($result['scope']['skip_release_check'] ?? false)) {
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

    /**
     * @param  array<string, mixed>  $result
     * @return array<int, string>
     */
    private function diffModeParts(array $result): array
    {
        $parts = match ((string) ($result['diff']['mode'] ?? 'working_tree')) {
            'staged' => ['--staged'],
            'refs' => array_values(array_filter([
                ($result['diff']['base'] ?? null) ? '--base=' . (string) $result['diff']['base'] : null,
                ($result['diff']['head'] ?? null) ? '--head=' . (string) $result['diff']['head'] : null,
            ])),
            default => [],
        };

        if ((bool) ($result['diff']['include_untracked'] ?? false)) {
            $parts[] = '--include-untracked';
        }

        return $parts;
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

    /**
     * @param  array<string, mixed>  $result
     */
    private function exitCodeForResult(array $result): int
    {
        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
    }
}
