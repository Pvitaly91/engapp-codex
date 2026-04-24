<?php

namespace App\Services\ContentDeployment;

use App\Services\PageV3PromptGenerator\PageV3ChangedPackagesPlanService;
use App\Services\V3PromptGenerator\V3ChangedPackagesPlanService;
use App\Support\PackageSeed\AbstractCrossDomainChangedContentService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ChangedContentPlanService extends AbstractCrossDomainChangedContentService
{
    public function __construct(
        private readonly V3ChangedPackagesPlanService $v3ChangedPackagesPlanService,
        private readonly PageV3ChangedPackagesPlanService $pageV3ChangedPackagesPlanService,
    ) {
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(?string $targetInput = null, array $options = []): array
    {
        $resolvedOptions = $this->normalizeOptions($options);

        try {
            $scope = $this->resolveUnifiedScope($targetInput, $resolvedOptions['domains'], $resolvedOptions['domains_explicit']);
        } catch (Throwable $exception) {
            return $this->errorResult(
                trim((string) ($targetInput ?? '')),
                $resolvedOptions['domains'],
                'target_resolution',
                $exception
            );
        }

        $result = $this->resultTemplate($scope, $resolvedOptions);
        $domainResults = [];
        $domainErrors = [];

        foreach ($scope['domains'] as $domain) {
            $service = $this->plannerForDomain($domain);
            $domainBaseRef = $this->effectiveBaseRefForDomain(
                $domain,
                $resolvedOptions['base'],
                $resolvedOptions['base_refs_by_domain']
            );

            try {
                $domainResult = $service->run($scope['input'], [
                    'base' => $domainBaseRef,
                    'head' => $resolvedOptions['head'],
                    'staged' => $resolvedOptions['staged'],
                    'working_tree' => $resolvedOptions['working_tree'],
                    'include_untracked' => $resolvedOptions['include_untracked'],
                    'with_release_check' => $resolvedOptions['with_release_check'],
                    'check_profile' => $resolvedOptions['check_profile'],
                    'strict' => false,
                ]);
            } catch (Throwable $exception) {
                $domainResult = [
                    'diff' => $result['diff'],
                    'scope' => [
                        'input' => $scope['input'],
                        'resolved_root_absolute_path' => null,
                        'resolved_root_relative_path' => $this->rootRelativePath($domain),
                        'single_package' => false,
                    ],
                    'summary' => $this->emptySummary(),
                    'phases' => [
                        'cleanup_deleted' => [],
                        'upsert_present' => [],
                    ],
                    'packages' => [],
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

            $domainResults[$domain] = $domainResult;

            if (is_array($domainResult['error'] ?? null)) {
                $domainErrors[$domain] = $domainResult['error'];
            }
        }

        $result['domains'] = $this->normalizedDomainResults($domainResults);
        $result['scope']['resolved_roots'] = $this->resolvedRootsFromDomainResults($scope['domains'], $domainResults);
        $result['scope']['single_package'] = count($result['scope']['resolved_roots']) === 1
            ? (bool) ($result['scope']['resolved_roots'][0]['single_package'] ?? false)
            : false;

        if ($scope['domains'] !== []) {
            $firstDomain = $scope['domains'][0];
            $firstDomainDiff = (array) ($domainResults[$firstDomain]['diff'] ?? []);
            $result['diff']['mode'] = (string) ($firstDomainDiff['mode'] ?? $result['diff']['mode']);
            $result['diff']['head'] = $firstDomainDiff['head'] ?? $result['diff']['head'];
            $result['diff']['include_untracked'] = (bool) ($firstDomainDiff['include_untracked'] ?? $result['diff']['include_untracked']);
        }

        $result['diff']['base_refs_by_domain'] = $this->effectiveBaseRefsFromDomainResults(
            $scope['domains'],
            $domainResults,
            $resolvedOptions['base_refs_by_domain'],
            $resolvedOptions['base']
        );

        if ($result['diff']['base'] === null && $result['diff']['base_refs_by_domain'] !== []) {
            $uniqueBaseRefs = array_values(array_unique(array_filter(
                $result['diff']['base_refs_by_domain'],
                static fn ($ref): bool => trim((string) $ref) !== ''
            )));

            if (count($uniqueBaseRefs) === 1) {
                $result['diff']['base'] = $uniqueBaseRefs[0];
            }
        }

        $cleanupPhase = $this->mergedCleanupPhase($domainResults, $scope['domains']);
        $upsertPhase = $this->mergedUpsertPhase($domainResults, $scope['domains']);
        $otherPackages = $this->mergedOtherPackages($domainResults, $scope['domains']);
        $packages = array_values(array_merge($cleanupPhase, $upsertPhase, $otherPackages));

        $result['phases'] = [
            'cleanup_deleted' => $cleanupPhase,
            'upsert_present' => $upsertPhase,
        ];
        $result['packages'] = $packages;
        $result['summary'] = $this->buildMergedSummary($packages);

        if ($domainErrors !== []) {
            $result['error'] = [
                'stage' => 'planning',
                'reason' => 'domain_planner_failed',
                'message' => 'One or more domain changed-package planners failed.',
                'domains' => $domainErrors,
            ];

            return $result;
        }

        $result['error'] = $this->strictPlannerFailure($packages, $resolvedOptions['strict']);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function writeReport(array $result): string
    {
        $domains = collect((array) ($result['scope']['domains'] ?? []))
            ->map(fn ($domain): string => Str::slug((string) $domain))
            ->implode('-');
        $mode = (string) ($result['diff']['mode'] ?? 'working_tree');
        $scopeHash = substr(sha1(json_encode([
            $result['scope']['input'] ?? null,
            $result['scope']['domains'] ?? [],
            $result['diff']['base'] ?? null,
            $result['diff']['head'] ?? null,
            $result['diff']['include_untracked'] ?? false,
        ])), 0, 8);
        $fileName = ($domains !== '' ? $domains : 'content')
            . '-changed-plan-'
            . Str::slug($mode)
            . '-'
            . $scopeHash
            . '.md';
        $relativePath = 'content-changed-plans/' . $fileName;

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
            throw new RuntimeException('Unsupported release-check profile. Use scaffold or release.');
        }

        return [
            'domains' => $this->normalizeDomainsOption($options['domains'] ?? null),
            'domains_explicit' => $this->domainsOptionWasExplicit($options['domains'] ?? null),
            'base' => trim((string) ($options['base'] ?? '')),
            'base_refs_by_domain' => $this->normalizeBaseRefsByDomain(
                is_array($options['base_refs_by_domain'] ?? null) ? $options['base_refs_by_domain'] : null
            ),
            'head' => trim((string) ($options['head'] ?? '')),
            'staged' => (bool) ($options['staged'] ?? false),
            'working_tree' => (bool) ($options['working_tree'] ?? false),
            'include_untracked' => (bool) ($options['include_untracked'] ?? false),
            'with_release_check' => (bool) ($options['with_release_check'] ?? false),
            'check_profile' => $checkProfile,
            'strict' => (bool) ($options['strict'] ?? false),
        ];
    }

    private function domainsOptionWasExplicit(string|array|null $value): bool
    {
        if (is_array($value)) {
            return collect($value)
                ->map(static fn ($token): string => trim((string) $token))
                ->filter()
                ->isNotEmpty();
        }

        return trim((string) ($value ?? '')) !== '';
    }

    /**
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    private function resultTemplate(array $scope, array $options): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => $options['base'] !== '' ? $options['base'] : null,
                'base_refs_by_domain' => $options['base_refs_by_domain'] !== [] ? $options['base_refs_by_domain'] : [],
                'head' => $options['head'] !== '' ? $options['head'] : null,
                'include_untracked' => (bool) $options['include_untracked'],
            ],
            'scope' => [
                'input' => $scope['input'],
                'domains' => $scope['domains'],
                'resolved_roots' => $scope['resolved_roots'],
                'single_package' => (bool) ($scope['single_package'] ?? false),
                'with_release_check' => (bool) $options['with_release_check'],
                'check_profile' => (string) $options['check_profile'],
                'strict' => (bool) $options['strict'],
            ],
            'summary' => $this->emptySummary(),
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
     * @param  list<string>  $domains
     * @return array<string, mixed>
     */
    private function errorResult(string $targetInput, array $domains, string $stage, Throwable $exception): array
    {
        return [
            'diff' => [
                'mode' => 'working_tree',
                'base' => null,
                'base_refs_by_domain' => [],
                'head' => null,
                'include_untracked' => false,
            ],
            'scope' => [
                'input' => $targetInput !== '' ? $targetInput : null,
                'domains' => $domains,
                'resolved_roots' => [],
                'single_package' => false,
            ],
            'summary' => $this->emptySummary(),
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
            'error' => [
                'stage' => $stage,
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ],
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $domainResults
     * @param  list<string>  $domains
     * @return list<array<string, mixed>>
     */
    private function mergedCleanupPhase(array $domainResults, array $domains): array
    {
        $orderedDomains = array_values(array_filter(['v3', 'page-v3'], fn (string $domain): bool => in_array($domain, $domains, true)));
        $packages = [];

        foreach ($orderedDomains as $domain) {
            foreach ((array) ($domainResults[$domain]['phases']['cleanup_deleted'] ?? []) as $package) {
                $packages[] = $this->annotatePackage($domain, (array) $package);
            }
        }

        return $packages;
    }

    /**
     * @param  array<string, array<string, mixed>>  $domainResults
     * @param  list<string>  $domains
     * @return list<array<string, mixed>>
     */
    private function mergedUpsertPhase(array $domainResults, array $domains): array
    {
        $orderedDomains = array_values(array_filter(['page-v3', 'v3'], fn (string $domain): bool => in_array($domain, $domains, true)));
        $packages = [];

        foreach ($orderedDomains as $domain) {
            foreach ((array) ($domainResults[$domain]['phases']['upsert_present'] ?? []) as $package) {
                $packages[] = $this->annotatePackage($domain, (array) $package);
            }
        }

        return $packages;
    }

    /**
     * @param  array<string, array<string, mixed>>  $domainResults
     * @param  list<string>  $domains
     * @return list<array<string, mixed>>
     */
    private function mergedOtherPackages(array $domainResults, array $domains): array
    {
        $packages = [];

        foreach ($domains as $domain) {
            foreach ((array) ($domainResults[$domain]['packages'] ?? []) as $package) {
                $package = (array) $package;

                if (in_array((string) ($package['recommended_phase'] ?? 'none'), ['cleanup_deleted', 'upsert_present'], true)) {
                    continue;
                }

                $packages[] = $this->annotatePackage($domain, $package);
            }
        }

        return $packages;
    }

    /**
     * @param  array<string, array<string, mixed>>  $domainResults
     * @return array<string, mixed>
     */
    private function normalizedDomainResults(array $domainResults): array
    {
        return [
            'v3' => $domainResults['v3'] ?? null,
            'page-v3' => $domainResults['page-v3'] ?? null,
        ];
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, array<string, mixed>>  $domainResults
     * @return list<array<string, mixed>>
     */
    private function resolvedRootsFromDomainResults(array $domains, array $domainResults): array
    {
        return array_values(array_map(function (string $domain) use ($domainResults): array {
            $scope = (array) ($domainResults[$domain]['scope'] ?? []);

            return [
                'domain' => $domain,
                'resolved_root_absolute_path' => $scope['resolved_root_absolute_path'] ?? null,
                'resolved_root_relative_path' => $scope['resolved_root_relative_path'] ?? $this->rootRelativePath($domain),
                'single_package' => (bool) ($scope['single_package'] ?? false),
            ];
        }, $domains));
    }

    /**
     * @param  list<string>  $domains
     * @param  array<string, array<string, mixed>>  $domainResults
     * @param  array<string, string>  $requestedBaseRefsByDomain
     * @return array<string, string|null>
     */
    private function effectiveBaseRefsFromDomainResults(
        array $domains,
        array $domainResults,
        array $requestedBaseRefsByDomain = [],
        string $fallbackBase = ''
    ): array {
        $baseRefs = [];

        foreach ($domains as $domain) {
            $domainBase = trim((string) ($domainResults[$domain]['diff']['base'] ?? ''));
            $requestedBase = trim((string) ($requestedBaseRefsByDomain[$domain] ?? $fallbackBase));

            $baseRefs[$domain] = $domainBase !== ''
                ? $domainBase
                : ($requestedBase !== '' ? $requestedBase : null);
        }

        return $baseRefs;
    }

    /**
     * @return V3ChangedPackagesPlanService|PageV3ChangedPackagesPlanService
     */
    private function plannerForDomain(string $domain): V3ChangedPackagesPlanService|PageV3ChangedPackagesPlanService
    {
        return match ($domain) {
            'v3' => $this->v3ChangedPackagesPlanService,
            'page-v3' => $this->pageV3ChangedPackagesPlanService,
            default => throw new RuntimeException(sprintf('Unsupported content domain [%s].', $domain)),
        };
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderMarkdownReport(array $result): string
    {
        $lines = [
            '# Changed Content Plan',
            '',
            '- Diff Mode: `' . (string) ($result['diff']['mode'] ?? 'working_tree') . '`',
            '- Base: `' . (string) (($result['diff']['base'] ?? null) ?: '') . '`',
            '- Base Refs By Domain: `' . json_encode((array) ($result['diff']['base_refs_by_domain'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '`',
            '- Head: `' . (string) (($result['diff']['head'] ?? null) ?: '') . '`',
            '- Include Untracked: `' . (((bool) ($result['diff']['include_untracked'] ?? false)) ? 'true' : 'false') . '`',
            '- Domains: `' . implode(', ', (array) ($result['scope']['domains'] ?? [])) . '`',
            '',
            '## Summary',
            '',
            '- Changed Packages: ' . (int) ($result['summary']['changed_packages'] ?? 0),
            '- Deleted Cleanup Candidates: ' . (int) ($result['summary']['deleted_cleanup_candidates'] ?? 0),
            '- Seed Candidates: ' . (int) ($result['summary']['seed_candidates'] ?? 0),
            '- Refresh Candidates: ' . (int) ($result['summary']['refresh_candidates'] ?? 0),
            '- Skipped: ' . (int) ($result['summary']['skipped'] ?? 0),
            '- Blocked: ' . (int) ($result['summary']['blocked'] ?? 0),
            '- Warnings: ' . (int) ($result['summary']['warnings'] ?? 0),
            '',
            '## Deleted Packages Cleanup Phase',
            '',
        ];

        $cleanupPackages = (array) ($result['phases']['cleanup_deleted'] ?? []);

        if ($cleanupPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($cleanupPackages as $package) {
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `' . $this->packagePath((array) $package) . '`'
                    . ' | type=`' . (string) ($package['package_type'] ?? 'unknown') . '`'
                    . ' | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`';
            }
        }

        $lines[] = '';
        $lines[] = '## Current Packages Upsert Phase';
        $lines[] = '';

        $upsertPackages = (array) ($result['phases']['upsert_present'] ?? []);

        if ($upsertPackages === []) {
            $lines[] = '- None.';
        } else {
            foreach ($upsertPackages as $package) {
                $lines[] = '- [' . (string) ($package['domain'] ?? 'unknown') . '] `' . $this->packagePath((array) $package) . '`'
                    . ' | type=`' . (string) ($package['package_type'] ?? 'unknown') . '`'
                    . ' | action=`' . (string) ($package['recommended_action'] ?? 'skip') . '`'
                    . ' | release=`' . (string) ($package['release_check']['status'] ?? 'skipped') . '`';
            }
        }

        if (is_array($result['error'] ?? null)) {
            $lines[] = '';
            $lines[] = '## Error';
            $lines[] = '';
            $lines[] = '- Stage: `' . (string) ($result['error']['stage'] ?? 'planning') . '`';
            $lines[] = '- Message: ' . (string) ($result['error']['message'] ?? 'Unknown error.');
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
