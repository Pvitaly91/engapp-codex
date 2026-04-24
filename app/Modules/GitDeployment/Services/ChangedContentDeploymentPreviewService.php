<?php

namespace App\Modules\GitDeployment\Services;

use App\Services\ContentDeployment\ContentSyncStateService;
use App\Services\ContentDeployment\ChangedContentPlanService;
use Illuminate\Support\Arr;
use RuntimeException;
use Throwable;

class ChangedContentDeploymentPreviewService
{
    public function __construct(
        private readonly ChangedContentPlanService $changedContentPlanService,
        private readonly DeploymentGitRefProbe $gitRefProbe,
        private readonly NativeGitDeploymentService $nativeGitDeploymentService,
        private readonly GitHubApiClient $gitHubApiClient,
        private readonly ?ContentSyncStateService $contentSyncStateService = null,
        private readonly ?DeploymentContentLockService $deploymentContentLockService = null,
    ) {
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function preview(array $context, array $options = []): array
    {
        $normalizedContext = $this->normalizeContext($context);
        $normalizedOptions = $this->normalizeOptions($options);
        $nativeWithoutShell = $this->isNativeModeWithoutShell($normalizedContext);

        if ($nativeWithoutShell) {
            $normalizedOptions['content_apply_requested'] = false;
        }

        try {
            $refs = $this->resolveRefs($normalizedContext);
            if ($nativeWithoutShell) {
                return $this->previewWithoutShell($normalizedContext, $normalizedOptions, $refs);
            }
            $domains = ['v3', 'page-v3'];
            $fallbackBaseRefs = array_fill_keys($domains, $refs['base_ref']);
            $contentSync = $this->resolvedContentSync($domains, $fallbackBaseRefs, $refs['head_ref']);
            $plan = $this->changedContentPlanService->run(null, [
                'domains' => $domains,
                'base_refs_by_domain' => $this->effectiveBaseRefsByDomain($contentSync),
                'head' => $refs['head_ref'],
                'with_release_check' => $normalizedOptions['with_release_check'],
                'check_profile' => $normalizedOptions['check_profile'],
                'strict' => $normalizedOptions['strict'],
            ]);
        } catch (Throwable $exception) {
            return $this->errorResult($normalizedContext, $normalizedOptions, $exception);
        }

        return [
            'deployment' => [
                'mode' => $normalizedContext['mode'],
                'source_kind' => $normalizedContext['source_kind'],
                'base_ref' => $refs['base_ref'],
                'head_ref' => $refs['head_ref'],
                'branch' => $normalizedContext['branch'],
                'commit' => $normalizedContext['commit'],
                'with_release_check' => $normalizedOptions['with_release_check'],
                'check_profile' => $normalizedOptions['check_profile'],
                'scope' => [
                    'domains' => ['v3', 'page-v3'],
                    'resolved_roots' => array_values((array) ($plan['scope']['resolved_roots'] ?? [])),
                ],
            ],
            'content_sync' => [
                'domains' => $contentSync,
            ],
            'content_plan' => $plan,
            'gate' => $this->buildGate($plan, $normalizedOptions['strict']),
            'lock' => $this->lockPreview($normalizedOptions['content_apply_requested']),
            'error' => is_array($plan['error'] ?? null) ? $plan['error'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $options
     * @param  array<string, string>  $refs
     * @return array<string, mixed>
     */
    private function previewWithoutShell(array $context, array $options, array $refs): array
    {
        $domains = ['v3', 'page-v3'];
        $fallbackBaseRefs = array_fill_keys($domains, $refs['base_ref']);
        $contentSync = $this->resolvedContentSync($domains, $fallbackBaseRefs, $refs['head_ref']);

        return [
            'deployment' => [
                'mode' => $context['mode'],
                'source_kind' => $context['source_kind'],
                'base_ref' => $refs['base_ref'],
                'head_ref' => $refs['head_ref'],
                'branch' => $context['branch'],
                'commit' => $context['commit'],
                'with_release_check' => $options['with_release_check'],
                'check_profile' => $options['check_profile'],
                'scope' => [
                    'domains' => $domains,
                    'resolved_roots' => [],
                ],
            ],
            'content_sync' => [
                'domains' => $contentSync,
            ],
            'content_plan' => [
                'summary' => [
                    'changed_packages' => 0,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'error' => null,
            ],
            'gate' => [
                'strict' => $options['strict'],
                'blocked' => false,
                'reasons' => ['API-only режим: зміна контенту пропущена через відсутність proc_open.'],
            ],
            'lock' => $this->lockPreview(false),
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $preview
     * @return list<string>
     */
    public function gateReasons(array $preview): array
    {
        return array_values(array_filter(array_map(
            static fn ($reason): string => trim((string) $reason),
            (array) ($preview['gate']['reasons'] ?? [])
        )));
    }

    /**
     * @param  array<string, mixed>  $preview
     */
    public function gateBlocks(array $preview): bool
    {
        return (bool) ($preview['gate']['blocked'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function normalizeContext(array $context): array
    {
        $mode = strtolower(trim((string) ($context['mode'] ?? '')));
        $sourceKind = strtolower(trim((string) ($context['source_kind'] ?? '')));
        $branch = trim((string) ($context['branch'] ?? ''));
        $commit = trim((string) ($context['commit'] ?? ''));

        if (! in_array($mode, ['standard', 'native'], true)) {
            throw new RuntimeException('Unsupported deployment preview mode. Use standard or native.');
        }

        if (! in_array($sourceKind, ['deploy', 'backup_restore'], true)) {
            throw new RuntimeException('Unsupported deployment preview source. Use deploy or backup_restore.');
        }

        if ($sourceKind === 'deploy' && $branch === '') {
            throw new RuntimeException('Deployment preview requires a target branch for deploy mode.');
        }

        if ($sourceKind === 'backup_restore' && $commit === '') {
            throw new RuntimeException('Deployment preview requires a target commit for backup restore mode.');
        }

        return [
            'mode' => $mode,
            'source_kind' => $sourceKind,
            'branch' => $branch !== '' ? $branch : null,
            'commit' => $commit !== '' ? $commit : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        $checkProfile = strtolower(trim((string) ($options['check_profile']
            ?? config('git-deployment.content_preview.check_profile', 'release'))));

        if (! in_array($checkProfile, ['scaffold', 'release'], true)) {
            throw new RuntimeException('Unsupported content preview check profile. Use scaffold or release.');
        }

        return [
            'with_release_check' => array_key_exists('with_release_check', $options)
                ? (bool) $options['with_release_check']
                : (bool) config('git-deployment.content_preview.with_release_check', true),
            'check_profile' => $checkProfile,
            'strict' => array_key_exists('strict', $options)
                ? (bool) $options['strict']
                : (bool) config('git-deployment.content_preview.strict', true),
            'content_apply_requested' => array_key_exists('content_apply_requested', $options)
                ? (bool) $options['content_apply_requested']
                : (bool) config('git-deployment.content_apply.enabled_by_default', true),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, string>
     */
    private function resolveRefs(array $context): array
    {
        return match ($context['mode'] . ':' . $context['source_kind']) {
            'standard:deploy' => $this->resolveStandardDeployRefs((string) $context['branch']),
            'standard:backup_restore' => $this->resolveStandardRollbackRefs((string) $context['commit']),
            'native:deploy' => $this->resolveNativeDeployRefs((string) $context['branch']),
            'native:backup_restore' => $this->resolveNativeRollbackRefs((string) $context['commit']),
            default => throw new RuntimeException('Unsupported deployment preview context.'),
        };
    }

    /**
     * @return array<string, string>
     */
    private function resolveStandardDeployRefs(string $branch): array
    {
        $baseRef = $this->gitRefProbe->currentHeadCommit();

        if ($baseRef === null) {
            throw new RuntimeException('Unable to resolve the current deployed ref from local HEAD.');
        }

        $localTrackingRef = 'origin/' . $branch;
        $localTrackingSha = $this->gitRefProbe->resolveCommit($localTrackingRef);

        if ($localTrackingSha === null) {
            throw new RuntimeException(sprintf(
                'Local tracking ref [%s] is unavailable; content preview cannot diff the deploy target safely.',
                $localTrackingRef
            ));
        }

        $remoteSha = $this->gitRefProbe->remoteBranchSha($branch);

        if ($remoteSha === null) {
            throw new RuntimeException(sprintf(
                'Unable to resolve the remote target ref for branch [%s].',
                $branch
            ));
        }

        if (strtolower($remoteSha) !== strtolower($localTrackingSha)) {
            throw new RuntimeException(sprintf(
                'Local tracking ref [%s] is stale compared to remote; content preview cannot diff the deploy target safely without fetching.',
                $localTrackingRef
            ));
        }

        return [
            'base_ref' => $baseRef,
            'head_ref' => $localTrackingRef,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function resolveStandardRollbackRefs(string $commit): array
    {
        $baseRef = $this->gitRefProbe->currentHeadCommit();

        if ($baseRef === null) {
            throw new RuntimeException('Unable to resolve the current deployed ref from local HEAD.');
        }

        if (! $this->gitRefProbe->commitExists($commit)) {
            throw new RuntimeException(sprintf(
                'Rollback target commit [%s] is not available in local git objects for content preview.',
                $commit
            ));
        }

        return [
            'base_ref' => $baseRef,
            'head_ref' => $commit,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function resolveNativeDeployRefs(string $branch): array
    {
        $baseRef = $this->nativeGitDeploymentService->headCommit();

        if ($baseRef === null || trim($baseRef) === '') {
            throw new RuntimeException('Unable to resolve the current deployed ref for native deployment preview.');
        }

        $branchInfo = $this->gitHubApiClient->getBranch($branch);
        $remoteSha = trim((string) Arr::get($branchInfo, 'object.sha', ''));

        if ($remoteSha === '') {
            throw new RuntimeException(sprintf(
                'GitHub API did not return a target commit for branch [%s].',
                $branch
            ));
        }

        if ($this->supportsShellCommands() && ! $this->gitRefProbe->commitExists($remoteSha)) {
            throw new RuntimeException(sprintf(
                'Target branch [%s] resolves to commit [%s], but that commit is not available in local git objects for content preview.',
                $branch,
                $remoteSha
            ));
        }

        return [
            'base_ref' => trim($baseRef),
            'head_ref' => $remoteSha,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function resolveNativeRollbackRefs(string $commit): array
    {
        $baseRef = $this->nativeGitDeploymentService->headCommit();

        if ($baseRef === null || trim($baseRef) === '') {
            throw new RuntimeException('Unable to resolve the current deployed ref for native rollback preview.');
        }

        if ($this->supportsShellCommands() && ! $this->gitRefProbe->commitExists($commit)) {
            throw new RuntimeException(sprintf(
                'Rollback target commit [%s] is not available in local git objects for content preview.',
                $commit
            ));
        }

        return [
            'base_ref' => trim($baseRef),
            'head_ref' => $commit,
        ];
    }

    /**
     * @param  array<string, mixed>  $plan
     * @return array<string, mixed>
     */
    private function buildGate(array $plan, bool $strict): array
    {
        $reasons = [];

        if (is_array($plan['error'] ?? null) && trim((string) ($plan['error']['message'] ?? '')) !== '') {
            $reasons[] = trim((string) $plan['error']['message']);
        }

        $blockedCount = (int) ($plan['summary']['blocked'] ?? 0);

        if ($blockedCount > 0) {
            $reasons[] = sprintf(
                'Content plan contains %d blocked package(s).',
                $blockedCount
            );
        }

        $warningCount = (int) ($plan['summary']['warnings'] ?? 0);

        if ($strict && $warningCount > 0) {
            $reasons[] = sprintf(
                'Strict deployment gate treats %d content warning(s) as blockers.',
                $warningCount
            );
        }

        return [
            'strict' => $strict,
            'blocked' => $reasons !== [],
            'reasons' => array_values(array_unique(array_filter($reasons))),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function errorResult(array $context, array $options, Throwable $exception): array
    {
        $message = $exception->getMessage();
        $error = [
            'stage' => 'deployment_preview',
            'message' => $message,
            'exception_class' => $exception::class,
        ];

        return [
            'deployment' => [
                'mode' => $context['mode'],
                'source_kind' => $context['source_kind'],
                'base_ref' => null,
                'head_ref' => null,
                'branch' => $context['branch'],
                'commit' => $context['commit'],
                'with_release_check' => $options['with_release_check'],
                'check_profile' => $options['check_profile'],
                'scope' => [
                    'domains' => ['v3', 'page-v3'],
                    'resolved_roots' => [],
                ],
            ],
            'content_sync' => [
                'domains' => [],
            ],
            'content_plan' => [
                'summary' => [
                    'changed_packages' => 0,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                    'skipped' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                ],
                'phases' => [
                    'cleanup_deleted' => [],
                    'upsert_present' => [],
                ],
                'packages' => [],
                'error' => $error,
            ],
            'gate' => [
                'strict' => $options['strict'],
                'blocked' => true,
                'reasons' => [$message],
            ],
            'lock' => $this->lockPreview((bool) ($options['content_apply_requested'] ?? true)),
            'error' => $error,
        ];
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, string>  $fallbackBaseRefs
     * @return array<string, array<string, mixed>>
     */
    private function resolvedContentSync(array $domains, array $fallbackBaseRefs, string $headRef): array
    {
        if ($this->contentSyncStateService === null) {
            $contentSync = [];

            foreach ($domains as $domain) {
                $fallbackBaseRef = $fallbackBaseRefs[$domain] ?? null;

                $contentSync[$domain] = [
                    'domain' => $domain,
                    'sync_state_ref' => null,
                    'fallback_base_ref' => $fallbackBaseRef,
                    'effective_base_ref' => $fallbackBaseRef,
                    'fallback_used' => true,
                    'drift_from_code_ref' => false,
                    'sync_state_uninitialized' => true,
                    'status' => 'uninitialized',
                    'last_successful_ref' => null,
                    'last_successful_applied_at' => null,
                    'last_attempted_base_ref' => null,
                    'last_attempted_head_ref' => null,
                    'last_attempted_status' => null,
                    'last_attempted_at' => null,
                    'last_attempt_meta' => null,
                    'target_head_ref' => $headRef,
                    'would_advance_to_head' => $fallbackBaseRef !== null && strtolower($fallbackBaseRef) !== strtolower($headRef),
                ];
            }

            return $contentSync;
        }

        return $this->contentSyncStateService->describe($domains, $fallbackBaseRefs, $headRef);
    }

    /**
     * @param  array<string, array<string, mixed>>  $contentSync
     * @return array<string, string|null>
     */
    private function effectiveBaseRefsByDomain(array $contentSync): array
    {
        $baseRefs = [];

        foreach ($contentSync as $domain => $domainSync) {
            $baseRefs[$domain] = $domainSync['effective_base_ref'] ?? null;
        }

        return $baseRefs;
    }

    private function isNativeModeWithoutShell(array $context): bool
    {
        return $context['mode'] === 'native' && ! $this->supportsShellCommands();
    }

    private function supportsShellCommands(): bool
    {
        return function_exists('proc_open');
    }

    /**
     * @return array<string, mixed>
     */
    private function lockPreview(bool $requiredForContentApply): array
    {
        try {
            return ($this->deploymentContentLockService ?? app(DeploymentContentLockService::class))
                ->previewStatus($requiredForContentApply);
        } catch (Throwable $exception) {
            return [
                'required_for_content_apply' => $requiredForContentApply,
                'current_status' => 'unavailable',
                'status' => 'unavailable',
                'blocked' => false,
                'stale_takeover_possible' => false,
                'takeover_requested' => false,
                'takeover_performed' => false,
                'snapshot' => null,
                'warnings' => ['content_operation_lock_status_unavailable'],
                'error' => [
                    'stage' => 'content_operation_lock',
                    'message' => $exception->getMessage(),
                    'exception_class' => $exception::class,
                ],
            ];
        }
    }
}
