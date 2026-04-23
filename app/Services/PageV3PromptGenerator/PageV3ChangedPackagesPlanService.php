<?php

namespace App\Services\PageV3PromptGenerator;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Support\Database\JsonPageDefinitionIndex;
use App\Support\PackageSeed\AbstractJsonPackageChangedPlanService;
use App\Support\PackageSeed\GitPackageDiffService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PageV3ChangedPackagesPlanService extends AbstractJsonPackageChangedPlanService
{
    public function __construct(
        GitPackageDiffService $gitPackageDiffService,
        private readonly PageV3FolderPlanService $folderPlanService,
        private readonly PageV3ReleaseCheckService $releaseCheckService,
        private readonly SeedRunsService $seedRunsService,
        private readonly JsonPageDefinitionIndex $definitionIndex,
    ) {
        parent::__construct($gitPackageDiffService);
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/Page_V3';
    }

    protected function reportDirectory(): string
    {
        return 'changed-package-plans/page-v3';
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
    protected function planCurrentPackage(string $targetInput): array
    {
        return $this->folderPlanService->run($targetInput, [
            'mode' => 'sync',
            'with_release_check' => false,
            'strict' => false,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function deletedPackageMetadata(string $packageRootRelativePath, string $historicalRef): array
    {
        $expectedSeederClass = $this->expectedSeederClass($packageRootRelativePath);
        $definitionPath = trim($packageRootRelativePath, '/') . '/definition.json';
        $definitionContents = $this->gitPackageDiffService->showFile($historicalRef, $definitionPath);

        if ($definitionContents === null) {
            return [
                'available' => false,
                'safe' => false,
                'resolved_seeder_class' => $expectedSeederClass,
                'package_type' => 'unknown',
                'summary' => [],
                'warnings' => ['Historical definition.json is unavailable for the deleted Page_V3 package.'],
                '_cleanup_classes' => [],
            ];
        }

        try {
            $definition = json_decode($definitionContents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            return [
                'available' => false,
                'safe' => false,
                'resolved_seeder_class' => $expectedSeederClass,
                'package_type' => 'unknown',
                'summary' => [],
                'warnings' => ['Historical definition.json contains invalid JSON: ' . $exception->getMessage()],
                '_cleanup_classes' => [],
            ];
        }

        if (! is_array($definition)) {
            return [
                'available' => false,
                'safe' => false,
                'resolved_seeder_class' => $expectedSeederClass,
                'package_type' => 'unknown',
                'summary' => [],
                'warnings' => ['Historical definition.json must decode to a JSON object.'],
                '_cleanup_classes' => [],
            ];
        }

        $contentType = $this->resolveHistoricalContentType($definition);
        $config = $this->resolveHistoricalContentConfig($definition, $contentType);
        $resolvedSeederClass = trim((string) data_get($definition, 'seeder.class', '')) ?: $expectedSeederClass;
        $cleanupClasses = collect([$resolvedSeederClass])
            ->merge($this->seedRunsService->relatedLocalizationClassesForTargetSeeder(
                $resolvedSeederClass,
                'page_localizations'
            ))
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $slug = trim((string) ($definition['slug'] ?? data_get($definition, 'category.slug', '')));
        $title = trim((string) (
            $contentType === 'category'
                ? (data_get($definition, 'category.title') ?: data_get($config, 'title', ''))
                : data_get($config, 'title', '')
        ));
        $categorySlug = trim((string) (
            $contentType === 'category'
                ? data_get($definition, 'category.slug', $slug)
                : data_get($config, 'category.slug', '')
        ));
        $parentSlug = trim((string) data_get($definition, 'category.parent_slug', ''));
        $safe = in_array($contentType, ['page', 'category'], true) && $resolvedSeederClass !== '';
        $warnings = [];

        if (! $safe) {
            $warnings[] = 'Historical definition metadata is insufficient to classify the deleted Page_V3 package safely.';
        }

        return [
            'available' => true,
            'safe' => $safe,
            'resolved_seeder_class' => $resolvedSeederClass,
            'package_type' => in_array($contentType, ['page', 'category'], true) ? $contentType : 'unknown',
            'summary' => [
                'content_type' => in_array($contentType, ['page', 'category'], true) ? $contentType : null,
                'slug' => $this->nullableString($slug),
                'title' => $this->nullableString($title),
                'category_slug' => $this->nullableString($categorySlug),
                'category_parent_slug' => $this->nullableString($parentSlug),
            ],
            'warnings' => $warnings,
            '_cleanup_classes' => $cleanupClasses,
        ];
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    protected function deletedPackageOwnership(array $metadata): array
    {
        $resolvedSeederClass = trim((string) ($metadata['resolved_seeder_class'] ?? ''));
        $cleanupClasses = collect((array) ($metadata['_cleanup_classes'] ?? []))
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $seedRunPresent = $resolvedSeederClass !== ''
            && Schema::hasTable('seed_runs')
            && DB::table('seed_runs')->where('class_name', $resolvedSeederClass)->exists();
        $pageCount = $resolvedSeederClass !== ''
            && Schema::hasTable('pages')
            && Schema::hasColumn('pages', 'seeder')
                ? Page::query()->where('seeder', $resolvedSeederClass)->count()
                : 0;
        $categoryCount = $resolvedSeederClass !== ''
            && Schema::hasTable('page_categories')
            && Schema::hasColumn('page_categories', 'seeder')
                ? PageCategory::query()->where('seeder', $resolvedSeederClass)->count()
                : 0;
        $blockCount = $cleanupClasses !== []
            && Schema::hasTable('text_blocks')
            && Schema::hasColumn('text_blocks', 'seeder')
                ? TextBlock::query()->whereIn('seeder', $cleanupClasses)->count()
                : 0;
        $packagePresent = $pageCount > 0 || $categoryCount > 0 || $blockCount > 0;
        $warnings = [];

        if ($seedRunPresent && ! $packagePresent) {
            $warnings[] = 'Canonical seed_runs record exists, but package-owned database content is absent.';
        }

        if (! $seedRunPresent && $packagePresent) {
            $warnings[] = 'Package-owned database content exists without a canonical seed_runs record.';
        }

        return [
            'seed_run_present' => $seedRunPresent,
            'package_present_in_db' => $packagePresent,
            'warnings' => array_values(array_unique(array_filter($warnings))),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function releaseCheckReport(string $targetInput, string $profile): array
    {
        return $this->releaseCheckService->run($targetInput, $profile);
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    protected function cleanupPhaseComparator(array $left, array $right): int
    {
        $leftWeight = $this->cleanupTypeWeight((string) ($left['package_type'] ?? 'unknown'));
        $rightWeight = $this->cleanupTypeWeight((string) ($right['package_type'] ?? 'unknown'));

        if ($leftWeight !== $rightWeight) {
            return $leftWeight <=> $rightWeight;
        }

        return strcmp($this->packageSortPath($left), $this->packageSortPath($right));
    }

    /**
     * @param  array<string, mixed>  $left
     * @param  array<string, mixed>  $right
     */
    protected function upsertPhaseComparator(array $left, array $right): int
    {
        $leftWeight = $this->upsertTypeWeight((string) ($left['package_type'] ?? 'unknown'));
        $rightWeight = $this->upsertTypeWeight((string) ($right['package_type'] ?? 'unknown'));

        if ($leftWeight !== $rightWeight) {
            return $leftWeight <=> $rightWeight;
        }

        return strcmp($this->packageSortPath($left), $this->packageSortPath($right));
    }

    private function expectedSeederClass(string $packageRootRelativePath): string
    {
        $relative = Str::after(str_replace('\\', '/', $packageRootRelativePath), 'database/seeders/Page_V3/');
        $segments = array_values(array_filter(explode('/', $relative)));
        $className = (string) array_pop($segments);
        $namespace = implode('\\', $segments);

        return 'Database\\Seeders\\Page_V3\\' . ($namespace !== '' ? $namespace . '\\' : '') . $className;
    }

    private function cleanupTypeWeight(string $packageType): int
    {
        return match ($packageType) {
            'page' => 0,
            'category' => 1,
            default => 2,
        };
    }

    private function upsertTypeWeight(string $packageType): int
    {
        return match ($packageType) {
            'category' => 0,
            'page' => 1,
            default => 2,
        };
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function resolveHistoricalContentType(array $definition): string
    {
        $contentType = $this->definitionIndex->resolveContentType($definition);

        if ($contentType === 'page'
            && ! array_key_exists('content_type', $definition)
            && ! is_array($definition['page'] ?? null)
            && is_array($definition['category'] ?? null)
        ) {
            return 'category';
        }

        return $contentType;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    private function resolveHistoricalContentConfig(array $definition, string $contentType): array
    {
        if ($contentType === 'category' && is_array($definition['category'] ?? null)) {
            return $definition['category'];
        }

        return $this->definitionIndex->resolveContentConfig($definition);
    }
}
