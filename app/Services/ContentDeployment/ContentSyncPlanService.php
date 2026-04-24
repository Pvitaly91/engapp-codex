<?php

namespace App\Services\ContentDeployment;

use App\Modules\GitDeployment\Services\DeploymentGitRefProbe;
use App\Support\PackageSeed\AbstractCrossDomainChangedContentService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ContentSyncPlanService extends AbstractCrossDomainChangedContentService
{
    public function __construct(
        private readonly ContentSyncStateService $contentSyncStateService,
        private readonly ChangedContentPlanService $changedContentPlanService,
        private readonly DeploymentGitRefProbe $deploymentGitRefProbe,
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
            $domains = $normalizedOptions['domains'];
            $headRef = $this->resolveHeadRef($normalizedOptions['head_ref']);
            $domainStates = $this->domainStates($domains, $headRef, $normalizedOptions['base_refs_by_domain']);
            $driftedDomains = $this->driftedInitializedDomains($domainStates);
            $contentPlan = $this->contentPlanForDriftedDomains($driftedDomains, $domainStates, $normalizedOptions, $headRef);
            $result = $this->resultTemplate($domainStates, $contentPlan, $headRef, $normalizedOptions);
            $result['error'] = $this->errorForResult($result, $normalizedOptions);

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
        $domains = collect(array_keys((array) ($result['domains'] ?? [])))
            ->map(fn (string $domain): string => Str::slug($domain))
            ->implode('-');
        $hash = substr(sha1(json_encode([
            $result['deployment_refs']['current_deployed_ref'] ?? null,
            $result['domains'] ?? [],
            $result['summary'] ?? [],
        ])), 0, 8);
        $fileName = ($domains !== '' ? $domains : 'content') . '-sync-plan-' . $hash . '.md';
        $relativePath = 'content-sync-plans/' . $fileName;

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
            throw new RuntimeException('Unsupported sync plan check profile. Use scaffold or release.');
        }

        return [
            'domains' => $this->normalizeDomainsOption($options['domains'] ?? null),
            'base_refs_by_domain' => $this->normalizeBaseRefsByDomain(
                is_array($options['base_refs_by_domain'] ?? null) ? $options['base_refs_by_domain'] : null
            ),
            'with_release_check' => (bool) ($options['with_release_check'] ?? false),
            'check_profile' => $checkProfile,
            'strict' => (bool) ($options['strict'] ?? false),
            'head_ref' => trim((string) ($options['head_ref'] ?? '')),
        ];
    }

    private function resolveHeadRef(string $overrideHeadRef): string
    {
        $headRef = $overrideHeadRef !== ''
            ? $overrideHeadRef
            : (string) ($this->deploymentGitRefProbe->currentHeadCommit() ?? '');

        $headRef = trim($headRef);

        if ($headRef === '') {
            throw new RuntimeException('Unable to resolve the current deployed code ref for content sync planning.');
        }

        return $headRef;
    }

    /**
     * @param  list<string>  $domains
     * @return array<string, array<string, mixed>>
     */
    private function domainStates(array $domains, string $headRef, array $baseRefsByDomain = []): array
    {
        if ($baseRefsByDomain !== []) {
            $states = [];

            foreach ($domains as $domain) {
                $baseRef = $this->normalizedNullableString($baseRefsByDomain[$domain] ?? null);
                $bootstrapRequired = $baseRef === null;
                $drifted = ! $bootstrapRequired && strtolower($baseRef) !== strtolower($headRef);

                $states[$domain] = [
                    'domain' => $domain,
                    'sync_state_ref' => $baseRef,
                    'current_deployed_ref' => $headRef,
                    'fallback_base_ref' => $headRef,
                    'effective_base_ref' => $bootstrapRequired ? null : $baseRef,
                    'drifted' => $drifted,
                    'bootstrap_required' => $bootstrapRequired,
                    'status' => $bootstrapRequired ? 'uninitialized' : ($drifted ? 'drifted' : 'synced'),
                    'last_successful_ref' => $baseRef,
                    'last_successful_applied_at' => null,
                    'last_attempted_base_ref' => null,
                    'last_attempted_head_ref' => null,
                    'last_attempted_status' => null,
                    'last_attempted_at' => null,
                    'last_attempt_meta' => null,
                    'sync_state_uninitialized' => $bootstrapRequired,
                ];
            }

            return $states;
        }

        $fallbackRefs = array_fill_keys($domains, $headRef);
        $descriptions = $this->contentSyncStateService->describe($domains, $fallbackRefs, $headRef);
        $states = [];

        foreach ($domains as $domain) {
            $description = (array) ($descriptions[$domain] ?? []);
            $syncStateRef = $this->normalizedNullableString($description['sync_state_ref'] ?? null);
            $bootstrapRequired = $syncStateRef === null;
            $drifted = ! $bootstrapRequired && strtolower($syncStateRef) !== strtolower($headRef);
            $status = (string) ($description['status'] ?? ($bootstrapRequired ? 'uninitialized' : ($drifted ? 'drifted' : 'synced')));

            $states[$domain] = [
                'domain' => $domain,
                'sync_state_ref' => $syncStateRef,
                'current_deployed_ref' => $headRef,
                'fallback_base_ref' => $this->normalizedNullableString($description['fallback_base_ref'] ?? null),
                'effective_base_ref' => $bootstrapRequired ? null : $syncStateRef,
                'drifted' => $drifted,
                'bootstrap_required' => $bootstrapRequired,
                'status' => $status,
                'last_successful_ref' => $this->normalizedNullableString($description['last_successful_ref'] ?? null),
                'last_successful_applied_at' => $description['last_successful_applied_at'] ?? null,
                'last_attempted_base_ref' => $this->normalizedNullableString($description['last_attempted_base_ref'] ?? null),
                'last_attempted_head_ref' => $this->normalizedNullableString($description['last_attempted_head_ref'] ?? null),
                'last_attempted_status' => $this->normalizedNullableString($description['last_attempted_status'] ?? null),
                'last_attempted_at' => $description['last_attempted_at'] ?? null,
                'last_attempt_meta' => is_array($description['last_attempt_meta'] ?? null)
                    ? $description['last_attempt_meta']
                    : null,
                'sync_state_uninitialized' => $bootstrapRequired,
            ];
        }

        return $states;
    }

    /**
     * @param  array<string, array<string, mixed>>  $domainStates
     * @return list<string>
     */
    private function driftedInitializedDomains(array $domainStates): array
    {
        return array_values(array_map(
            static fn (array $domainState): string => (string) $domainState['domain'],
            array_values(array_filter(
                $domainStates,
                static fn (array $domainState): bool => ! (bool) ($domainState['bootstrap_required'] ?? false)
                    && (bool) ($domainState['drifted'] ?? false)
            ))
        ));
    }

    /**
     * @param  list<string>  $driftedDomains
     * @param  array<string, array<string, mixed>>  $domainStates
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function contentPlanForDriftedDomains(array $driftedDomains, array $domainStates, array $options, string $headRef): array
    {
        if ($driftedDomains === []) {
            return $this->emptyContentPlan($headRef);
        }

        $baseRefsByDomain = [];

        foreach ($driftedDomains as $domain) {
            $baseRefsByDomain[$domain] = $domainStates[$domain]['effective_base_ref'];
        }

        return $this->changedContentPlanService->run(null, [
            'domains' => $driftedDomains,
            'base_refs_by_domain' => $baseRefsByDomain,
            'head' => $headRef,
            'with_release_check' => (bool) $options['with_release_check'],
            'check_profile' => (string) $options['check_profile'],
            'strict' => false,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyContentPlan(string $headRef): array
    {
        return [
            'diff' => [
                'mode' => 'refs',
                'base' => null,
                'base_refs_by_domain' => [],
                'head' => $headRef,
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => null,
                'domains' => [],
                'resolved_roots' => [],
                'single_package' => false,
                'with_release_check' => false,
                'check_profile' => 'release',
                'strict' => false,
            ],
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
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $domainStates
     * @param  array<string, mixed>  $contentPlan
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function resultTemplate(array $domainStates, array $contentPlan, string $headRef, array $options): array
    {
        $uninitializedDomains = array_values(array_map(
            static fn (array $domainState): string => (string) $domainState['domain'],
            array_values(array_filter(
                $domainStates,
                static fn (array $domainState): bool => (bool) ($domainState['bootstrap_required'] ?? false)
            ))
        ));
        $failedLastApplyDomains = array_values(array_filter(
            array_keys($domainStates),
            fn (string $domain): bool => ($domainStates[$domain]['status'] ?? null) === 'failed_last_apply'
        ));

        return [
            'deployment_refs' => [
                'current_deployed_ref' => $headRef,
            ],
            'domains' => $domainStates,
            'summary' => [
                'synced_domains' => count(array_filter($domainStates, static fn (array $state): bool => ($state['status'] ?? null) === 'synced')),
                'drifted_domains' => count(array_filter($domainStates, static fn (array $state): bool => (bool) ($state['drifted'] ?? false))),
                'uninitialized_domains' => count($uninitializedDomains),
                'blocked' => (int) ($contentPlan['summary']['blocked'] ?? 0),
                'warnings' => (int) ($contentPlan['summary']['warnings'] ?? 0) + count($uninitializedDomains) + count($failedLastApplyDomains),
                'changed_packages' => (int) ($contentPlan['summary']['changed_packages'] ?? 0),
                'deleted_cleanup_candidates' => (int) ($contentPlan['summary']['deleted_cleanup_candidates'] ?? 0),
                'seed_candidates' => (int) ($contentPlan['summary']['seed_candidates'] ?? 0),
                'refresh_candidates' => (int) ($contentPlan['summary']['refresh_candidates'] ?? 0),
            ],
            'content_plan' => $contentPlan,
            'bootstrap' => [
                'required_domains' => $uninitializedDomains,
            ],
            'options' => [
                'domains' => array_keys($domainStates),
                'with_release_check' => (bool) $options['with_release_check'],
                'check_profile' => (string) $options['check_profile'],
                'strict' => (bool) $options['strict'],
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>|null
     */
    private function errorForResult(array $result, array $options): ?array
    {
        if (is_array($result['content_plan']['error'] ?? null)) {
            return $result['content_plan']['error'];
        }

        if (! (bool) ($options['strict'] ?? false)) {
            return null;
        }

        $bootstrapRequired = (array) ($result['bootstrap']['required_domains'] ?? []);

        if ($bootstrapRequired !== []) {
            return [
                'stage' => 'sync_plan',
                'reason' => 'bootstrap_required',
                'message' => 'One or more domains do not have an initialized content sync cursor and require explicit bootstrap.',
                'domains' => $bootstrapRequired,
            ];
        }

        if ((int) ($result['content_plan']['summary']['blocked'] ?? 0) > 0) {
            return [
                'stage' => 'planning',
                'reason' => 'blocked_packages',
                'message' => 'Changed-content planner found blocked packages for sync repair and --strict is enabled.',
            ];
        }

        if ((int) ($result['content_plan']['summary']['warnings'] ?? 0) > 0) {
            return [
                'stage' => 'planning',
                'reason' => 'warnings_are_fatal',
                'message' => 'Changed-content planner returned warnings for sync repair and --strict is enabled.',
            ];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function errorResult(array $options, Throwable $exception): array
    {
        $domains = collect(is_array($options['domains'] ?? null) ? $options['domains'] : explode(',', (string) ($options['domains'] ?? '')))
            ->map(static fn ($token): string => str_replace('_', '-', strtolower(trim((string) $token))))
            ->filter()
            ->values()
            ->all();

        return [
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
            'content_plan' => $this->emptyContentPlan($this->normalizedNullableString($options['head_ref'] ?? null) ?? ''),
            'bootstrap' => [
                'required_domains' => [],
            ],
            'options' => [
                'domains' => $domains,
                'with_release_check' => (bool) ($options['with_release_check'] ?? false),
                'check_profile' => (string) ($options['check_profile'] ?? 'release'),
                'strict' => (bool) ($options['strict'] ?? false),
            ],
            'artifacts' => [
                'report_path' => null,
            ],
            'error' => [
                'stage' => 'sync_plan',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    private function normalizedNullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized !== '' ? $normalized : null;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# Content Sync Plan',
            '',
            '- Current Deployed Ref: `' . (string) (($result['deployment_refs']['current_deployed_ref'] ?? null) ?: '') . '`',
            '- Domains: `' . implode(', ', array_keys((array) ($result['domains'] ?? []))) . '`',
            '',
            '## Summary',
            '',
            '- Synced Domains: ' . (int) ($result['summary']['synced_domains'] ?? 0),
            '- Drifted Domains: ' . (int) ($result['summary']['drifted_domains'] ?? 0),
            '- Uninitialized Domains: ' . (int) ($result['summary']['uninitialized_domains'] ?? 0),
            '- Changed Packages: ' . (int) ($result['summary']['changed_packages'] ?? 0),
            '- Deleted Cleanup Candidates: ' . (int) ($result['summary']['deleted_cleanup_candidates'] ?? 0),
            '- Seed Candidates: ' . (int) ($result['summary']['seed_candidates'] ?? 0),
            '- Refresh Candidates: ' . (int) ($result['summary']['refresh_candidates'] ?? 0),
            '- Blocked: ' . (int) ($result['summary']['blocked'] ?? 0),
            '- Warnings: ' . (int) ($result['summary']['warnings'] ?? 0),
            '',
            '## Domains',
            '',
        ];

        foreach ((array) ($result['domains'] ?? []) as $domain => $domainState) {
            $domainState = (array) $domainState;
            $lines[] = '- [' . $domain . ']'
                . ' status=`' . (string) ($domainState['status'] ?? 'uninitialized') . '`'
                . ' sync_ref=`' . (string) (($domainState['sync_state_ref'] ?? null) ?: '') . '`'
                . ' current_deployed_ref=`' . (string) (($domainState['current_deployed_ref'] ?? null) ?: '') . '`'
                . ' effective_base_ref=`' . (string) (($domainState['effective_base_ref'] ?? null) ?: '') . '`'
                . ' bootstrap_required=`' . (((bool) ($domainState['bootstrap_required'] ?? false)) ? 'true' : 'false') . '`';
        }

        $lines[] = '';
        $lines[] = '## Deleted Packages Cleanup Phase';
        $lines[] = '';

        $cleanupPackages = (array) ($result['content_plan']['phases']['cleanup_deleted'] ?? []);

        if ($cleanupPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($cleanupPackages as $package) {
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `'
                    . $this->packagePath((array) $package)
                    . '` | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`';
            }
        }

        $lines[] = '';
        $lines[] = '## Current Packages Upsert Phase';
        $lines[] = '';

        $upsertPackages = (array) ($result['content_plan']['phases']['upsert_present'] ?? []);

        if ($upsertPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($upsertPackages as $package) {
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `'
                    . $this->packagePath((array) $package)
                    . '` | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`';
            }
        }

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) ($result['error']['stage'] ?? 'sync_plan') . '`';
            $lines[] = '- Message: ' . (string) ($result['error']['message'] ?? 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
