<?php

namespace App\Support\PackageSeed;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

abstract class AbstractCrossDomainChangedContentService
{
    /**
     * @return list<string>
     */
    protected function supportedDomains(): array
    {
        return ['v3', 'page-v3'];
    }

    protected function rootRelativePath(string $domain): string
    {
        return match ($domain) {
            'v3' => 'database/seeders/V3',
            'page-v3' => 'database/seeders/Page_V3',
            default => throw new RuntimeException(sprintf('Unsupported content domain [%s].', $domain)),
        };
    }

    protected function domainLabel(string $domain): string
    {
        return match ($domain) {
            'v3' => 'V3',
            'page-v3' => 'Page_V3',
            default => $domain,
        };
    }

    /**
     * @return list<string>
     */
    protected function normalizeDomainsOption(string|array|null $value): array
    {
        $tokens = is_array($value)
            ? $value
            : explode(',', (string) ($value ?? ''));

        $domains = collect($tokens)
            ->map(static fn ($token): string => strtolower(trim((string) $token)))
            ->map(static fn (string $domain): string => str_replace('_', '-', $domain))
            ->filter()
            ->values()
            ->all();

        if ($domains === []) {
            return $this->supportedDomains();
        }

        $invalid = array_values(array_filter(
            $domains,
            fn (string $domain): bool => ! in_array($domain, $this->supportedDomains(), true)
        ));

        if ($invalid !== []) {
            throw new RuntimeException(sprintf(
                'Unsupported --domains value(s): %s. Supported domains: v3, page-v3.',
                implode(', ', $invalid)
            ));
        }

        return array_values(array_unique($domains));
    }

    /**
     * @param  array<string, string>|null  $value
     * @return array<string, string>
     */
    protected function normalizeBaseRefsByDomain(array|null $value): array
    {
        if ($value === null) {
            return [];
        }

        $normalizedRefs = [];

        foreach ($value as $domain => $ref) {
            $normalizedDomain = str_replace('_', '-', strtolower(trim((string) $domain)));

            if (! in_array($normalizedDomain, $this->supportedDomains(), true)) {
                throw new RuntimeException(sprintf(
                    'Unsupported base_refs_by_domain key [%s]. Supported domains: v3, page-v3.',
                    $domain
                ));
            }

            $normalizedRef = trim((string) $ref);

            if ($normalizedRef === '') {
                continue;
            }

            $normalizedRefs[$normalizedDomain] = $normalizedRef;
        }

        return $normalizedRefs;
    }

    /**
     * @param  array<string, string>  $baseRefsByDomain
     */
    protected function effectiveBaseRefForDomain(string $domain, string $base, array $baseRefsByDomain): string
    {
        return trim((string) ($baseRefsByDomain[$domain] ?? $base));
    }

    /**
     * @param  list<string>  $requestedDomains
     * @return array<string, mixed>
     */
    protected function resolveUnifiedScope(?string $targetInput, array $requestedDomains, bool $domainsExplicit = false): array
    {
        $normalizedTarget = trim((string) ($targetInput ?? ''));

        if ($normalizedTarget === '') {
            return [
                'input' => null,
                'domains' => $requestedDomains,
                'resolved_roots' => array_values(array_map(function (string $domain): array {
                    $rootRelativePath = $this->rootRelativePath($domain);

                    return [
                        'domain' => $domain,
                        'resolved_root_absolute_path' => base_path($rootRelativePath),
                        'resolved_root_relative_path' => $rootRelativePath,
                        'single_package' => false,
                    ];
                }, $requestedDomains)),
                'single_package' => false,
            ];
        }

        $absoluteTargetPath = $this->resolveAbsolutePathAllowMissing($normalizedTarget);
        $matchingDomains = array_values(array_filter(
            $this->supportedDomains(),
            fn (string $domain): bool => $this->pathWithinRoot(
                $absoluteTargetPath,
                $this->resolveAbsolutePathAllowMissing($this->rootRelativePath($domain))
            )
        ));

        if ($matchingDomains === []) {
            throw new RuntimeException(
                'Target must resolve inside database/seeders/V3 or database/seeders/Page_V3.'
            );
        }

        $detectedDomain = $matchingDomains[0];

        if ($domainsExplicit && $requestedDomains !== [$detectedDomain]) {
            throw new RuntimeException(sprintf(
                'Target resolves inside %s, but --domains requested [%s].',
                $this->domainLabel($detectedDomain),
                implode(', ', $requestedDomains)
            ));
        }

        return [
            'input' => $normalizedTarget,
            'domains' => [$detectedDomain],
            'resolved_roots' => [],
            'single_package' => false,
        ];
    }

