<?php

namespace App\Services\ContentDeployment;

use App\Models\ContentOperationRun;
use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class ContentOperationReplayService
{
    public function __construct(
        private readonly ContentOperationRunService $contentOperationRunService,
        private readonly ChangedContentApplyService $changedContentApplyService,
        private readonly ContentSyncApplyService $contentSyncApplyService,
        private readonly ContentSyncStateService $contentSyncStateService,
        private readonly DeploymentGitRefProbe $deploymentGitRefProbe,
        private readonly ?ContentOperationLockService $contentOperationLockService = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(int|string|ContentOperationRun $run, array $options = []): array
    {
        $originalRun = $this->resolveRun($run);
        $normalizedOptions = $this->normalizeOptions($options);
        $result = $this->resultTemplate($originalRun, $normalizedOptions);
        $recordedRun = null;
        $historyWarning = null;
        $artifactMissing = $originalRun->payload_json_path !== null && ! is_array($originalRun->payload_json ?? null);
        $resolvedContext = [
            'report_requested' => (bool) ($normalizedOptions['write_report'] ?? false),
        ];
        $preValidationEntries = $this->preValidationEntries($originalRun, $normalizedOptions);

        if ($preValidationEntries !== []) {
            $result['replay']['validation']['blocked'] = true;
            $result['replay']['validation']['reasons'] = $preValidationEntries;
        } else {
            try {
                $resolvedContext = $this->resolveContext($originalRun, $normalizedOptions);
                $result['replay']['resolved_context'] = $resolvedContext;
                $validation = $this->validateContext($originalRun, $resolvedContext, $normalizedOptions);
                $result['replay']['validation'] = [
                    'blocked' => (bool) ($validation['blocked'] ?? false),
                    'warnings' => array_values((array) ($validation['warnings'] ?? [])),
                    'reasons' => array_values((array) ($validation['reasons'] ?? [])),
                    'current_deployed_ref' => $validation['current_deployed_ref'] ?? null,
                ];
            } catch (Throwable $exception) {
                $result['replay']['validation']['blocked'] = true;
                $result['replay']['validation']['reasons'][] = $this->validationEntry(
                    $artifactMissing ? 'artifact_missing' : 'replay_context_unavailable',
                    $exception->getMessage()
                );
            }
        }

        try {
            $recordedRun = $this->contentOperationRunService->start([
                'replayed_from_run_id' => $originalRun->id,
                'operation_kind' => $originalRun->operation_kind,
                'trigger_source' => $this->nullableString($normalizedOptions['trigger_source'] ?? null)
                    ?? 'cli',
                'domains' => (array) ($resolvedContext['domains'] ?? $originalRun->domains ?? []),
                'base_ref' => $resolvedContext['base_ref'] ?? $originalRun->base_ref,
                'head_ref' => $resolvedContext['head_ref'] ?? $originalRun->head_ref,
                'base_refs_by_domain' => $resolvedContext['base_refs_by_domain'] ?? $originalRun->base_refs_by_domain,
                'dry_run' => (bool) ($resolvedContext['dry_run'] ?? true),
                'strict' => (bool) ($resolvedContext['strict'] ?? false),
                'with_release_check' => $resolvedContext['with_release_check'] ?? $originalRun->with_release_check,
                'skip_release_check' => $resolvedContext['skip_release_check'] ?? $originalRun->skip_release_check,
                'bootstrap_uninitialized' => (bool) ($resolvedContext['bootstrap_uninitialized'] ?? $originalRun->bootstrap_uninitialized),
                'operator_user_id' => $normalizedOptions['operator_user_id'] ?? null,
                'meta' => [
                    'replay' => true,
                    'original_run_id' => $originalRun->id,
                    'original_status' => $originalRun->status,
                    'original_trigger_source' => $originalRun->trigger_source,
                    'stale_context_warnings' => array_values((array) $result['replay']['validation']['warnings']),
                    'validation_reasons' => array_values((array) $result['replay']['validation']['reasons']),
                ],
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $historyWarning = 'Content operation history could not be initialized: ' . $exception->getMessage();
        }

        if ((bool) ($result['replay']['validation']['blocked'] ?? false)) {
            $result['replay']['result'] = null;
            $result['replay']['status'] = 'blocked';

            return $this->finalize($result, $resolvedContext, $recordedRun, $historyWarning, [
                'blocked_count' => count((array) ($result['replay']['validation']['reasons'] ?? [])),
                'warning_count' => count((array) ($result['replay']['validation']['warnings'] ?? [])),
            ]);
        }

        $lockLease = $this->acquireLock($resolvedContext, $recordedRun, $normalizedOptions);
        $result['lock'] = $this->lockPayload($lockLease, $resolvedContext);

        if (! (bool) ($lockLease['acquired'] ?? false)) {
            $result['replay']['validation']['blocked'] = true;
            $result['replay']['validation']['reasons'][] = $this->validationEntry(
                (string) data_get($lockLease, 'error.reason', 'active_lock_present'),
                (string) data_get($lockLease, 'error.message', 'Replay is blocked by the global content-operation lock.'),
                ['lock' => $lockLease['lock'] ?? null]
            );
            $result['replay']['result'] = null;
            $result['replay']['status'] = 'blocked';
            $result['error'] = [
                'stage' => 'content_operation_lock',
                'reason' => (string) data_get($lockLease, 'error.reason', 'active_lock_present'),
                'message' => (string) data_get($lockLease, 'error.message', 'Replay is blocked by the global content-operation lock.'),
                'lock' => $lockLease['lock'] ?? null,
            ];

            return $this->finalize($result, $resolvedContext, $recordedRun, $historyWarning, [
                'blocked_count' => count((array) ($result['replay']['validation']['reasons'] ?? [])),
                'warning_count' => count((array) ($result['replay']['validation']['warnings'] ?? [])),
            ]);
        }

        try {
            $ownerToken = $lockLease['owner_token'] ?? null;
            $serviceResult = $this->executeReplay($resolvedContext + [
                'heartbeat' => fn (): null => $this->heartbeatLock($ownerToken),
            ]);
            $result['replay']['result'] = $serviceResult;
            $result['replay']['status'] = $this->resultStatusForContext($resolvedContext, $serviceResult);
        } catch (Throwable $exception) {
            $result['replay']['result'] = $this->errorResultForContext($resolvedContext, $exception);
            $result['replay']['status'] = 'failed';
        } finally {
            $this->contentOperationLockService?->release($lockLease['owner_token'] ?? null);
        }

        return $this->finalize($result, $resolvedContext, $recordedRun, $historyWarning, [
            'warning_count' => count((array) ($result['replay']['validation']['warnings'] ?? [])),
        ]);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $originalId = (int) data_get($result, 'original_run.id', 0);
        $runType = (bool) data_get($result, 'replay.mode.dry_run', true) ? 'dry-run' : 'live';
        $hash = substr(sha1(json_encode([
            $originalId,
            data_get($result, 'replay.resolved_context.base_ref'),
            data_get($result, 'replay.resolved_context.base_refs_by_domain'),
            data_get($result, 'replay.resolved_context.head_ref'),
            data_get($result, 'replay.status'),
        ])), 0, 8);
        $relativePath = 'content-operation-replays/' . now()->format('Y/m/d')
            . '/replay-' . $originalId . '-' . $runType . '-' . $hash . '.md';

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    private function resolveRun(int|string|ContentOperationRun $run): ContentOperationRun
    {
        if ($run instanceof ContentOperationRun) {
            $resolved = $this->contentOperationRunService->findWithArtifacts((int) $run->id);
        } else {
            $resolved = $this->contentOperationRunService->findWithArtifacts((int) $run);
        }

        if ($resolved === null) {
            throw new RuntimeException('Content operation run not found.');
        }

        return $resolved;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        return [
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'force' => (bool) ($options['force'] ?? false),
            'json' => (bool) ($options['json'] ?? false),
            'write_report' => (bool) ($options['write_report'] ?? false),
            'strict' => (bool) ($options['strict'] ?? false),
            'allow_success' => (bool) ($options['allow_success'] ?? false),
            'reuse_original_mode' => (bool) ($options['reuse_original_mode'] ?? false),
            'takeover_stale_lock' => (bool) ($options['takeover_stale_lock'] ?? false),
            'trigger_source' => $options['trigger_source'] ?? null,
            'operator_user_id' => $options['operator_user_id'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function resultTemplate(ContentOperationRun $originalRun, array $options): array
    {
        return [
            'original_run' => [
                'id' => $originalRun->id,
                'replayed_from_run_id' => $originalRun->replayed_from_run_id,
                'operation_kind' => $originalRun->operation_kind,
                'status' => $originalRun->status,
                'trigger_source' => $originalRun->trigger_source,
                'domains' => is_array($originalRun->domains) ? $originalRun->domains : [],
                'base_ref' => $originalRun->base_ref,
                'head_ref' => $originalRun->head_ref,
                'base_refs_by_domain' => is_array($originalRun->base_refs_by_domain) ? $originalRun->base_refs_by_domain : [],
                'dry_run' => (bool) $originalRun->dry_run,
                'strict' => (bool) $originalRun->strict,
                'with_release_check' => $originalRun->with_release_check,
                'skip_release_check' => $originalRun->skip_release_check,
                'bootstrap_uninitialized' => (bool) $originalRun->bootstrap_uninitialized,
                'payload_json_path' => $originalRun->payload_json_absolute_path ?? ($originalRun->payload_json_path ? storage_path('app/' . $originalRun->payload_json_path) : null),
                'report_path' => $originalRun->report_absolute_path ?? ($originalRun->report_path ? storage_path('app/' . $originalRun->report_path) : null),
                'summary' => is_array($originalRun->summary) ? $originalRun->summary : [],
            ],
            'replay' => [
                'mode' => [
                    'dry_run' => true,
                    'force' => (bool) $options['force'],
                    'allow_success' => (bool) $options['allow_success'],
                    'reuse_original_mode' => (bool) $options['reuse_original_mode'],
                    'takeover_stale_lock' => (bool) $options['takeover_stale_lock'],
                ],
                'validation' => [
                    'blocked' => false,
                    'warnings' => [],
                    'reasons' => [],
                ],
                'resolved_context' => [],
                'result' => null,
                'new_run' => null,
                'status' => 'ready',
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function resolveContext(ContentOperationRun $originalRun, array $options): array
    {
        $payload = is_array($originalRun->payload_json ?? null) ? $originalRun->payload_json : null;
        $artifactMissing = $originalRun->payload_json_path !== null && $payload === null;
        $operationKind = trim((string) $originalRun->operation_kind);

        if (! in_array($operationKind, ['apply_changed', 'apply_sync', 'deployment_apply_changed', 'deployment_sync_repair'], true)) {
            throw new RuntimeException('Unsupported operation kind for replay.');
        }

        $originalDryRun = (bool) $originalRun->dry_run;
        $replayDryRun = (bool) $options['reuse_original_mode']
            ? $originalDryRun
            : ! (bool) $options['force'];

        $checkProfile = $this->checkProfileFrom($originalRun, $payload);
        $domains = $this->domainsFrom($originalRun, $payload);
        $strict = (bool) $originalRun->strict || (bool) $options['strict'];

        if ($domains === []) {
            throw new RuntimeException('Replay context does not include any domains.');
        }

        if (in_array($operationKind, ['apply_changed', 'deployment_apply_changed'], true)) {
            $baseRefsByDomain = $this->baseRefsByDomainFrom($originalRun, $payload);
            $baseRef = $this->nullableString(
                $originalRun->base_ref
                    ?? data_get($payload, 'diff.base')
                    ?? data_get($payload, 'deployment.base_ref')
                    ?? data_get($payload, 'original_run.base_ref')
            );
            $headRef = $this->nullableString(
                $originalRun->head_ref
                    ?? data_get($payload, 'diff.head')
                    ?? data_get($payload, 'deployment.head_ref')
                    ?? data_get($payload, 'original_run.head_ref')
            );

            if ($headRef === null) {
                throw new RuntimeException($artifactMissing
                    ? 'Replay requires a canonical payload artifact or persisted head ref.'
                    : 'Replay context is missing the recorded head ref.');
            }

            if ($baseRef === null && $baseRefsByDomain === []) {
                throw new RuntimeException($artifactMissing
                    ? 'Replay requires a canonical payload artifact or persisted base refs.'
                    : 'Replay context is missing the recorded base ref.');
            }

            return [
                'operation_kind' => $operationKind,
                'domains' => $domains,
                'base_ref' => $baseRef,
                'head_ref' => $headRef,
                'base_refs_by_domain' => $baseRefsByDomain,
                'with_release_check' => (bool) ($originalRun->with_release_check ?? false),
                'skip_release_check' => (bool) ($originalRun->skip_release_check ?? false),
                'check_profile' => $checkProfile,
                'strict' => $strict,
                'bootstrap_uninitialized' => false,
                'dry_run' => $replayDryRun,
                'force' => ! $replayDryRun,
                'report_requested' => (bool) ($options['write_report'] ?? false),
            ];
        }

        $headRef = $this->nullableString(
            $originalRun->head_ref
                ?? data_get($payload, 'plan.deployment_refs.current_deployed_ref')
                ?? data_get($payload, 'deployment.head_ref')
                ?? data_get($payload, 'original_run.head_ref')
        );

        if ($headRef === null) {
            throw new RuntimeException($artifactMissing
                ? 'Replay requires a canonical payload artifact or persisted deployed head ref.'
                : 'Replay context is missing the recorded deployed head ref.');
        }

        return [
            'operation_kind' => $operationKind,
            'domains' => $domains,
            'base_ref' => $this->nullableString($originalRun->base_ref),
            'head_ref' => $headRef,
            'base_refs_by_domain' => $this->baseRefsByDomainFrom($originalRun, $payload),
            'with_release_check' => (bool) ($originalRun->with_release_check ?? false),
            'skip_release_check' => (bool) ($originalRun->skip_release_check ?? false),
            'check_profile' => $checkProfile,
            'strict' => $strict,
            'bootstrap_uninitialized' => (bool) $originalRun->bootstrap_uninitialized,
            'dry_run' => $replayDryRun,
            'force' => ! $replayDryRun,
            'report_requested' => (bool) ($options['write_report'] ?? false),
        ];
    }

    /**
     * @param  array<string, mixed>  $resolvedContext
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function validateContext(ContentOperationRun $originalRun, array $resolvedContext, array $options): array
    {
        $warnings = [];
        $reasons = [];
        $operationKind = (string) ($resolvedContext['operation_kind'] ?? '');
        $headRef = $this->nullableString($resolvedContext['head_ref'] ?? null);
        $baseRef = $this->nullableString($resolvedContext['base_ref'] ?? null);
        $baseRefsByDomain = (array) ($resolvedContext['base_refs_by_domain'] ?? []);

        if (! in_array($operationKind, ['apply_changed', 'apply_sync', 'deployment_apply_changed', 'deployment_sync_repair'], true)) {
            $reasons[] = $this->validationEntry('unsupported_operation_kind', 'This content operation kind cannot be replayed.');
        }

        if ($originalRun->payload_json_path !== null && ! is_array($originalRun->payload_json ?? null)) {
            $warnings[] = $this->validationEntry(
                'artifact_missing',
                'The canonical payload artifact is missing or unreadable; replay is relying on persisted run metadata only.'
            );
        }

        if ($headRef !== null && ! $this->refExists($headRef)) {
            $reasons[] = $this->validationEntry('recorded_head_ref_missing', 'The recorded head ref is no longer available in local git objects.');
        }

        if (in_array($operationKind, ['apply_changed', 'deployment_apply_changed'], true) && $baseRef !== null && ! $this->refExists($baseRef)) {
            $reasons[] = $this->validationEntry('recorded_base_ref_missing', 'The recorded base ref is no longer available in local git objects.');
        }

        foreach ($baseRefsByDomain as $domain => $domainBaseRef) {
            $domainBaseRef = $this->nullableString($domainBaseRef);

            if ($domainBaseRef !== null && ! $this->refExists($domainBaseRef)) {
                $entry = $this->validationEntry(
                    'recorded_base_ref_missing',
                    'One recorded domain base ref is no longer available in local git objects.',
                    ['domain' => (string) $domain, 'base_ref' => $domainBaseRef]
                );

                if (in_array($operationKind, ['apply_changed', 'deployment_apply_changed'], true)) {
                    $reasons[] = $entry;
                } else {
                    $warnings[] = $entry;
                }
            }
        }

        $currentDeployedRef = in_array($operationKind, ['apply_sync', 'deployment_apply_changed', 'deployment_sync_repair'], true)
            ? $this->deploymentGitRefProbe->currentHeadCommit()
            : null;

        if (in_array($operationKind, ['apply_sync', 'deployment_apply_changed', 'deployment_sync_repair'], true)
            && $currentDeployedRef !== null
            && $headRef !== null
            && strtolower($currentDeployedRef) !== strtolower($headRef)) {
            $reasons[] = $this->validationEntry(
                'current_deployed_ref_changed_since_original_run',
                'The current deployed ref changed since the original run was recorded.',
                ['current_deployed_ref' => $currentDeployedRef, 'recorded_head_ref' => $headRef]
            );
        }

        $currentSyncStates = $this->contentSyncStateService->getMany((array) ($resolvedContext['domains'] ?? []));

        foreach ((array) ($resolvedContext['domains'] ?? []) as $domain) {
            $currentSyncRef = $this->nullableString($currentSyncStates[$domain]?->last_successful_ref);
            $recordedDomainBase = $this->nullableString($baseRefsByDomain[$domain] ?? $baseRef);

            if ($currentSyncRef !== null
                && $recordedDomainBase !== null
                && strtolower($currentSyncRef) !== strtolower($recordedDomainBase)) {
                $warnings[] = $this->validationEntry(
                    'content_sync_state_advanced_since_original_run',
                    'The content sync cursor for this domain changed since the original run.',
                    [
                        'domain' => (string) $domain,
                        'current_sync_ref' => $currentSyncRef,
                        'recorded_base_ref' => $recordedDomainBase,
                    ]
                );
            }
        }

        if ((bool) $options['strict'] || (bool) ($resolvedContext['strict'] ?? false)) {
            foreach ($warnings as $warning) {
                $reasons[] = $this->validationEntry(
                    (string) ($warning['code'] ?? 'warnings_are_fatal'),
                    (string) ($warning['message'] ?? 'Replay validation warning became fatal because strict mode is enabled.'),
                    (array) ($warning['context'] ?? [])
                );
            }
        }

        return [
            'blocked' => $reasons !== [],
            'warnings' => array_values($warnings),
            'reasons' => array_values($reasons),
            'current_deployed_ref' => $currentDeployedRef,
        ];
    }

    /**
     * @param  array<string, mixed>  $resolvedContext
     * @return array<string, mixed>
     */
    private function executeReplay(array $resolvedContext): array
    {
        return match ((string) ($resolvedContext['operation_kind'] ?? '')) {
            'apply_changed', 'deployment_apply_changed' => $this->changedContentApplyService->run(null, [
                'domains' => $resolvedContext['domains'],
                'base' => $resolvedContext['base_ref'] ?? '',
                'base_refs_by_domain' => $resolvedContext['base_refs_by_domain'] ?? [],
                'head' => $resolvedContext['head_ref'] ?? '',
                'dry_run' => (bool) ($resolvedContext['dry_run'] ?? true),
                'force' => (bool) ($resolvedContext['force'] ?? false),
                'with_release_check' => (bool) ($resolvedContext['with_release_check'] ?? false),
                'skip_release_check' => (bool) ($resolvedContext['skip_release_check'] ?? false),
                'check_profile' => (string) ($resolvedContext['check_profile'] ?? 'release'),
                'strict' => (bool) ($resolvedContext['strict'] ?? false),
                'heartbeat' => $resolvedContext['heartbeat'] ?? null,
            ]),
            'apply_sync', 'deployment_sync_repair' => $this->contentSyncApplyService->run([
                'domains' => $resolvedContext['domains'],
                'base_refs_by_domain' => $resolvedContext['base_refs_by_domain'] ?? [],
                'head_ref' => $resolvedContext['head_ref'] ?? '',
                'dry_run' => (bool) ($resolvedContext['dry_run'] ?? true),
                'force' => (bool) ($resolvedContext['force'] ?? false),
                'with_release_check' => (bool) ($resolvedContext['with_release_check'] ?? false),
                'skip_release_check' => (bool) ($resolvedContext['skip_release_check'] ?? false),
                'check_profile' => (string) ($resolvedContext['check_profile'] ?? 'release'),
                'strict' => (bool) ($resolvedContext['strict'] ?? false),
                'bootstrap_uninitialized' => (bool) ($resolvedContext['bootstrap_uninitialized'] ?? false),
                'heartbeat' => $resolvedContext['heartbeat'] ?? null,
            ]),
            default => throw new RuntimeException('Unsupported operation kind for replay execution.'),
        };
    }

    /**
     * @param  array<string, mixed>  $resolvedContext
     * @param  array<string, mixed>|null  $recordedRun
     * @param  array<string, mixed>  $summary
     * @return array<string, mixed>
     */
    private function finalize(
        array $result,
        array $resolvedContext,
        mixed $recordedRun,
        ?string $historyWarning,
        array $summary = []
    ): array {
        if (data_get($result, 'replay.status') !== 'blocked' && (bool) data_get($result, 'replay.mode.dry_run', true) === false) {
            $result['replay']['mode']['force'] = true;
        }

        if (! empty($historyWarning)) {
            $result['replay']['new_run']['warning'] = $historyWarning;
        }

        if (($result['error'] ?? null) === null && ($result['replay']['result'] ?? null) === null && data_get($result, 'replay.status') === 'blocked') {
            $result['error'] = [
                'stage' => 'replay_validation',
                'message' => 'Replay was blocked before canonical execution started.',
            ];
        } elseif (is_array(data_get($result, 'replay.result.error'))) {
            $result['error'] = data_get($result, 'replay.result.error');
        }

        if (data_get($result, 'replay.mode.dry_run')) {
            $result['replay']['mode']['dry_run'] = true;
        }

        if (($resolvedContext['dry_run'] ?? true) === true) {
            $result['replay']['mode']['dry_run'] = true;
        } else {
            $result['replay']['mode']['dry_run'] = false;
        }

        if (($summary['warning_count'] ?? null) === null) {
            $summary['warning_count'] = count((array) data_get($result, 'replay.validation.warnings', []));
        }

        if (($summary['blocked_count'] ?? null) === null) {
            $summary['blocked_count'] = count((array) data_get($result, 'replay.validation.reasons', []));
        }

        if (data_get($result, 'replay.result') !== null) {
            $summary = array_merge($summary, [
                'changed_packages' => (int) data_get($result, 'replay.result.plan.summary.changed_packages', 0),
                'deleted_cleanup_candidates' => (int) data_get($result, 'replay.result.plan.summary.deleted_cleanup_candidates', 0),
                'seed_candidates' => (int) data_get($result, 'replay.result.plan.summary.seed_candidates', 0),
                'refresh_candidates' => (int) data_get($result, 'replay.result.plan.summary.refresh_candidates', 0),
                'stopped_phase' => data_get($result, 'replay.result.error.phase'),
                'stopped_package_key' => data_get($result, 'replay.result.error.package'),
                'bootstrap_domains' => (array) data_get($result, 'replay.result.apply.bootstrap.applied', []),
                'sync_state_advanced_domains' => (array) data_get($result, 'replay.result.content_sync.advanced_domains', []),
            ]);
        }

        if (($result['artifacts']['report_path'] ?? null) === null && ($resolvedContext['report_requested'] ?? false)) {
            $relativePath = $this->writeReport($result);
            $result['artifacts']['report_path'] = storage_path('app/' . $relativePath);
        }

        if ($recordedRun !== null) {
            try {
                $finalRun = match ((string) ($result['replay']['status'] ?? 'failed')) {
                    'dry_run' => $this->contentOperationRunService->finishDryRun($recordedRun, $result, $summary),
                    'blocked' => $this->contentOperationRunService->finishBlocked($recordedRun, $result, $summary),
                    'partial' => $this->contentOperationRunService->finishPartial($recordedRun, $result, $summary),
                    'success' => $this->contentOperationRunService->finishSuccess($recordedRun, $result, $summary),
                    default => $this->contentOperationRunService->finishFailure($recordedRun, $result['error'] ?? [], $result, $summary),
                };

                $result['replay']['new_run'] = [
                    'id' => $finalRun->id,
                    'status' => $finalRun->status,
                    'payload_json_path' => $finalRun->payload_json_path ? storage_path('app/' . $finalRun->payload_json_path) : null,
                    'report_path' => $finalRun->report_path ? storage_path('app/' . $finalRun->report_path) : null,
                ] + (is_array($result['replay']['new_run'] ?? null) ? $result['replay']['new_run'] : []);
            } catch (Throwable $exception) {
                report($exception);
                $result['replay']['new_run']['warning'] = 'Content operation history could not be finalized: ' . $exception->getMessage();
            }
        }

        $result['status'] = $result['replay']['status'] ?? 'failed';

        return $result;
    }

    /**
     * @param  array<string, mixed>  $resolvedContext
     * @return array<string, mixed>
     */
    private function errorResultForContext(array $resolvedContext, Throwable $exception): array
    {
        if (in_array((string) ($resolvedContext['operation_kind'] ?? ''), ['apply_sync', 'deployment_sync_repair'], true)) {
            return [
                'domains_before' => [],
                'plan' => [
                    'deployment_refs' => [
                        'current_deployed_ref' => $resolvedContext['head_ref'] ?? null,
                    ],
                ],
                'apply' => [
                    'executed' => false,
                    'dry_run' => (bool) ($resolvedContext['dry_run'] ?? true),
                    'changed_content_result' => null,
                    'bootstrap' => [
                        'requested' => (bool) ($resolvedContext['bootstrap_uninitialized'] ?? false),
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
                    'stage' => 'replay_execution',
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ],
            ];
        }

        return [
            'diff' => [
                'mode' => 'refs',
                'base' => $resolvedContext['base_ref'] ?? null,
                'base_refs_by_domain' => (array) ($resolvedContext['base_refs_by_domain'] ?? []),
                'head' => $resolvedContext['head_ref'] ?? null,
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => null,
                'domains' => (array) ($resolvedContext['domains'] ?? []),
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => (bool) ($resolvedContext['with_release_check'] ?? false),
                'skip_release_check' => (bool) ($resolvedContext['skip_release_check'] ?? false),
                'check_profile' => (string) ($resolvedContext['check_profile'] ?? 'release'),
                'strict' => (bool) ($resolvedContext['strict'] ?? false),
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
                'dry_run' => (bool) ($resolvedContext['dry_run'] ?? true),
                'force' => (bool) ($resolvedContext['force'] ?? false),
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
                'stage' => 'replay_execution',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $resolvedContext
     * @param  array<string, mixed>  $serviceResult
     */
    private function resultStatusForContext(array $resolvedContext, array $serviceResult): string
    {
        if (in_array((string) ($resolvedContext['operation_kind'] ?? ''), ['apply_sync', 'deployment_sync_repair'], true)) {
            return match ((string) ($serviceResult['status'] ?? 'failed')) {
                'dry_run' => 'dry_run',
                'completed' => 'success',
                'partial' => 'partial',
                'blocked' => 'blocked',
                default => 'failed',
            };
        }

        if ((bool) ($resolvedContext['dry_run'] ?? true) && empty($serviceResult['error'])) {
            return 'dry_run';
        }

        if (empty($serviceResult['error'])) {
            return 'success';
        }

        $reason = (string) ($serviceResult['error']['reason'] ?? '');

        if (in_array($reason, ['preflight_failed', 'blocked_packages', 'warnings_are_fatal', 'force_required'], true)) {
            return 'blocked';
        }

        $cleanupSucceeded = (int) ($serviceResult['execution']['cleanup_deleted']['succeeded'] ?? 0);
        $upsertSucceeded = (int) ($serviceResult['execution']['upsert_present']['succeeded'] ?? 0);

        return ($cleanupSucceeded > 0 || $upsertSucceeded > 0) ? 'partial' : 'failed';
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return list<string>
     */
    private function domainsFrom(ContentOperationRun $run, ?array $payload): array
    {
        $domains = is_array($run->domains) ? $run->domains : [];

        if ($domains !== []) {
            return array_values(array_filter(array_map([$this, 'nullableString'], $domains)));
        }

        $fromPayload = data_get($payload, 'scope.domains')
            ?? data_get($payload, 'plan.options.domains')
            ?? data_get($payload, 'original_run.domains')
            ?? array_keys((array) data_get($payload, 'domains_before', data_get($payload, 'content_sync.before.domains', [])));

        if (! is_array($fromPayload)) {
            return [];
        }

        return array_values(array_filter(array_map([$this, 'nullableString'], array_is_list($fromPayload) ? $fromPayload : array_keys($fromPayload))));
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, string|null>
     */
    private function baseRefsByDomainFrom(ContentOperationRun $run, ?array $payload): array
    {
        $refs = is_array($run->base_refs_by_domain) ? $run->base_refs_by_domain : [];

        if ($refs === [] && is_array($payload)) {
            $refs = (array) (
                data_get($payload, 'diff.base_refs_by_domain')
                ?? data_get($payload, 'original_run.base_refs_by_domain')
                ?? data_get($payload, 'replay.resolved_context.base_refs_by_domain')
            );

            if ($refs === []) {
                foreach ((array) data_get($payload, 'domains_before', data_get($payload, 'content_sync.before.domains', [])) as $domain => $state) {
                    $refs[(string) $domain] = data_get($state, 'effective_base_ref', data_get($state, 'sync_state_ref'));
                }
            }
        }

        $normalized = [];

        foreach ($refs as $domain => $ref) {
            $domain = $this->nullableString((string) $domain);

            if ($domain === null) {
                continue;
            }

            $normalized[$domain] = $this->nullableString($ref);
        }

        return $normalized;
    }

    private function checkProfileFrom(ContentOperationRun $run, ?array $payload): string
    {
        $profile = $this->nullableString(
            data_get($run->meta, 'check_profile')
                ?? data_get($payload, 'scope.check_profile')
                ?? data_get($payload, 'plan.options.check_profile')
                ?? data_get($payload, 'preview.deployment.check_profile')
        );

        return in_array($profile, ['scaffold', 'release'], true) ? $profile : 'release';
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function validationEntry(string $code, string $message, array $context = []): array
    {
        return [
            'code' => $code,
            'message' => $message,
            'context' => $context !== [] ? $context : null,
        ];
    }

    private function refExists(?string $ref): bool
    {
        $normalizedRef = $this->nullableString($ref);

        if ($normalizedRef === null) {
            return false;
        }

        return $this->deploymentGitRefProbe->resolveCommit($normalizedRef) !== null;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return list<array<string, mixed>>
     */
    private function preValidationEntries(ContentOperationRun $originalRun, array $options): array
    {
        $entries = [];
        $operationKind = trim((string) $originalRun->operation_kind);

        if (! in_array($operationKind, ['apply_changed', 'apply_sync', 'deployment_apply_changed', 'deployment_sync_repair'], true)) {
            $entries[] = $this->validationEntry(
                'unsupported_operation_kind',
                'This content operation kind cannot be replayed.'
            );
        }

        if ((string) $originalRun->status === 'success' && ! (bool) ($options['allow_success'] ?? false)) {
            $entries[] = $this->validationEntry(
                'successful_original_run_requires_allow_success',
                'Replaying a successful run requires --allow-success.'
            );
        }

        if ((bool) ($options['reuse_original_mode'] ?? false) && ! (bool) $originalRun->dry_run && ! (bool) ($options['force'] ?? false)) {
            $entries[] = $this->validationEntry(
                'reuse_original_live_mode_requires_force',
                'Replaying an originally live run with --reuse-original-mode still requires --force.'
            );
        }

        return $entries;
    }

    private function nullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param  array<string, mixed>  $resolvedContext
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function acquireLock(array $resolvedContext, mixed $recordedRun, array $options): array
    {
        if ($this->contentOperationLockService === null) {
            return [
                'acquired' => true,
                'status' => 'disabled',
                'owner_token' => null,
                'lock' => ['exists' => false, 'status' => 'disabled'],
                'warnings' => ['content_operation_lock_unavailable'],
                'takeover' => [
                    'requested' => (bool) ($options['takeover_stale_lock'] ?? false),
                    'performed' => false,
                ],
                'error' => null,
            ];
        }

        return $this->contentOperationLockService->acquire([
            'operation_kind' => (string) ($resolvedContext['operation_kind'] ?? 'replay'),
            'trigger_source' => (string) ($options['trigger_source'] ?? 'cli'),
            'domains' => (array) ($resolvedContext['domains'] ?? []),
            'content_operation_run_id' => $recordedRun?->id,
            'operator_user_id' => $options['operator_user_id'] ?? null,
            'meta' => [
                'replay' => true,
                'original_run_id' => data_get($recordedRun, 'replayed_from_run_id'),
                'dry_run' => (bool) ($resolvedContext['dry_run'] ?? true),
                'head_ref' => $resolvedContext['head_ref'] ?? null,
            ],
        ], (bool) ($options['takeover_stale_lock'] ?? false));
    }

    private function heartbeatLock(mixed $ownerToken): null
    {
        $this->contentOperationLockService?->heartbeat(is_string($ownerToken) ? $ownerToken : null);

        return null;
    }

    /**
     * @param  array<string, mixed>  $lease
     * @param  array<string, mixed>  $resolvedContext
     * @return array<string, mixed>
     */
    private function lockPayload(array $lease, array $resolvedContext): array
    {
        return [
            'acquired' => (bool) ($lease['acquired'] ?? false),
            'status' => (string) ($lease['status'] ?? 'unknown'),
            'owner_token' => $lease['owner_token'] ?? null,
            'operation_kind' => (string) ($resolvedContext['operation_kind'] ?? 'replay'),
            'trigger_source' => (string) ($lease['lock']['trigger_source'] ?? 'cli'),
            'domains' => (array) ($lease['lock']['domains'] ?? ($resolvedContext['domains'] ?? [])),
            'content_operation_run_id' => $lease['lock']['content_operation_run_id'] ?? null,
            'acquired_at' => $lease['lock']['acquired_at'] ?? null,
            'heartbeat_at' => $lease['lock']['heartbeat_at'] ?? null,
            'expires_at' => $lease['lock']['expires_at'] ?? null,
            'warnings' => array_values((array) ($lease['warnings'] ?? [])),
            'takeover' => (array) ($lease['takeover'] ?? ['requested' => false, 'performed' => false]),
            'lock' => $lease['lock'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# Content Operation Replay',
            '',
            '- Original Run ID: ' . (int) data_get($result, 'original_run.id', 0),
            '- Original Kind: `' . (string) data_get($result, 'original_run.operation_kind', 'unknown') . '`',
            '- Original Status: `' . (string) data_get($result, 'original_run.status', 'unknown') . '`',
            '- Replay Dry Run: `' . ((bool) data_get($result, 'replay.mode.dry_run', true) ? 'true' : 'false') . '`',
            '- Replay Force: `' . ((bool) data_get($result, 'replay.mode.force', false) ? 'true' : 'false') . '`',
            '- Replay Status: `' . (string) data_get($result, 'replay.status', 'unknown') . '`',
            '',
            '## Validation',
            '',
            '- Blocked: `' . ((bool) data_get($result, 'replay.validation.blocked', false) ? 'true' : 'false') . '`',
            '- Warnings: `' . json_encode((array) data_get($result, 'replay.validation.warnings', []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`',
            '- Reasons: `' . json_encode((array) data_get($result, 'replay.validation.reasons', []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`',
            '',
            '## Resolved Context',
            '',
            '- Domains: `' . implode(',', (array) data_get($result, 'replay.resolved_context.domains', [])) . '`',
            '- Base Ref: `' . (string) (data_get($result, 'replay.resolved_context.base_ref') ?? '') . '`',
            '- Base Refs By Domain: `' . json_encode((array) data_get($result, 'replay.resolved_context.base_refs_by_domain', []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`',
            '- Head Ref: `' . (string) (data_get($result, 'replay.resolved_context.head_ref') ?? '') . '`',
            '- Bootstrap Uninitialized: `' . ((bool) data_get($result, 'replay.resolved_context.bootstrap_uninitialized', false) ? 'true' : 'false') . '`',
            '',
            '## New Run',
            '',
            '- Run ID: `' . (string) (data_get($result, 'replay.new_run.id') ?? '') . '`',
            '- Status: `' . (string) (data_get($result, 'replay.new_run.status') ?? '') . '`',
            '- Payload Path: `' . (string) (data_get($result, 'replay.new_run.payload_json_path') ?? '') . '`',
            '- Report Path: `' . (string) (data_get($result, 'replay.new_run.report_path') ?? '') . '`',
        ];

        if (is_array(data_get($result, 'error'))) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) data_get($result, 'error.stage', 'replay') . '`';
            $lines[] = '- Message: ' . (string) data_get($result, 'error.message', 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
