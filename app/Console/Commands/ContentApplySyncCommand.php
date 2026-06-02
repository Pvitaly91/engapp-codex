<?php

namespace App\Console\Commands;

use App\Services\ContentDeployment\ContentOperationRunService;
use App\Services\ContentDeployment\ContentOperationLockService;
use App\Services\ContentDeployment\ContentSyncApplyService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class ContentApplySyncCommand extends Command
{
    protected $signature = 'content:apply-sync
        {--domains= : v3,page-v3 | v3 | page-v3}
        {--dry-run : Execute full sync-repair preflight without live writes}
        {--force : Allow live sync repair against the currently deployed code ref}
        {--json : Output the content sync repair result as JSON}
        {--write-report : Write a Markdown report into storage/app/content-sync-apply-reports}
        {--with-release-check : Add release-check summaries for initialized drifted domains}
        {--skip-release-check : Skip release-check execution during seed/refresh phases}
        {--check-profile=release : scaffold | release}
        {--strict : Treat planner warnings and unresolved bootstrap-required states as failures}
        {--bootstrap-uninitialized : Explicitly record current deployed ref as synced for uninitialized domains}
        {--takeover-stale-lock : Explicitly take over a stale global content-operation lock}';

    protected $description = 'Repair content drift from persisted per-domain sync refs to the currently deployed code ref.';

    public function __construct(
        private readonly ContentSyncApplyService $contentSyncApplyService,
        private readonly ContentOperationRunService $contentOperationRunService,
        private readonly ContentOperationLockService $contentOperationLockService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $run = null;
        $historyWarning = null;

        try {
            $run = $this->contentOperationRunService->start($this->operationRunContext());
        } catch (Throwable $exception) {
            report($exception);
            $historyWarning = 'Content operation history could not be initialized: ' . $exception->getMessage();
        }

        $lockLease = $this->contentOperationLockService->acquire(
            $this->lockContext($run),
            (bool) $this->option('takeover-stale-lock')
        );

        if (! (bool) ($lockLease['acquired'] ?? false)) {
            $result = $this->lockBlockedResult($lockLease);
        } else {
            try {
                $ownerToken = $lockLease['owner_token'] ?? null;
                $result = $this->contentSyncApplyService->run([
                    'domains' => $this->option('domains'),
                    'dry_run' => (bool) $this->option('dry-run'),
                    'force' => (bool) $this->option('force'),
                    'with_release_check' => (bool) $this->option('with-release-check'),
                    'skip_release_check' => (bool) $this->option('skip-release-check'),
                    'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
                    'strict' => (bool) $this->option('strict'),
                    'bootstrap_uninitialized' => (bool) $this->option('bootstrap-uninitialized'),
                    'heartbeat' => fn (): null => $this->heartbeatLock($ownerToken),
                ]);
            } catch (Throwable $exception) {
                $result = $this->errorResult($exception);
            } finally {
                $this->contentOperationLockService->release($lockLease['owner_token'] ?? null);
            }

            $result['lock'] = $this->lockPayload($lockLease);
        }

        if ($this->option('write-report')) {
            $path = $this->contentSyncApplyService->writeReport($result);
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

            return empty($result['error']) ? self::SUCCESS : self::FAILURE;
        }

        $this->renderHumanOutput($result);

        return empty($result['error']) ? self::SUCCESS : self::FAILURE;
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

        $this->line('Command: content:apply-sync');
        $this->line('Current Deployed Ref: ' . (string) (($result['plan']['deployment_refs']['current_deployed_ref'] ?? null) ?: 'unavailable'));
        $this->line('Execution: ' . (((bool) ($result['apply']['dry_run'] ?? false)) ? 'dry-run' : 'live'));
        $this->line('Force: ' . (((bool) $this->option('force')) ? 'true' : 'false'));
        $this->line('Bootstrap Requested: ' . (((bool) ($result['apply']['bootstrap']['requested'] ?? false)) ? 'true' : 'false'));
        $this->newLine();

        $this->line('Domains Before');
        $this->renderDomainLines((array) ($result['domains_before'] ?? []));
        $this->newLine();

        if (is_array($result['apply']['changed_content_result'] ?? null)) {
            $changed = (array) $result['apply']['changed_content_result'];
            $this->line(sprintf(
                'Canonical Apply Summary: changed=%d; deleted-cleanup=%d; seed=%d; refresh=%d',
                (int) ($changed['plan']['summary']['changed_packages'] ?? 0),
                (int) ($changed['plan']['summary']['deleted_cleanup_candidates'] ?? 0),
                (int) ($changed['plan']['summary']['seed_candidates'] ?? 0),
                (int) ($changed['plan']['summary']['refresh_candidates'] ?? 0)
            ));
            $this->line(sprintf(
                'Canonical Preflight: ok=%d; warn=%d; fail=%d',
                (int) ($changed['preflight']['summary']['ok'] ?? 0),
                (int) ($changed['preflight']['summary']['warn'] ?? 0),
                (int) ($changed['preflight']['summary']['fail'] ?? 0)
            ));
            $this->newLine();
        }

        $simulated = (array) ($result['apply']['bootstrap']['simulated'] ?? []);
        $applied = (array) ($result['apply']['bootstrap']['applied'] ?? []);

        if ($simulated !== []) {
            foreach ($simulated as $entry) {
                $this->line(sprintf(
                    '[SIMULATE] Bootstrap uninitialized domain [%s] to %s',
                    (string) ($entry['domain'] ?? 'unknown'),
                    (string) (($entry['head_ref'] ?? null) ?: '—')
                ));
            }
            $this->newLine();
        }

        if ($applied !== []) {
            foreach ($applied as $entry) {
                $this->line(sprintf(
                    '[OK] Bootstrapped uninitialized domain [%s] to %s',
                    (string) ($entry['domain'] ?? 'unknown'),
                    (string) (($entry['head_ref'] ?? null) ?: '—')
                ));
            }
            $this->newLine();
        }

        $this->line('Domains After');
        $this->renderDomainLines((array) ($result['domains_after'] ?? []));

        if ((bool) ($result['apply']['dry_run'] ?? false) && empty($result['error'])) {
            $this->newLine();
            $this->line('Live Repair Hint:');
            $this->line($this->liveHint($result));
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
    }

    /**
     * @param  array<string, array<string, mixed>>  $domains
     */
    private function renderDomainLines(array $domains): void
    {
        if ($domains === []) {
            $this->line('- None.');

            return;
        }

        foreach ($domains as $domain => $state) {
            $state = (array) $state;
            $this->line(sprintf(
                '[%s] status=%s | sync_ref=%s | current_deployed_ref=%s | bootstrap_required=%s | last_attempt_status=%s',
                $domain,
                (string) ($state['status'] ?? 'uninitialized'),
                (string) (($state['sync_state_ref'] ?? null) ?: '—'),
                (string) (($state['current_deployed_ref'] ?? null) ?: '—'),
                ((bool) ($state['bootstrap_required'] ?? false)) ? 'true' : 'false',
                (string) (($state['last_attempted_status'] ?? null) ?: '—')
            ));
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function liveHint(array $result): string
    {
        $parts = ['php artisan content:apply-sync', '--force'];
        $domains = array_keys((array) ($result['domains_before'] ?? []));

        if ($domains !== ['v3', 'page-v3']) {
            $parts[] = '--domains=' . implode(',', $domains);
        }

        if ((bool) ($result['apply']['bootstrap']['requested'] ?? false)) {
            $parts[] = '--bootstrap-uninitialized';
        }

        if ((bool) ($result['plan']['options']['with_release_check'] ?? false)) {
            $parts[] = '--with-release-check';
        }

        if ((bool) ($this->option('skip-release-check'))) {
            $parts[] = '--skip-release-check';
        }

        if (($result['plan']['options']['check_profile'] ?? 'release') !== 'release') {
            $parts[] = '--check-profile=' . (string) $result['plan']['options']['check_profile'];
        }

        if ((bool) ($result['plan']['options']['strict'] ?? false)) {
            $parts[] = '--strict';
        }

        return implode(' ', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    private function errorResult(Throwable $exception): array
    {
        return [
            'domains_before' => [],
            'plan' => [
                'deployment_refs' => [
                    'current_deployed_ref' => null,
                ],
            ],
            'apply' => [
                'executed' => false,
                'dry_run' => (bool) $this->option('dry-run'),
                'changed_content_result' => null,
                'bootstrap' => [
                    'requested' => (bool) $this->option('bootstrap-uninitialized'),
                    'simulated' => [],
                    'applied' => [],
                ],
            ],
            'domains_after' => [],
            'status' => 'failed',
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'sync_apply',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function operationRunContext(): array
    {
        return [
            'operation_kind' => 'apply_sync',
            'trigger_source' => 'cli',
            'domains' => $this->option('domains'),
            'dry_run' => (bool) $this->option('dry-run'),
            'strict' => (bool) $this->option('strict'),
            'with_release_check' => (bool) $this->option('with-release-check'),
            'skip_release_check' => (bool) $this->option('skip-release-check'),
            'bootstrap_uninitialized' => (bool) $this->option('bootstrap-uninitialized'),
            'operator_user_id' => null,
            'meta' => [
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
            ],
        ];
    }

    private function recordOperationRun(mixed $run, array $result): mixed
    {
        $status = match ((string) ($result['status'] ?? 'failed')) {
            'dry_run' => 'dry_run',
            'completed' => 'success',
            'partial' => 'partial',
            'blocked' => 'blocked',
            default => 'failed',
        };

        return match ($status) {
            'dry_run' => $this->contentOperationRunService->finishDryRun($run, $result),
            'blocked' => $this->contentOperationRunService->finishBlocked($run, $result),
            'partial' => $this->contentOperationRunService->finishPartial($run, $result),
            'success' => $this->contentOperationRunService->finishSuccess($run, $result),
            default => $this->contentOperationRunService->finishFailure($run, $result['error'] ?? [], $result),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function lockContext(mixed $run): array
    {
        return [
            'operation_kind' => 'apply_sync',
            'trigger_source' => 'cli',
            'domains' => $this->option('domains'),
            'content_operation_run_id' => $run?->id,
            'operator_user_id' => null,
            'meta' => [
                'dry_run' => (bool) $this->option('dry-run'),
                'bootstrap_uninitialized' => (bool) $this->option('bootstrap-uninitialized'),
                'check_profile' => (string) ($this->option('check-profile') ?? 'release'),
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
    private function lockBlockedResult(array $lease): array
    {
        $message = (string) data_get($lease, 'error.message', 'Content sync repair is blocked by the global content-operation lock.');
        $result = $this->errorResult(new RuntimeException($message));
        $result['lock'] = $this->lockPayload($lease);
        $result['status'] = 'blocked';
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
            'operation_kind' => 'apply_sync',
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