    protected function resolveAbsolutePathAllowMissing(string $input): string
    {
        $candidatePath = $this->isAbsolutePath($input)
            ? $input
            : base_path($input);
        $normalizedCandidatePath = $this->normalizePath($candidatePath);

        if (File::exists($normalizedCandidatePath)) {
            $realPath = realpath($normalizedCandidatePath);

            return $this->normalizePath($realPath !== false ? $realPath : $normalizedCandidatePath);
        }

        return $normalizedCandidatePath;
    }

    protected function normalizePath(string $path): string
    {
        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path));

        if ($normalized === '') {
            return $normalized;
        }

        $prefix = '';

        if (preg_match('/^[A-Za-z]:\\\\/', $normalized) === 1) {
            $prefix = substr($normalized, 0, 3);
            $normalized = substr($normalized, 3);
        } elseif (str_starts_with($normalized, DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR)) {
            $prefix = DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR;
            $normalized = ltrim($normalized, DIRECTORY_SEPARATOR);
        } elseif (str_starts_with($normalized, DIRECTORY_SEPARATOR)) {
            $prefix = DIRECTORY_SEPARATOR;
            $normalized = ltrim($normalized, DIRECTORY_SEPARATOR);
        }

        $segments = [];

        foreach (explode(DIRECTORY_SEPARATOR, $normalized) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);

                continue;
            }

            $segments[] = $segment;
        }

        $collapsed = implode(DIRECTORY_SEPARATOR, $segments);

        return $prefix . $collapsed;
    }

    protected function isAbsolutePath(string $path): bool
    {
        return preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1
            || str_starts_with($path, '/')
            || str_starts_with($path, '\\\\');
    }

    protected function pathWithinRoot(string $path, string $root): bool
    {
        $normalizedPath = strtolower(rtrim($this->normalizePath($path), DIRECTORY_SEPARATOR));
        $normalizedRoot = strtolower(rtrim($this->normalizePath($root), DIRECTORY_SEPARATOR));

        return $normalizedPath === $normalizedRoot
            || str_starts_with($normalizedPath, $normalizedRoot . DIRECTORY_SEPARATOR);
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function packagePath(array $package): string
    {
        return (string) (($package['current_relative_path'] ?? null)
            ?: ($package['historical_relative_path'] ?? null)
            ?: ($package['relative_path'] ?? null)
            ?: ($package['package_key'] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function packageKey(array $package): string
    {
        return (string) ($package['package_key'] ?? $this->packagePath($package));
    }

    /**
     * @param  array<string, mixed>  $package
     */
    protected function packageHasPlannerWarning(array $package): bool
    {
        $releaseStatus = (string) ($package['release_check']['status'] ?? 'skipped');

        return ((array) ($package['warnings'] ?? [])) !== []
            || in_array($releaseStatus, ['warn', 'fail'], true);
    }

    /**
     * @param  list<array<string, mixed>>  $packages
     * @return array<string, int>
     */
    protected function buildMergedSummary(array $packages): array
    {
        return [
            'changed_packages' => count($packages),
            'seed_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'seed'
            )),
            'refresh_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'refresh'
            )),
            'deleted_cleanup_candidates' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'unseed_deleted'
            )),
            'skipped' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'skip'
            )),
            'blocked' => count(array_filter(
                $packages,
                static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked'
            )),
            'warnings' => count(array_filter(
                $packages,
                fn (array $package): bool => $this->packageHasPlannerWarning($package)
            )),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $packages
     * @return array<string, mixed>|null
     */
    protected function strictPlannerFailure(array $packages, bool $strict): ?array
    {
        if (! $strict) {
            return null;
        }

        $blockedPackages = array_values(array_filter(
            $packages,
            static fn (array $package): bool => ($package['recommended_action'] ?? null) === 'blocked'
        ));

        if ($blockedPackages !== []) {
            return [
                'stage' => 'planning',
                'reason' => 'blocked_packages',
                'message' => 'Changed-content planner found blocked packages and --strict is enabled.',
                'packages' => array_values(array_map(
                    fn (array $package): string => $this->packagePath($package),
                    $blockedPackages
                )),
            ];
        }

        $warningPackages = array_values(array_filter(
            $packages,
            fn (array $package): bool => $this->packageHasPlannerWarning($package)
        ));

        if ($warningPackages !== []) {
            return [
                'stage' => 'planning',
                'reason' => 'warnings_are_fatal',
                'message' => 'Changed-content planner returned warnings and --strict is enabled.',
                'packages' => array_values(array_map(
                    fn (array $package): string => $this->packagePath($package),
                    $warningPackages
                )),
            ];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $package
     * @return array<string, mixed>
     */
    protected function annotatePackage(string $domain, array $package): array
    {
        $package['domain'] = $domain;
        $package['relative_path'] = $this->packagePath($package);

        return $package;
    }
}
