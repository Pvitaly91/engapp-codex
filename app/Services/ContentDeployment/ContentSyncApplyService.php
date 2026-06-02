<?php

namespace App\Services\ContentDeployment;

use App\Support\PackageSeed\AbstractCrossDomainChangedContentService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ContentSyncApplyService extends AbstractCrossDomainChangedContentService
{
    public function __construct(
        private readonly ContentSyncPlanService $contentSyncPlanService,
        private readonly ChangedContentApplyService $changedContentApplyService,
        private readonly ContentSyncStateService $contentSyncStateService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(array $options = []): array
    {
        try {
            $normalizedOptions = $this->normalizeOptions($options);
            $this->heartbeat($normalizedOptions);
            $planResult = $this->contentSyncPlanService->run([
                'domains' => $normalizedOptions['domains'],
                'base_refs_by_domain' => $normalizedOptions['base_refs_by_domain'],
                'head_ref' => $normalizedOptions['head_ref'],
                'with_release_check' => $normalizedOptions['with_release_check'],
                'check_profile' => $normalizedOptions['check_profile'],
                'strict' => false,
            ]);
            $result = $this->resultTemplate($planResult, $normalizedOptions);

            if (is_array($planResult['error'] ?? null)) {
                $result['status'] = 'blocked';
                $result['error'] = $planResult['error'];
                $result['domains_after'] = $this->reloadDomainStates($result, $normalizedOptions);

                return $result;
            }

            $driftedDomains = $this->driftedInitializedDomainsFromPlan($planResult);
            $bootstrapDomains = array_values((array) ($planResult['bootstrap']['required_domains'] ?? []));

            if ($blockingError = $this->blockingError($planResult, $normalizedOptions, $bootstrapDomains)) {
                $result['status'] = 'blocked';
                $result['error'] = $blockingError;
                $result['domains_after'] = $this->reloadDomainStates($result, $normalizedOptions);

                return $result;
            }

            if (! $normalizedOptions['dry_run'] && ! $normalizedOptions['force']) {
                $result['status'] = 'blocked';
                $result['error'] = [
                    'stage' => 'sync_apply',
                    'reason' => 'force_required',
                    'message' => 'Live content sync repair requires --force.',
                ];
                $result['domains_after'] = $this->reloadDomainStates($result, $normalizedOptions);

                return $result;
            }

            if ($driftedDomains !== []) {
                $this->heartbeat($normalizedOptions);
                $changedResult = $this->changedContentApplyService->run(null, [
                    'domains' => $driftedDomains,
                    'base_refs_by_domain' => $this->baseRefsByDomain($planResult, $driftedDomains),
                    'head' => (string) ($planResult['deployment_refs']['current_deployed_ref'] ?? ''),
                    'dry_run' => (bool) $normalizedOptions['dry_run'],
                    'force' => (bool) $normalizedOptions['force'],
                    'with_release_check' => (bool) $normalizedOptions['with_release_check'],
                    'skip_release_check' => (bool) $normalizedOptions['skip_release_check'],
                    'check_profile' => (string) $normalizedOptions['check_profile'],
                    'strict' => (bool) $normalizedOptions['strict'],
                    'track_sync_state' => false,
                    'heartbeat' => $normalizedOptions['heartbeat'],
                ]);
                $result['apply']['executed'] = true;
                $result['apply']['changed_content_result'] = $changedResult;

                $this->recordChangedApplyAttempt($planResult, $changedResult, $driftedDomains, $normalizedOptions);

                if (! $normalizedOptions['dry_run'] && is_array($changedResult['error'] ?? null)) {
                    $result['error'] = $changedResult['error'];
                    $result['status'] = $this->successfulDomainsFromChangedApply($planResult, $changedResult, $driftedDomains) !== []
                        ? 'partial'
                        : 'failed';
                    $result['domains_after'] = $this->reloadDomainStates($result, $normalizedOptions);

                    return $result;
                }
            }

            $bootstrapResult = $this->handleBootstrapDomains($planResult, $bootstrapDomains, $normalizedOptions);
            $result['apply']['bootstrap']['requested'] = $bootstrapDomains !== [] && (bool) $normalizedOptions['bootstrap_uninitialized'];
            $result['apply']['bootstrap']['simulated'] = $bootstrapResult['simulated'];
            $result['apply']['bootstrap']['applied'] = $bootstrapResult['applied'];

            if (is_array($bootstrapResult['error'] ?? null)) {
                $result['error'] = $bootstrapResult['error'];
                $result['status'] = 'failed';
                $result['domains_after'] = $this->reloadDomainStates($result, $normalizedOptions);

                return $result;
            }

            $result['domains_after'] = $this->reloadDomainStates($result, $normalizedOptions);
            $result['status'] = $normalizedOptions['dry_run'] ? 'dry_run' : 'completed';

            return $result;
        } catch (Throwable $exception) {
            return $this->errorResult($options, $exception);
        }
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $domains = collect(array_keys((array) ($result['domains_before'] ?? [])))
            ->map(fn (string $domain): string => Str::slug($domain))
            ->implode('-');
        $runType = (bool) ($result['apply']['dry_run'] ?? false) ? 'dry-run' : 'live';
        $hash = substr(sha1(json_encode([
            $result['domains_before'] ?? [],
            $result['status'] ?? null,
            $result['apply']['bootstrap'] ?? [],
        ])), 0, 8);
        $fileName = ($domains !== '' ? $domains : 'content') . '-sync-apply-' . $runType . '-' . $hash . '.md';
        $relativePath = 'content-sync-apply-reports/' . $fileName;

        Storage::disk('local')->put($relativePath, $this->renderMarkdownReport($result));

        return $relativePath;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        $checkProfile = strtolower(trim((string) ($options['check_profile'] ?? 'release')));

        if (! in_array($checkProfile, ['scaffold', 'release'], true)) {
            throw new RuntimeException('Unsupported sync apply check profile. Use scaffold or release.');
        }

        return [
            'domains' => $this->normalizeDomainsOption($options['domains'] ?? null),
            'base_refs_by_domain' => $this->normalizeBaseRefsByDomain(
                is_array($options['base_refs_by_domain'] ?? null) ? $options['base_refs_by_domain'] : null
            ),
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'force' => (bool) ($options['force'] ?? false),
            'with_release_check' => (bool) ($options['with_release_check'] ?? false),
            'skip_release_check' => (bool) ($options['skip_release_check'] ?? false),
            'check_profile' => $checkProfile,
            'strict' => (bool) ($options['strict'] ?? false),
            'bootstrap_uninitialized' => (bool) ($options['bootstrap_uninitialized'] ?? false),
            'head_ref' => trim((string) ($options['head_ref'] ?? '')),
            'heartbeat' => is_callable($options['heartbeat'] ?? null) ? $options['heartbeat'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $planResult
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function resultTemplate(array $planResult, array $options): array
    {
        return [
            'domains_before' => (array) ($planResult['domains'] ?? []),
            'plan' => $planResult,
            'apply' => [
                'executed' => false,
                'dry_run' => (bool) $options['dry_run'],
                'changed_content_result' => null,
                'bootstrap' => [
                    'requested' => false,
                    'simulated' => [],
                    'applied' => [],
                ],
            ],
            'domains_after' => null,
            'status' => 'ready',
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $planResult
     * @return list<string>
     */
    private function driftedInitializedDomainsFromPlan(array $planResult): array
    {
        $domains = [];

        foreach ((array) ($planResult['domains'] ?? []) as $domain => $domainState) {
            $domainState = (array) $domainState;

            if ((bool) ($domainState['bootstrap_required'] ?? false)) {
                continue;
            }

            if ((bool) ($domainState['drifted'] ?? false)) {
                $domains[] = (string) $domain;
            }
        }

        return $domains;
    }

    /**
     * @param  array<string, mixed>  $planResult
     * @param  list<string>  $bootstrapDomains
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>|null
     */
    private function blockingError(array $planResult, array $options, array $bootstrapDomains): ?array
    {
        if ($bootstrapDomains !== [] && ! (bool) ($options['bootstrap_uninitialized'] ?? false)) {
            return [
                'stage' => 'sync_apply',
                'reason' => 'bootstrap_required',
                'message' => 'One or more domains are uninitialized. Re-run with --bootstrap-uninitialized to record an explicit sync cursor.',
                'domains' => $bootstrapDomains,
            ];
        }

        if ((bool) ($options['strict'] ?? false)) {
            if ((int) ($planResult['content_plan']['summary']['blocked'] ?? 0) > 0) {
                return [
                    'stage' => 'planning',
                    'reason' => 'blocked_packages',
                    'message' => 'Sync repair planner found blocked packages and --strict is enabled.',
                ];
            }

            if ((int) ($planResult['content_plan']['summary']['warnings'] ?? 0) > 0) {
                return [
                    'stage' => 'planning',
                    'reason' => 'warnings_are_fatal',
                    'message' => 'Sync repair planner returned warnings and --strict is enabled.',
                ];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $planResult
     * @param  list<string>  $domains
     * @return array<string, string|null>
     */
    private function baseRefsByDomain(array $planResult, array $domains): array
    {
        $baseRefs = [];

        foreach ($domains as $domain) {
            $baseRefs[$domain] = $planResult['domains'][$domain]['effective_base_ref'] ?? null;
        }

        return $baseRefs;
    }

    /**
     * @param  array<string, mixed>  $planResult
     * @param  array<string, mixed>  $changedResult
     * @param  list<string>  $driftedDomains
     * @param  array<string, mixed>  $options
     */
    private function recordChangedApplyAttempt(array $planResult, array $changedResult, array $driftedDomains, array $options): void
    {
        $headRef = (string) ($planResult['deployment_refs']['current_deployed_ref'] ?? '');
        $baseRefsByDomain = $this->baseRefsByDomain($planResult, $driftedDomains);

        if ($options['dry_run']) {
            foreach ($driftedDomains as $domain) {
                $this->contentSyncStateService->recordDryRun([$domain], [$domain => $baseRefsByDomain[$domain] ?? null], $headRef, [
                    'domains' => [
                        $domain => $this->domainMeta($planResult, $changedResult, $domain, 'dry_run'),
                    ],
                ]);
            }

            return;
        }

        $successfulDomains = $this->successfulDomainsFromChangedApply($planResult, $changedResult, $driftedDomains);
        $failedDomains = array_values(array_diff($driftedDomains, $successfulDomains));

        foreach ($successfulDomains as $domain) {
            $this->contentSyncStateService->recordSuccess([$domain], [$domain => $baseRefsByDomain[$domain] ?? null], $headRef, [
                'domains' => [
                    $domain => $this->domainMeta($planResult, $changedResult, $domain, 'success'),
                ],
            ]);
        }

        foreach ($failedDomains as $domain) {
            $this->contentSyncStateService->recordFailure([$domain], [$domain => $baseRefsByDomain[$domain] ?? null], $headRef, [
                'domains' => [
                    $domain => $this->domainMeta($planResult, $changedResult, $domain, 'failed'),
                ],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $planResult
     * @param  array<string, mixed>  $changedResult
     * @param  list<string>  $driftedDomains
     * @return list<string>
     */
    private function successfulDomainsFromChangedApply(array $planResult, array $changedResult, array $driftedDomains): array
    {
        if (! is_array($changedResult['error'] ?? null)) {
            return $driftedDomains;
        }

        $executionStatuses = $this->executionStatusesByDomain($changedResult);
        $successful = [];

        foreach ($driftedDomains as $domain) {
            $actionablePackages = $this->actionablePlanPackagesForDomain($changedResult, $domain);

            if ($actionablePackages === []) {
                $successful[] = $domain;

                continue;
            }

            $statuses = $executionStatuses[$domain] ?? [];

            if ($statuses !== [] && count($statuses) === count($actionablePackages) && collect($statuses)->every(
                static fn (string $status): bool => $status === 'ok'
            )) {
                $successful[] = $domain;
            }
        }

        return $successful;
    }

    /**
     * @param  array<string, mixed>  $changedResult
     * @return array<string, list<string>>
     */
    private function executionStatusesByDomain(array $changedResult): array
    {
        $statuses = [];

        foreach (['cleanup_deleted', 'upsert_present'] as $phase) {
            foreach ((array) ($changedResult['execution'][$phase]['packages'] ?? []) as $package) {
                $domain = (string) ($package['domain'] ?? 'unknown');
                $statuses[$domain][] = (string) ($package['status'] ?? 'pending');
            }
        }

        return $statuses;
    }

    /**
     * @param  array<string, mixed>  $changedResult
     * @return list<array<string, mixed>>
     */
    private function actionablePlanPackagesForDomain(array $changedResult, string $domain): array
    {
        return array_values(array_filter(
            (array) ($changedResult['plan']['packages'] ?? []),
            static fn (array $package): bool => ($package['domain'] ?? null) === $domain
                && in_array((string) ($package['recommended_action'] ?? 'skip'), ['seed', 'refresh', 'unseed_deleted'], true)
        ));
    }

    /**
     * @param  array<string, mixed>  $planResult
     * @param  array<string, mixed>  $changedResult
     * @return array<string, mixed>
     */
    private function domainMeta(array $planResult, array $changedResult, string $domain, string $status): array
    {
        return [
            'sync_repair' => true,
            'domain_status' => $status,
            'current_deployed_ref' => $planResult['deployment_refs']['current_deployed_ref'] ?? null,
            'stopped_phase' => $changedResult['error']['phase'] ?? null,
            'stopped_package' => $changedResult['error']['package'] ?? null,
            'error_reason' => $changedResult['error']['reason'] ?? null,
            'error_message' => $changedResult['error']['message'] ?? null,
            'plan_summary' => (array) ($changedResult['plan']['summary'] ?? []),
            'preflight_summary' => (array) ($changedResult['preflight']['summary'] ?? []),
            'bootstrap' => false,
        ];
    }

    /**
     * @param  array<string, mixed>  $planResult
     * @param  list<string>  $bootstrapDomains
     * @param  array<string, mixed>  $options
     * @return array{simulated:list<array<string,mixed>>,applied:list<array<string,mixed>>,error:array<string,mixed>|null}
     */
    private function handleBootstrapDomains(array $planResult, array $bootstrapDomains, array $options): array
    {
        $simulated = [];
        $applied = [];

        if ($bootstrapDomains === [] || ! (bool) ($options['bootstrap_uninitialized'] ?? false)) {
            return [
                'simulated' => $simulated,
                'applied' => $applied,
                'error' => null,
            ];
        }

        $headRef = (string) ($planResult['deployment_refs']['current_deployed_ref'] ?? '');

        foreach ($bootstrapDomains as $domain) {
            $this->heartbeat($options);
            $entry = [
                'domain' => $domain,
                'head_ref' => $headRef,
                'bootstrap' => true,
            ];

            if ($options['dry_run']) {
                $simulated[] = $entry;

                continue;
            }

            $this->contentSyncStateService->recordSuccess([$domain], [$domain => null], $headRef, [
                'domains' => [
                    $domain => [
                        'sync_repair' => true,
                        'bootstrap' => true,
                        'current_deployed_ref' => $headRef,
                        'error_message' => null,
                    ],
                ],
            ]);
            $applied[] = $entry;
        }

        return [
            'simulated' => $simulated,
            'applied' => $applied,
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, array<string, mixed>>
     */
    private function reloadDomainStates(array $result, array $options): array
    {
        $domains = array_keys((array) ($result['domains_before'] ?? []));
        $headRef = trim((string) ($result['plan']['deployment_refs']['current_deployed_ref'] ?? $options['head_ref'] ?? ''));
        $fallbackRefs = array_fill_keys($domains, $headRef);
        $descriptions = $this->contentSyncStateService->describe($domains, $fallbackRefs, $headRef);
        $states = [];

        foreach ($domains as $domain) {
            $description = (array) ($descriptions[$domain] ?? []);
            $syncStateRef = $this->normalizedNullableString($description['sync_state_ref'] ?? null);
            $bootstrapRequired = $syncStateRef === null;
            $states[$domain] = [
                'domain' => $domain,
                'sync_state_ref' => $syncStateRef,
                'current_deployed_ref' => $headRef,
                'effective_base_ref' => $bootstrapRequired ? null : $syncStateRef,
                'fallback_base_ref' => $this->normalizedNullableString($description['fallback_base_ref'] ?? null),
                'drifted' => ! $bootstrapRequired && strtolower($syncStateRef) !== strtolower($headRef),
                'bootstrap_required' => $bootstrapRequired,
                'status' => (string) ($description['status'] ?? ($bootstrapRequired ? 'uninitialized' : 'synced')),
                'last_successful_ref' => $this->normalizedNullableString($description['last_successful_ref'] ?? null),
                'last_successful_applied_at' => $description['last_successful_applied_at'] ?? null,
                'last_attempted_base_ref' => $this->normalizedNullableString($description['last_attempted_base_ref'] ?? null),
                'last_attempted_head_ref' => $this->normalizedNullableString($description['last_attempted_head_ref'] ?? null),
                'last_attempted_status' => $this->normalizedNullableString($description['last_attempted_status'] ?? null),
                'last_attempted_at' => $description['last_attempted_at'] ?? null,
                'last_attempt_meta' => is_array($description['last_attempt_meta'] ?? null) ? $description['last_attempt_meta'] : null,
                'sync_state_uninitialized' => $bootstrapRequired,
            ];
        }

        return $states;
    }

    private function normalizedNullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function errorResult(array $options, Throwable $exception): array
    {
        return [
            'domains_before' => [],
            'plan' => [
                'deployment_refs' => [
                    'current_deployed_ref' => $this->normalizedNullableString($options['head_ref'] ?? null),
                ],
                'domains' => [],
                'summary' => [
                    'synced_domains' => 0,
                    'drifted_domains' => 0,
                    'uninitialized_domains' => 0,
                    'blocked' => 0,
                    'warnings' => 0,
                    'changed_packages' => 0,
                    'deleted_cleanup_candidates' => 0,
                    'seed_candidates' => 0,
                    'refresh_candidates' => 0,
                ],
                'content_plan' => null,
                'bootstrap' => [
                    'required_domains' => [],
                ],
                'error' => null,
            ],
            'apply' => [
                'executed' => false,
                'dry_run' => (bool) ($options['dry_run'] ?? false),
                'changed_content_result' => null,
                'bootstrap' => [
                    'requested' => false,
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
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# Content Sync Apply',
            '',
            '- Status: `' . (string) ($result['status'] ?? 'ready') . '`',
            '- Dry Run: `' . (((bool) ($result['apply']['dry_run'] ?? false)) ? 'true' : 'false') . '`',
            '- Current Deployed Ref: `' . (string) (($result['plan']['deployment_refs']['current_deployed_ref'] ?? null) ?: '') . '`',
            '',
            '## Domains Before',
            '',
        ];

        foreach ((array) ($result['domains_before'] ?? []) as $domain => $domainState) {
            $domainState = (array) $domainState;
            $lines[] = '- [' . $domain . ']'
                . ' status=`' . (string) ($domainState['status'] ?? 'uninitialized') . '`'
                . ' sync_ref=`' . (string) (($domainState['sync_state_ref'] ?? null) ?: '') . '`'
                . ' current_deployed_ref=`' . (string) (($domainState['current_deployed_ref'] ?? null) ?: '') . '`';
        }

        $lines[] = '';
        $lines[] = '## Changed Content Apply';
        $lines[] = '';

        if (is_array($result['apply']['changed_content_result'] ?? null)) {
            $changed = (array) $result['apply']['changed_content_result'];
            $lines[] = '- Changed Packages: ' . (int) ($changed['plan']['summary']['changed_packages'] ?? 0);
            $lines[] = '- Deleted Cleanup Candidates: ' . (int) ($changed['plan']['summary']['deleted_cleanup_candidates'] ?? 0);
            $lines[] = '- Seed Candidates: ' . (int) ($changed['plan']['summary']['seed_candidates'] ?? 0);
            $lines[] = '- Refresh Candidates: ' . (int) ($changed['plan']['summary']['refresh_candidates'] ?? 0);
            $lines[] = '- Preflight OK: ' . (int) ($changed['preflight']['summary']['ok'] ?? 0);
            $lines[] = '- Preflight Warn: ' . (int) ($changed['preflight']['summary']['warn'] ?? 0);
            $lines[] = '- Preflight Fail: ' . (int) ($changed['preflight']['summary']['fail'] ?? 0);
        } else {
            $lines[] = '- No canonical changed-content apply execution was required.';
        }

        $lines[] = '';
        $lines[] = '## Bootstrap';
        $lines[] = '';
        $lines[] = '- Requested: `' . (((bool) ($result['apply']['bootstrap']['requested'] ?? false)) ? 'true' : 'false') . '`';
        $lines[] = '- Simulated: `' . json_encode((array) ($result['apply']['bootstrap']['simulated'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`';
        $lines[] = '- Applied: `' . json_encode((array) ($result['apply']['bootstrap']['applied'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`';

        $lines[] = '';
        $lines[] = '## Domains After';
        $lines[] = '';

        foreach ((array) ($result['domains_after'] ?? []) as $domain => $domainState) {
            $domainState = (array) $domainState;
            $lines[] = '- [' . $domain . ']'
                . ' status=`' . (string) ($domainState['status'] ?? 'uninitialized') . '`'
                . ' sync_ref=`' . (string) (($domainState['sync_state_ref'] ?? null) ?: '') . '`'
                . ' last_attempt_status=`' . (string) (($domainState['last_attempted_status'] ?? null) ?: '') . '`';
        }

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) ($result['error']['stage'] ?? 'sync_apply') . '`';
            $lines[] = '- Message: ' . (string) ($result['error']['message'] ?? 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function heartbeat(array $options): void
    {
        $heartbeat = $options['heartbeat'] ?? null;

        if (is_callable($heartbeat)) {
            $heartbeat();
        }
    }
}
