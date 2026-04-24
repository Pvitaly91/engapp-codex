<?php

namespace App\Modules\GitDeployment\Services;

use App\Services\ContentDeployment\ChangedContentApplyService;
use App\Services\ContentDeployment\ContentOperationLockService;
use App\Services\ContentDeployment\ContentOperationRunService;
use App\Services\ContentDeployment\ContentSyncStateService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ChangedContentDeploymentApplyService
{
    public function __construct(
        private readonly ChangedContentDeploymentPreviewService $changedContentDeploymentPreviewService,
        private readonly ChangedContentApplyService $changedContentApplyService,
        private readonly ?ContentSyncStateService $contentSyncStateService = null,
        private readonly ?ContentOperationRunService $contentOperationRunService = null,
        private readonly ?ContentOperationLockService $contentOperationLockService = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(array $context, array $options = []): array
    {
        $normalizedOptions = $this->normalizeOptions($options);
        $preview = $this->changedContentDeploymentPreviewService->preview(
            $context,
            $this->previewOptions($normalizedOptions)
        );

        return $this->runFromPreview($preview, $normalizedOptions);
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function runFromPreview(array $preview, array $options = []): array
    {
        $normalizedOptions = $this->normalizeOptions($options);
        $run = $this->startOperationRun($preview, $normalizedOptions);
        $result = $this->resultTemplate($preview, $normalizedOptions);

        $baseRef = trim((string) data_get($preview, 'deployment.base_ref', ''));
        $headRef = trim((string) data_get($preview, 'deployment.head_ref', ''));
        $baseRefsByDomain = $this->baseRefsByDomainFromPreview($preview);
        $previewError = is_array($preview['error'] ?? null) ? $preview['error'] : null;

        if ($previewError !== null || $baseRef === '' || $headRef === '') {
            $result['status'] = 'preview_failed';
            $result['error'] = $previewError ?? [
                'stage' => 'deployment_preview',
                'message' => 'Deployment content apply requires resolved base/head refs from the deployment preview.',
            ];

            return $this->finalizeAndRecord($result, $normalizedOptions, $run);
        }

        $lockLease = is_array($normalizedOptions['content_lock_lease'])
            ? $normalizedOptions['content_lock_lease']
            : $this->lockService()->acquire(
                $this->lockContext($preview, $normalizedOptions, $run),
                (bool) $normalizedOptions['takeover_stale_lock']
            );

        if (is_array($normalizedOptions['content_lock_lease']) && $run?->id !== null) {
            $this->lockService()->attachRun($lockLease['owner_token'] ?? null, $run->id);
            $lockLease['lock'] = $this->lockService()->snapshot();
        }

        $result['lock'] = $this->lockPayload($lockLease, $preview);

        if (! (bool) ($lockLease['acquired'] ?? false)) {
            $result['status'] = 'content_apply_blocked';
            $result['error'] = [
                'stage' => 'content_operation_lock',
                'reason' => (string) data_get($lockLease, 'error.reason', 'active_lock_present'),
                'message' => (string) data_get($lockLease, 'error.message', 'Deployment content apply is blocked by the global content-operation lock.'),
                'lock' => $lockLease['lock'] ?? null,
            ];

            return $this->finalizeAndRecord($result, $normalizedOptions, $run);
        }

        try {
            $ownerToken = $lockLease['owner_token'] ?? null;
            $applyResult = $this->changedContentApplyService->run(null, [
                'domains' => ['v3', 'page-v3'],
                'base' => $baseRef,
                'base_refs_by_domain' => $baseRefsByDomain !== [] ? $baseRefsByDomain : null,
                'head' => $headRef,
                'dry_run' => (bool) $normalizedOptions['dry_run'],
                'force' => ! (bool) $normalizedOptions['dry_run'],
                'with_release_check' => (bool) $normalizedOptions['with_release_check'],
                'skip_release_check' => (bool) $normalizedOptions['skip_release_check'],
                'check_profile' => (string) $normalizedOptions['check_profile'],
                'strict' => (bool) $normalizedOptions['strict'],
                'heartbeat' => fn (): null => $this->heartbeatLock($ownerToken),
            ]);
        } catch (Throwable $exception) {
            $applyResult = $this->applyErrorResult($normalizedOptions, $baseRef, $headRef, $exception);
        } finally {
            if ((bool) $normalizedOptions['release_content_lock']) {
                $this->lockService()->release($lockLease['owner_token'] ?? null);
            }
        }

        $result['deployment']['content_apply_executed'] = ! (bool) $normalizedOptions['dry_run'];
        $result['content_apply']['executed'] = true;
        $result['content_apply']['result'] = $applyResult;
        $result['status'] = empty($applyResult['error'])
            ? ((bool) $normalizedOptions['dry_run'] ? 'ready' : 'completed')
            : 'content_apply_failed';
        $result['error'] = is_array($applyResult['error'] ?? null) ? $applyResult['error'] : null;
        $result['content_sync']['after'] = $this->resolvedAfterSyncState($preview, $headRef);
        $result['content_sync']['advanced_domains'] = $this->advancedDomains(
            (array) ($result['content_sync']['before']['domains'] ?? []),
            (array) ($result['content_sync']['after']['domains'] ?? [])
        );

        return $this->finalizeAndRecord($result, $normalizedOptions, $run);
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function deploymentFailedResult(array $preview, string $message, array $options = []): array
    {
        $normalizedOptions = $this->normalizeOptions($options);
        $run = $this->startOperationRun($preview, $normalizedOptions);
        $result = $this->resultTemplate($preview, $normalizedOptions);
        $result['status'] = 'deploy_failed';
        $result['error'] = [
            'stage' => 'deploy',
            'message' => $message,
        ];

        if (is_array($normalizedOptions['content_lock_lease'])) {
            if ($run?->id !== null) {
                $this->lockService()->attachRun($normalizedOptions['content_lock_lease']['owner_token'] ?? null, $run->id);
                $normalizedOptions['content_lock_lease']['lock'] = $this->lockService()->snapshot();
            }

            $result['lock'] = $this->lockPayload($normalizedOptions['content_lock_lease'], $preview);
        }

        return $this->finalizeAndRecord($result, $normalizedOptions, $run);
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $lockReservation
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function lockBlockedResult(array $preview, array $lockReservation, array $options = []): array
    {
        $normalizedOptions = $this->normalizeOptions($options);
        $run = $this->startOperationRun($preview, $normalizedOptions);
        $result = $this->resultTemplate($preview, $normalizedOptions);
        $lease = is_array($lockReservation['lease'] ?? null) ? $lockReservation['lease'] : $lockReservation;

        $result['status'] = 'content_apply_blocked';
        $result['lock'] = $this->lockPayload($lease, $preview);
        $result['error'] = [
            'stage' => 'content_operation_lock',
            'reason' => (string) data_get($lease, 'error.reason', 'active_lock_present'),
            'message' => (string) data_get($lease, 'error.message', 'Deployment content apply is blocked by the global content-operation lock before code update.'),
            'lock' => $lease['lock'] ?? $lockReservation['snapshot'] ?? null,
        ];

        return $this->finalizeAndRecord($result, $normalizedOptions, $run);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $mode = Str::slug((string) data_get($result, 'deployment.mode', 'standard'));
        $sourceKind = Str::slug((string) data_get($result, 'deployment.source_kind', 'deploy'));
        $runType = (bool) data_get($result, 'content_apply.dry_run', false) ? 'dry-run' : 'live';
        $hash = substr(sha1(json_encode([
            data_get($result, 'deployment.base_ref'),
            data_get($result, 'deployment.head_ref'),
            data_get($result, 'deployment.content_apply_requested'),
            data_get($result, 'content_apply.dry_run'),
            data_get($result, 'status'),
        ])), 0, 8);
        $fileName = $mode . '-' . $sourceKind . '-content-apply-' . $runType . '-' . $hash . '.md';
        $relativePath = 'deployment-content-apply-reports/' . $fileName;

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        $checkProfile = strtolower(trim((string) ($options['check_profile']
            ?? config('git-deployment.content_apply.check_profile', 'release'))));

        if (! in_array($checkProfile, ['scaffold', 'release'], true)) {
            throw new RuntimeException('Unsupported deployment content-apply check profile. Use scaffold or release.');
        }

        return [
            'requested' => array_key_exists('requested', $options)
                ? (bool) $options['requested']
                : true,
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'with_release_check' => array_key_exists('with_release_check', $options)
                ? (bool) $options['with_release_check']
                : (bool) config('git-deployment.content_apply.with_release_check', true),
            'skip_release_check' => array_key_exists('skip_release_check', $options)
                ? (bool) $options['skip_release_check']
                : (bool) config('git-deployment.content_apply.skip_release_check', false),
            'check_profile' => $checkProfile,
            'strict' => array_key_exists('strict', $options)
                ? (bool) $options['strict']
                : (bool) config('git-deployment.content_apply.strict', true),
            'write_report' => array_key_exists('write_report', $options)
                ? (bool) $options['write_report']
                : true,
            'trigger_source' => (string) ($options['trigger_source'] ?? ''),
            'operator_user_id' => $options['operator_user_id'] ?? null,
            'takeover_stale_lock' => (bool) ($options['takeover_stale_lock'] ?? false),
            'content_lock_lease' => is_array($options['content_lock_lease'] ?? null)
                ? $options['content_lock_lease']
                : null,
            'release_content_lock' => array_key_exists('release_content_lock', $options)
                ? (bool) $options['release_content_lock']
                : ! is_array($options['content_lock_lease'] ?? null),
            'meta' => is_array($options['meta'] ?? null) ? $options['meta'] : [],
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function previewOptions(array $options): array
    {
        return [
            'with_release_check' => (bool) $options['with_release_check'],
            'check_profile' => (string) $options['check_profile'],
            'strict' => (bool) $options['strict'],
        ];
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function resultTemplate(array $preview, array $options): array
    {
        return [
            'deployment' => [
                'mode' => (string) data_get($preview, 'deployment.mode', 'standard'),
                'source_kind' => (string) data_get($preview, 'deployment.source_kind', 'deploy'),
                'base_ref' => data_get($preview, 'deployment.base_ref'),
                'head_ref' => data_get($preview, 'deployment.head_ref'),
                'branch' => data_get($preview, 'deployment.branch'),
                'commit' => data_get($preview, 'deployment.commit'),
                'content_apply_requested' => (bool) $options['requested'],
                'content_apply_executed' => false,
            ],
            'preview' => $preview,
            'content_apply' => [
                'executed' => false,
                'dry_run' => (bool) $options['dry_run'],
                'result' => null,
            ],
            'content_sync' => [
                'before' => is_array($preview['content_sync'] ?? null)
                    ? $preview['content_sync']
                    : ['domains' => []],
                'after' => null,
                'advanced_domains' => [],
            ],
            'gate' => [
                'strict' => (bool) data_get($preview, 'gate.strict', $options['strict']),
                'blocked' => (bool) data_get($preview, 'gate.blocked', false),
                'reasons' => array_values((array) data_get($preview, 'gate.reasons', [])),
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'status' => 'ready',
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function finalize(array $result, array $options): array
    {
        if ((bool) $options['write_report']) {
            $relativePath = $this->writeReport($result);
            $result['artifacts']['report_path'] = storage_path('app/' . $relativePath);
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     */
    private function startOperationRun(array $preview, array $options): mixed
    {
        if ($this->contentOperationRunService === null) {
            return null;
        }

        try {
            return $this->contentOperationRunService->start([
                'operation_kind' => 'deployment_apply_changed',
                'trigger_source' => $this->triggerSource($preview, $options),
                'domains' => data_get($preview, 'deployment.scope.domains', ['v3', 'page-v3']),
                'base_ref' => data_get($preview, 'deployment.base_ref'),
                'head_ref' => data_get($preview, 'deployment.head_ref'),
                'base_refs_by_domain' => $this->baseRefsByDomainFromPreview($preview),
                'dry_run' => (bool) ($options['dry_run'] ?? false),
                'strict' => (bool) ($options['strict'] ?? false),
                'with_release_check' => $options['with_release_check'] ?? null,
                'skip_release_check' => $options['skip_release_check'] ?? null,
                'bootstrap_uninitialized' => false,
                'operator_user_id' => $options['operator_user_id'] ?? null,
                'meta' => array_merge((array) ($options['meta'] ?? []), [
                    'deployment_mode' => data_get($preview, 'deployment.mode'),
                    'source_kind' => data_get($preview, 'deployment.source_kind'),
                    'branch' => data_get($preview, 'deployment.branch'),
                    'commit' => data_get($preview, 'deployment.commit'),
                ]),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     */
    private function finalizeAndRecord(array $result, array $options, mixed $run): array
    {
        $result = $this->finalize($result, $options);

        if ($run === null || $this->contentOperationRunService === null) {
            return $result;
        }

        try {
            $recordedRun = match ($this->historyStatusForResult($result)) {
                'dry_run' => $this->contentOperationRunService->finishDryRun($run, $result),
                'blocked' => $this->contentOperationRunService->finishBlocked($run, $result),
                'partial' => $this->contentOperationRunService->finishPartial($run, $result),
                'success' => $this->contentOperationRunService->finishSuccess($run, $result),
                default => $this->contentOperationRunService->finishFailure($run, $result['error'] ?? [], $result),
            };

            $result['operation_run'] = [
                'id' => $recordedRun->id,
                'status' => $recordedRun->status,
                'payload_json_path' => $recordedRun->payload_json_path ? storage_path('app/' . $recordedRun->payload_json_path) : null,
                'report_path' => $recordedRun->report_path ? storage_path('app/' . $recordedRun->report_path) : null,
            ];
        } catch (Throwable $exception) {
            report($exception);
            $result['operation_run']['warning'] = 'Content operation history could not be finalized: ' . $exception->getMessage();
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     */
    private function triggerSource(array $preview, array $options): string
    {
        $explicit = trim((string) ($options['trigger_source'] ?? ''));

        if ($explicit !== '') {
            return $explicit;
        }

        return (string) data_get($preview, 'deployment.mode') === 'native'
            ? 'native_deployment_ui'
            : 'deployment_ui';
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function historyStatusForResult(array $result): string
    {
        return match ((string) ($result['status'] ?? 'failed')) {
            'ready' => (bool) data_get($result, 'content_apply.dry_run', false) ? 'dry_run' : 'success',
            'completed' => 'success',
            'preview_failed' => 'blocked',
            'deploy_failed' => 'failed',
            'content_apply_failed' => $this->canonicalApplyPartial($result) ? 'partial' : 'failed',
            'content_apply_blocked' => 'blocked',
            default => 'failed',
        };
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function canonicalApplyPartial(array $result): bool
    {
        return (int) data_get($result, 'content_apply.result.execution.cleanup_deleted.succeeded', 0) > 0
            || (int) data_get($result, 'content_apply.result.execution.upsert_present.succeeded', 0) > 0;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function applyErrorResult(array $options, string $baseRef, string $headRef, Throwable $exception): array
    {
        return [
            'diff' => [
                'mode' => 'refs',
                'base' => $baseRef,
                'head' => $headRef,
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => null,
                'domains' => ['v3', 'page-v3'],
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => (bool) $options['with_release_check'],
                'skip_release_check' => (bool) $options['skip_release_check'],
                'check_profile' => (string) $options['check_profile'],
                'strict' => (bool) $options['strict'],
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
                'dry_run' => (bool) $options['dry_run'],
                'force' => ! (bool) $options['dry_run'],
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
                'stage' => 'deployment_content_apply',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $applyResult = (array) data_get($result, 'content_apply.result', []);
        $lines = [
            '# Deployment Content Apply',
            '',
            '- Mode: `' . (string) data_get($result, 'deployment.mode', 'standard') . '`',
            '- Source: `' . (string) data_get($result, 'deployment.source_kind', 'deploy') . '`',
            '- Base: `' . (string) (data_get($result, 'deployment.base_ref') ?? '') . '`',
            '- Head: `' . (string) (data_get($result, 'deployment.head_ref') ?? '') . '`',
            '- Requested: `' . (data_get($result, 'deployment.content_apply_requested', false) ? 'true' : 'false') . '`',
            '- Live Executed: `' . (data_get($result, 'deployment.content_apply_executed', false) ? 'true' : 'false') . '`',
            '- Dry Run: `' . (data_get($result, 'content_apply.dry_run', false) ? 'true' : 'false') . '`',
            '- Status: `' . (string) data_get($result, 'status', 'ready') . '`',
            '',
            '## Content Sync',
            '',
        ];

        foreach ((array) data_get($result, 'content_sync.before.domains', []) as $domain => $before) {
            $lines[] = '- [' . $domain . '] before status=`' . (string) ($before['status'] ?? 'uninitialized') . '`'
                . ' effective_base=`' . (string) (($before['effective_base_ref'] ?? null) ?: '') . '`'
                . ' target=`' . (string) (($before['target_head_ref'] ?? null) ?: '') . '`';
        }

        foreach ((array) data_get($result, 'content_sync.after.domains', []) as $domain => $after) {
            $lines[] = '- [' . $domain . '] after status=`' . (string) ($after['status'] ?? 'uninitialized') . '`'
                . ' sync_ref=`' . (string) (($after['sync_state_ref'] ?? null) ?: '') . '`';
        }

        $lines = array_merge($lines, [
            '',
            '## Gate',
            '',
            '- Blocked: `' . (data_get($result, 'gate.blocked', false) ? 'true' : 'false') . '`',
            '- Strict: `' . (data_get($result, 'gate.strict', false) ? 'true' : 'false') . '`',
        ]);

        $gateReasons = array_values(array_filter((array) data_get($result, 'gate.reasons', [])));

        if ($gateReasons !== []) {
            foreach ($gateReasons as $reason) {
                $lines[] = '- Reason: ' . $reason;
            }
        }

        $lines[] = '';
        $lines[] = '## Changed Content Apply';
        $lines[] = '';
        $lines[] = '- Changed Packages: ' . (int) data_get($applyResult, 'plan.summary.changed_packages', 0);
        $lines[] = '- Deleted Cleanup Candidates: ' . (int) data_get($applyResult, 'plan.summary.deleted_cleanup_candidates', 0);
        $lines[] = '- Seed Candidates: ' . (int) data_get($applyResult, 'plan.summary.seed_candidates', 0);
        $lines[] = '- Refresh Candidates: ' . (int) data_get($applyResult, 'plan.summary.refresh_candidates', 0);
        $lines[] = '- Preflight OK: ' . (int) data_get($applyResult, 'preflight.summary.ok', 0);
        $lines[] = '- Preflight Warn: ' . (int) data_get($applyResult, 'preflight.summary.warn', 0);
        $lines[] = '- Preflight Fail: ' . (int) data_get($applyResult, 'preflight.summary.fail', 0);
        $lines[] = '';
        $lines[] = '## Cleanup Deleted Phase';
        $lines[] = '';

        $cleanupPackages = (array) data_get($applyResult, 'execution.cleanup_deleted.packages', []);

        if ($cleanupPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($cleanupPackages as $package) {
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `'
                    . (string) ($package['relative_path'] ?? $package['package_key'] ?? '')
                    . '` | status=`' . (string) ($package['status'] ?? 'pending') . '`';
            }
        }

        $lines[] = '';
        $lines[] = '## Upsert Present Phase';
        $lines[] = '';

        $upsertPackages = (array) data_get($applyResult, 'execution.upsert_present.packages', []);

        if ($upsertPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($upsertPackages as $package) {
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `'
                    . (string) ($package['relative_path'] ?? $package['package_key'] ?? '')
                    . '` | action=`' . (string) ($package['action'] ?? 'skip') . '`'
                    . ' | status=`' . (string) ($package['status'] ?? 'pending') . '`';
            }
        }

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) data_get($result, 'error.stage', 'deployment_content_apply') . '`';
            $lines[] = '- Message: ' . (string) data_get($result, 'error.message', 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return array<string, string|null>
     */
    private function baseRefsByDomainFromPreview(array $preview): array
    {
        $baseRefs = [];

        foreach ((array) data_get($preview, 'content_sync.domains', []) as $domain => $domainSync) {
            $baseRefs[$domain] = $domainSync['effective_base_ref'] ?? null;
        }

        return array_filter($baseRefs, static fn ($ref): bool => trim((string) $ref) !== '');
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return array<string, mixed>|null
     */
    private function resolvedAfterSyncState(array $preview, string $headRef): ?array
    {
        if ($this->contentSyncStateService === null) {
            return data_get($preview, 'content_sync');
        }

        $domains = array_keys((array) data_get($preview, 'content_sync.domains', []));

        if ($domains === []) {
            $domains = ['v3', 'page-v3'];
        }

        $fallbackRefs = array_fill_keys($domains, $headRef);

        return [
            'domains' => $this->contentSyncStateService->describe($domains, $fallbackRefs, $headRef),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $before
     * @param  array<string, array<string, mixed>>  $after
     * @return list<string>
     */
    private function advancedDomains(array $before, array $after): array
    {
        $advanced = [];

        foreach ($after as $domain => $afterDomain) {
            $beforeRef = strtolower(trim((string) ($before[$domain]['sync_state_ref'] ?? '')));
            $afterRef = strtolower(trim((string) ($afterDomain['sync_state_ref'] ?? '')));

            if ($afterRef !== '' && $beforeRef !== $afterRef) {
                $advanced[] = $domain;
            }
        }

        return $advanced;
    }

    private function lockService(): ContentOperationLockService
    {
        return $this->contentOperationLockService ?? app(ContentOperationLockService::class);
    }

    /**
     * @param  array<string, mixed>  $preview
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function lockContext(array $preview, array $options, mixed $run): array
    {
        return [
            'operation_kind' => 'deployment_apply_changed',
            'trigger_source' => $this->triggerSource($preview, $options),
            'domains' => data_get($preview, 'deployment.scope.domains', ['v3', 'page-v3']),
            'content_operation_run_id' => $run?->id,
            'operator_user_id' => $options['operator_user_id'] ?? null,
            'meta' => [
                'deployment_mode' => data_get($preview, 'deployment.mode'),
                'source_kind' => data_get($preview, 'deployment.source_kind'),
                'base_ref' => data_get($preview, 'deployment.base_ref'),
                'head_ref' => data_get($preview, 'deployment.head_ref'),
                'dry_run' => (bool) ($options['dry_run'] ?? false),
            ],
        ];
    }

    private function heartbeatLock(mixed $ownerToken): null
    {
        $this->lockService()->heartbeat(is_string($ownerToken) ? $ownerToken : null);

        return null;
    }

    /**
     * @param  array<string, mixed>  $lease
     * @param  array<string, mixed>  $preview
     * @return array<string, mixed>
     */
    private function lockPayload(array $lease, array $preview): array
    {
        return [
            'acquired' => (bool) ($lease['acquired'] ?? false),
            'status' => (string) ($lease['status'] ?? 'unknown'),
            'owner_token' => $lease['owner_token'] ?? null,
            'operation_kind' => 'deployment_apply_changed',
            'trigger_source' => (string) ($lease['lock']['trigger_source'] ?? data_get($preview, 'deployment.mode', 'deployment_ui')),
            'domains' => (array) ($lease['lock']['domains'] ?? data_get($preview, 'deployment.scope.domains', ['v3', 'page-v3'])),
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
