<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ChangedContentApplyService;
use App\Services\ContentDeployment\ContentOperationLockService;
use App\Services\ContentDeployment\ContentOperationRunService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class ContentApplyChangedCommand extends Command
{
    protected $signature = 'content:apply-changed
        {target? : Optional folder root, package directory, definition.json, top-level loader stub PHP, or real seeder PHP}
        {--domains= : v3,page-v3 | v3 | page-v3}
        {--base= : Base git ref for ref-diff mode}
        {--head= : Head git ref for ref-diff mode}
        {--staged : Diff staged changes against HEAD}
        {--working-tree : Diff the working tree against HEAD}
        {--include-untracked : Include untracked files as added package signals}
        {--dry-run : Execute full unified changed-content preflight without live writes}
        {--force : Allow live changed-content apply after a clean full-scope preflight}
        {--json : Output the unified changed-content apply result as JSON}
        {--write-report : Write a Markdown report into storage/app/content-changed-apply-reports}
        {--with-release-check : Add release-check summaries to the unified changed-content planner output}
        {--skip-release-check : Skip release-check execution during current-package seed or refresh phases}
        {--check-profile=release : scaffold | release}
        {--strict : Treat planner or preflight warnings as failures}
        {--takeover-stale-lock : Explicitly take over a stale global content-operation lock}';

    protected $description = 'Apply git-diff-aware changed content across V3 and Page_V3 by cleaning deleted packages first, then seeding or refreshing current packages.';

    public function __construct(
        private readonly ChangedContentApplyService $changedContentApplyService,
        private readonly ContentOperationRunService $contentOperationRunService,
        private readonly ContentOperationLockService $contentOperationLockService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $target = trim((string) $this->argument('target'));
        $run = null;
        $historyWarning = null;

        try {
            $run = $this->contentOperationRunService->start($this->operationRunContext($target));
        } catch (Throwable $exception) {
            report($exception);
            $historyWarning = 'Content operation history could not be initialized: ' . $exception->getMessage();
        }

        $lockLease = $this->contentOperationLockService->acquire(
            $this->lockContext($run, $target),
            (bool) $this->option('takeover-stale-lock')
        );

        if (! (bool) ($lockLease['acquired'] ?? false)) {
            $result = $this->lockBlockedResult($target, $lockLease);
        } else {
            try {
                $ownerToken = $lockLease['owner_token'] ?? null;
                $result = $this->changedContentApplyService->run($target !== '' ? $target : null, [
                    'domains' => $this->option('domains'),
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
                    'heartbeat' => fn (): null => $this->heartbeatLock($ownerToken),
                ]);
            } catch (Throwable $exception) {
                $result = $this->errorResult($target, $exception);
            } finally {
                $this->contentOperationLockService->release($lockLease['owner_token'] ?? null);
            }

            $result['lock'] = $this->lockPayload($lockLease);
        }

        if ($this->option('write-report')) {
            $path = $this->changedContentApplyService->writeReport($result);
            $result['artifacts']['report_path'] = storage_path('app/' . $path);
        }

        if ($run !== null) {
            try {
                $recordedRun = $this->recordOperationRun($run, $result);
                $result['operation_run'] = [
                    'id' => $recordedRun->id,
                    'status' => $recordedRun->status,
                    'payload_json_path' => $recordedRun->payload_json_path ? storage_path('app/' . $recordedRun->payload_json_path) : null,
                    'report_path' => $recordedRun->report_path ? storage_path('app/' . $recordedRun->report_path) : null,
                ];
            } catch (Throwable $exception) {
                report($exception);
                $historyWarning = 'Content operation history could not be finalized: ' . $exception->getMessage();
            }
        }

        if ($historyWarning !== null) {
            $result['operation_run']['warning'] = $historyWarning;
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

        $this->line('Command: content:apply-changed');
        $this->line('Diff Mode: ' . (string) ($result['diff']['mode'] ?? 'working_tree'));
        $this->line('Domains: ' . implode(', ', (array) ($result['scope']['domains'] ?? [])));
        $this->line('Target Scope: ' . (string) (($result['scope']['input'] ?? null) ?: 'database/seeders/V3 + database/seeders/Page_V3'));
        $this->line('Execution: ' . ((bool) ($result['execution']['dry_run'] ?? false) ? 'dry-run' : 'live'));
        $this->line('Force: ' . (((bool) ($result['execution']['force'] ?? false)) ? 'true' : 'false'));
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
            $this->line('[OK] Dry-run changed-content preflight passed: scope is clean for live execution.');
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
            $this->line('Stopped On Domain: ' . (string) ($result['error']['domain'] ?? 'unknown'));
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

        if (! empty($result['operation_run']['id'])) {
            $this->newLine();
            $this->line('Run ID: ' . (string) $result['operation_run']['id']);
            $this->line('History Payload: ' . (string) (($result['operation_run']['payload_json_path'] ?? null) ?: '—'));
        }

        if (! empty($result['operation_run']['warning'])) {
            $this->newLine();
            $this->warn((string) $result['operation_run']['warning']);
        }

        if (is_array($result['lock'] ?? null)) {
            $this->newLine();
            $this->line('Lock: status=' . (string) ($result['lock']['status'] ?? 'unknown')
                . '; acquired=' . (((bool) ($result['lock']['acquired'] ?? false)) ? 'true' : 'false')
                . '; takeover=' . (((bool) data_get($result, 'lock.takeover.performed', false)) ? 'performed' : 'not-performed'));
        }

        if ((bool) ($result['execution']['dry_run'] ?? false) && empty($result['error'])) {
            $this->newLine();
            $this->line('Live Apply Hint:');
            $this->line($this->liveApplyHint($result));
        }
    }

    /**
     * @param  list<array<string, mixed>>  $packages
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
            $domain = (string) ($package['domain'] ?? 'unknown');

            $line = match ($status) {
                'ok' => $phase === 'cleanup_deleted'
                    ? sprintf('[OK] Cleaned up deleted package [%s]: %s', $domain, $path)
                    : sprintf('[OK] %s package [%s]: %s', $action === 'refresh' ? 'Refreshed' : 'Seeded', $domain, $path),
                'failed' => sprintf('[FAIL] %s [%s]: %s', $phase === 'cleanup_deleted' ? 'Cleanup failed' : 'Upsert failed', $domain, $path),
                'pending' => sprintf('[PENDING] Not executed after earlier failure [%s]: %s', $domain, $path),
                default => sprintf('[SKIP] [%s] %s', $domain, $path),
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
     * @param  array<string, mixed>  $result
     */
    private function liveApplyHint(array $result): string
    {
        $parts = ['php artisan content:apply-changed'];
        $target = $result['scope']['input'] ?? null;

        if (is_string($target) && $target !== '') {
            $parts[] = $this->shellEscapeArgument($target);
        }

        $domains = (array) ($result['scope']['domains'] ?? []);

        if ($domains !== ['v3', 'page-v3']) {
            $parts[] = '--domains=' . implode(',', $domains);
        }

        $parts = array_merge($parts, $this->diffModeParts($result));
        $parts[] = '--force';

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
     * @return list<string>
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
                'input' => $target !== '' ? $target : null,
                'domains' => [],
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => (bool) $this->option('with-release-check'),
                'skip_release_check' => (bool) $this->option('skip-release-check'),
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
                'strict' => (bool) $this->option('strict'),
            ],
            'plan' => [
                'summary' => [
                    'changed_packages' => 0,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                    'deleted_cleanup_candidates' => 0,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'domains' => [
                    'v3' => null,
                    'page-v3' => null,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'error' => null,
            ],
            'preflight' => [
                'executed' => false,
                'summary' => [
                    'candidates' => 0,
                    'ok' => 0,
                    'warn' => 0,
                    'fail' => 0,
                    'skipped' => 0,
                ],
                'packages' => [],
            ],
            'execution' => [
                'dry_run' => (bool) $this->option('dry-run'),
                'force' => (bool) $this->option('force'),
                'fail_fast' => true,
                'scope_transactional' => false,
                'cleanup_deleted' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
                'upsert_present' => [
                    'started' => 0,
                    'completed' => 0,
                    'succeeded' => 0,
                    'failed' => 0,
                    'stopped_on' => null,
                    'packages' => [],
                ],
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
     * @param  array<string, mixed>  $result
     */
    private function exitCodeForResult(array $result): int
    {
        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
    }

    private function shellEscapeArgument(string $value): string
    {
        $escaped = str_replace('"', '\"', $value);

        return '"' . $escaped . '"';
    }

    /**
     * @return array<string, mixed>
     */
    private function operationRunContext(string $target): array
    {
        return [
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => $this->option('domains'),
            'base_ref' => (string) ($this->option('base') ?? ''),
            'head_ref' => (string) ($this->option('head') ?? ''),
            'dry_run' => (bool) $this->option('dry-run'),
            'strict' => (bool) $this->option('strict'),
            'with_release_check' => (bool) $this->option('with-release-check'),
            'skip_release_check' => (bool) $this->option('skip-release-check'),
            'bootstrap_uninitialized' => false,
            'operator_user_id' => null,
            'meta' => [
                'target' => $target !== '' ? $target : null,
                'staged' => (bool) $this->option('staged'),
                'working_tree' => (bool) $this->option('working-tree'),
                'include_untracked' => (bool) $this->option('include-untracked'),
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
            ],
        ];
    }

    private function recordOperationRun(mixed $run, array $result): mixed
    {
        $status = $this->historyStatusForResult($result);

        return match ($status) {
            'dry_run' => $this->contentOperationRunService->finishDryRun($run, $result),
            'blocked' => $this->contentOperationRunService->finishBlocked($run, $result),
            'partial' => $this->contentOperationRunService->finishPartial($run, $result),
            'success' => $this->contentOperationRunService->finishSuccess($run, $result),
            default => $this->contentOperationRunService->finishFailure($run, $result['error'] ?? [], $result),
        };
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function historyStatusForResult(array $result): string
    {
        if ((bool) ($result['execution']['dry_run'] ?? false) && empty($result['error'])) {
            return 'dry_run';
        }

        if (empty($result['error'])) {
            return 'success';
        }

        $reason = (string) ($result['error']['reason'] ?? '');

        if (in_array($reason, [
            'preflight_failed',
            'blocked_packages',
            'warnings_are_fatal',
            'force_required',
            'active_lock_present',
            'stale_lock_takeover_required',
            'stale_lock_present',
        ], true)) {
            return 'blocked';
        }

        $cleanupSucceeded = (int) ($result['execution']['cleanup_deleted']['succeeded'] ?? 0);
        $upsertSucceeded = (int) ($result['execution']['upsert_present']['succeeded'] ?? 0);

        if ($cleanupSucceeded > 0 || $upsertSucceeded > 0) {
            return 'partial';
        }

        return 'failed';
    }

    /**
     * @return array<string, mixed>
     */
    private function lockContext(mixed $run, string $target): array
    {
        return [
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => $this->option('domains'),
            'content_operation_run_id' => $run?->id,
            'operator_user_id' => null,
            'meta' => [
                'target' => $target !== '' ? $target : null,
                'dry_run' => (bool) $this->option('dry-run'),
                'base_ref' => (string) ($this->option('base') ?? ''),
                'head_ref' => (string) ($this->option('head') ?? ''),
                'staged' => (bool) $this->option('staged'),
                'working_tree' => (bool) $this->option('working-tree'),
            ],
        ];
    }

    private function heartbeatLock(mixed $ownerToken): null
    {
        $this->contentOperationLockService->heartbeat(is_string($ownerToken) ? $ownerToken : null);

        return null;
    }

    /**
     * @param  array<string, mixed>  $lease
     * @return array<string, mixed>
     */
    private function lockBlockedResult(string $target, array $lease): array
    {
        $message = (string) data_get($lease, 'error.message', 'Content operation is blocked by the global content-operation lock.');
        $result = $this->errorResult($target, new RuntimeException($message));
        $result['lock'] = $this->lockPayload($lease);
        $result['error'] = [
            'stage' => 'content_operation_lock',
            'reason' => (string) data_get($lease, 'error.reason', 'active_lock_present'),
            'message' => $message,
            'lock' => $lease['lock'] ?? null,
        ];

        return $result;
    }

    /**
     * @param  array<string, mixed>  $lease
     * @return array<string, mixed>
     */
    private function lockPayload(array $lease): array
    {
        return [
            'acquired' => (bool) ($lease['acquired'] ?? false),
            'status' => (string) ($lease['status'] ?? 'unknown'),
            'owner_token' => $lease['owner_token'] ?? null,
            'operation_kind' => 'apply_changed',
            'trigger_source' => 'cli',
            'domains' => (array) ($lease['lock']['domains'] ?? []),
            'content_operation_run_id' => $lease['lock']['content_operation_run_id'] ?? null,
            'acquired_at' => $lease['lock']['acquired_at'] ?? null,
            'heartbeat_at' => $lease['lock']['heartbeat_at'] ?? null,
            'expires_at' => $lease['lock']['expires_at'] ?? null,
            'warnings' => array_values((array) ($lease['warnings'] ?? [])),
            'takeover' => (array) ($lease['takeover'] ?? ['requested' => false, 'performed' => false]),
            'lock' => $lease['lock'] ?? null,
        ];
    }
}
