<?php

namespace App\Services\PageV3PromptGenerator;

use App\Support\Database\JsonPageDefinitionIndex;
use App\Support\PackageSeed\AbstractJsonPackageFolderPlanService;
use Illuminate\Support\Facades\File;

class PageV3FolderPlanService extends AbstractJsonPackageFolderPlanService
{
    public function __construct(
        private readonly PageV3PackageRefreshService $packageRefreshService,
        private readonly PageV3ReleaseCheckService $releaseCheckService,
        private readonly JsonPageDefinitionIndex $definitionIndex,
    ) {
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/Page_V3';
    }

    protected function reportDirectory(): string
    {
        return 'folder-plans/page-v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['en', 'pl'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function inspectPackageTarget(string $targetInput): array
    {
        return $this->packageRefreshService->inspectTarget($targetInput);
    }

    /**
     * @return array<string, mixed>
     */
    protected function releaseCheckReport(string $targetInput, string $profile): array
    {
        return $this->releaseCheckService->run($targetInput, $profile);
    }

    protected function seedCommandName(): string
    {
        return 'page-v3:seed-package';
    }

    protected function refreshCommandName(): string
    {
        return 'page-v3:refresh-package';
    }

    protected function unseedCommandName(): string
    {
        return 'page-v3:unseed-package';
    }

    protected function packageTypeWeight(string $packageType): int
    {
        return match ($packageType) {
            'category' => 0,
            'page' => 1,
            default => 2,
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @param  array<string, mixed>  $scope
     * @param  array<string, mixed>  $options
     * @return array<int, array<string, mixed>>
     */
    protected function augmentPackagesForScope(array $packages, array $scope, array $options): array
    {
        $providedCategorySlugs = collect($packages)
            ->filter(static fn (array $package): bool => ($package['package_type'] ?? null) === 'category')
            ->map(static fn (array $package): string => trim((string) (
                $package['_definition_summary']['slug']
                    ?? $package['_definition_summary']['category_slug']
                    ?? ''
            )))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $packages = array_values(array_map(function (array $package) use ($providedCategorySlugs): array {
            if (($package['package_type'] ?? null) !== 'page') {
                return $package;
            }

            $targetCategorySlug = trim((string) (($package['_definition_summary']['category_slug'] ?? null) ?: ''));

            if ($targetCategorySlug === '' || in_array($targetCategorySlug, $providedCategorySlugs, true)) {
                return $package;
            }

            $warnings = collect((array) ($package['warnings'] ?? []))
                ->push(sprintf(
                    'Page package references category slug [%s] outside the current scope; folder lifecycle will not expand scope.',
                    $targetCategorySlug
                ))
                ->unique()
                ->values()
                ->all();

            $package['warnings'] = $warnings;

            return $package;
        }, $packages));

        if (! in_array(($options['mode'] ?? null), ['destroy-files', 'destroy'], true)) {
            return $packages;
        }

        $inScopePagesByCategorySlug = collect($packages)
            ->filter(static fn (array $package): bool => ($package['package_type'] ?? null) === 'page')
            ->groupBy(static fn (array $package): string => trim((string) ($package['_definition_summary']['category_slug'] ?? '')));
        $inScopePackageLookup = collect($packages)
            ->pluck('relative_path')
            ->map(static fn ($value): string => trim((string) $value))
            ->filter()
            ->flip()
            ->all();

        return array_values(array_map(function (array $package) use ($inScopePagesByCategorySlug, $inScopePackageLookup, $scope): array {
            if (($package['package_type'] ?? null) !== 'category') {
                return $package;
            }

            $categorySlug = trim((string) (
                $package['_definition_summary']['slug']
                    ?? $package['_definition_summary']['category_slug']
                    ?? ''
            ));

            if ($categorySlug === '') {
                return $package;
            }

            $warnings = collect((array) ($package['warnings'] ?? []));
            $unsafeInScopePages = collect($inScopePagesByCategorySlug->get($categorySlug, collect()))
                ->filter(fn (array $page): bool => $this->pagePackageRemainsAfterDestructivePlan($page))
                ->pluck('relative_path')
                ->map(static fn ($path): string => trim((string) $path))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($unsafeInScopePages !== []) {
                $warnings->push(sprintf(
                        'Category package still has in-scope page dependents that are not eligible for destructive cleanup: %s.',
                        implode(', ', $unsafeInScopePages)
                    ));

                if (in_array(($package['recommended_action'] ?? null), ['destroy_files', 'destroy'], true)) {
                    $package['recommended_action'] = 'blocked';
                }
            }

            $outOfScopePages = $this->destroyModeSiblingPageDependents(
                $package,
                $categorySlug,
                $scope,
                $inScopePackageLookup
            );

            if ($outOfScopePages !== []) {
                $warnings->push(sprintf(
                    'Sibling Page_V3 packages outside the current scope still reference category slug [%s]: %s.',
                    $categorySlug,
                    implode(', ', $outOfScopePages)
                ));

                if (in_array(($package['recommended_action'] ?? null), ['destroy_files', 'destroy'], true)) {
                    $package['recommended_action'] = 'blocked';
                }
            }

            $package['warnings'] = $warnings
                ->unique()
                ->values()
                ->all();

            return $package;
        }, $packages));
    }

    /**
     * @param  array<int, array<string, mixed>>  $packages
     * @return array<int, array<string, mixed>>
     */
    protected function sortPackagesForUnseed(array $packages): array
    {
        usort($packages, function (array $left, array $right): int {
            $groupOrder = [
                'page' => 0,
                'category' => 1,
            ];

            $leftWeight = $groupOrder[(string) ($left['package_type'] ?? 'unknown')] ?? 99;
            $rightWeight = $groupOrder[(string) ($right['package_type'] ?? 'unknown')] ?? 99;

            if ($leftWeight !== $rightWeight) {
                return $leftWeight <=> $rightWeight;
            }

            return strcmp(
                (string) ($left['relative_path'] ?? ''),
                (string) ($right['relative_path'] ?? '')
            );
        });

        return $packages;
    }

    /**
     * @param  array<string, bool>  $inScopePackageLookup
     * @return list<string>
     */
    private function destroyModeSiblingPageDependents(
        array $categoryPackage,
        string $categorySlug,
        array $scope,
        array $inScopePackageLookup,
    ): array {
        $packageRootAbsolutePath = base_path(str_replace('/', DIRECTORY_SEPARATOR, (string) ($categoryPackage['relative_path'] ?? '')));
        $parentDirectory = dirname($packageRootAbsolutePath);

        if (! File::isDirectory($parentDirectory)) {
            return [];
        }

        return collect(File::files($parentDirectory))
            ->filter(static fn ($file): bool => strtolower($file->getExtension()) === 'php')
            ->map(function ($file) use ($categoryPackage, $categorySlug, $scope, $inScopePackageLookup): ?string {
                $className = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $categoryClassName = basename((string) ($categoryPackage['relative_path'] ?? ''));

                if ($className === '' || $className === $categoryClassName) {
                    return null;
                }

                $candidatePackageRoot = $this->normalizePath($file->getPath() . DIRECTORY_SEPARATOR . $className);
                $candidateRelativePath = trim((string) ($this->relativePath($candidatePackageRoot) ?? ''));

                if (
                    $candidateRelativePath === ''
                    || isset($inScopePackageLookup[$candidateRelativePath])
                    || $this->isPathWithinRoot($candidatePackageRoot, (string) ($scope['resolved_root_absolute_path'] ?? ''))
                ) {
                    return null;
                }

                $definitionPath = $this->normalizePath($candidatePackageRoot . '/definition.json');

                if (! File::exists($definitionPath)) {
                    return null;
                }

                try {
                    $definition = $this->definitionIndex->loadDefinitionFromFile($definitionPath);
                    $contentType = $this->definitionIndex->resolveContentType($definition);
                    $config = $this->definitionIndex->resolveContentConfig($definition);
                } catch (\Throwable) {
                    return null;
                }

                if ($contentType !== 'page') {
                    return null;
                }

                $referencedCategorySlug = trim((string) (
                    data_get($config, 'category.slug')
                    ?: data_get($definition, 'category.slug', '')
                ));

                return $referencedCategorySlug === $categorySlug
                    ? $candidateRelativePath
                    : null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $package
     */
    private function pagePackageRemainsAfterDestructivePlan(array $package): bool
    {
        if (($package['package_type'] ?? null) !== 'page') {
            return false;
        }

        return ! in_array((string) ($package['recommended_action'] ?? 'skip'), ['destroy_files', 'destroy', 'skip'], true);
    }
}
