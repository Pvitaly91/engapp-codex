<?php

namespace App\Services\PageV3PromptGenerator;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\TextBlock;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Support\Database\JsonPageDefinitionIndex;
use App\Support\Database\JsonPageRuntimeSeeder;
use App\Support\PackageSeed\AbstractJsonPackageRefreshService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PageV3PackageRefreshService extends AbstractJsonPackageRefreshService
{
    public function __construct(
        private readonly PageV3ReleaseCheckService $releaseCheckService,
        private readonly SeedRunsService $seedRunsService,
        private readonly JsonPageDefinitionIndex $definitionIndex,
    ) {}

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/Page_V3';
    }

    protected function reportDirectory(): string
    {
        return 'package-refresh-reports/page-v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['en', 'pl'];
    }

    /**
     * @param  array<string, mixed>  $target
     */
    protected function expectedSeederClass(array $target): string
    {
        $relative = Str::after(
            str_replace('\\', '/', (string) $target['package_root_relative_path']),
            'database/seeders/Page_V3/'
        );
        $segments = array_values(array_filter(explode('/', $relative)));
        $className = (string) array_pop($segments);
        $namespace = implode('\\', $segments);

        return 'Database\\Seeders\\Page_V3\\' . ($namespace !== '' ? $namespace . '\\' : '') . $className;
    }

    /**
     * @return array<string, mixed>
     */
    protected function releaseCheckReport(string $targetInput, string $profile): array
    {
        return $this->releaseCheckService->run($targetInput, $profile);
    }

    /**
     * @return array<string, mixed>
     */
    protected function readDefinition(string $definitionAbsolutePath): array
    {
        return (new JsonPageRuntimeSeeder($definitionAbsolutePath))->readDefinition();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    protected function ownershipContext(
        array $definition,
        array $target,
        string $resolvedSeederClass,
    ): array {
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
        $pageCount = $this->packagePageCount($resolvedSeederClass);
        $categoryCount = $this->packageCategoryCount($resolvedSeederClass);
        $blockCount = $this->packageBlockCount($cleanupClasses);
        $seedRunPresent = $this->seedRunPresent($resolvedSeederClass);
        $packagePresent = $pageCount > 0 || $categoryCount > 0 || $blockCount > 0;
        $warnings = [];

        if (! $seedRunPresent) {
            $warnings[] = 'Canonical seed_runs record is missing for the resolved seeder class.';
        }

        if (! $packagePresent) {
            $warnings[] = 'Package-owned database content is absent; refresh will fall back to seed-only behavior.';
        }

        return [
            'seed_run_present' => $seedRunPresent,
            'package_present_in_db' => $packagePresent,
            'warnings' => array_values(array_unique(array_filter($warnings))),
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function executeAdminRefresh(string $resolvedSeederClass, array $options): array
    {
        return $this->seedRunsService->refreshSeedersByClassNames([$resolvedSeederClass], [
            'atomic' => true,
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'rollback_on_error' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    protected function definitionSummary(
        array $definition,
        array $target,
        string $resolvedSeederClass,
    ): array {
        $contentType = $this->definitionIndex->resolveContentType($definition);
        $config = $this->definitionIndex->resolveContentConfig($definition);
        $blocks = array_values(array_filter((array) ($config['blocks'] ?? []), 'is_array'));
        $slug = trim((string) ($definition['slug'] ?? Arr::get($definition, 'category.slug', '')));
        $title = trim((string) (
            $contentType === 'category'
                ? (Arr::get($definition, 'category.title') ?: Arr::get($config, 'title', ''))
                : Arr::get($config, 'title', '')
        ));
        $categorySlug = trim((string) (
            $contentType === 'category'
                ? Arr::get($definition, 'category.slug', $slug)
                : Arr::get($config, 'category.slug', '')
        ));
        $parentSlug = trim((string) Arr::get($definition, 'category.parent_slug', ''));

        return [
            'content_type' => $contentType,
            'slug' => $slug !== '' ? $slug : null,
            'title' => $title !== '' ? $title : null,
            'type' => $this->nullableString($definition['type'] ?? Arr::get($config, 'type')),
            'category_slug' => $categorySlug !== '' ? $categorySlug : null,
            'category_parent_slug' => $parentSlug !== '' ? $parentSlug : null,
            'block_count' => count($blocks),
            'has_subtitle_block' => ! empty($config['subtitle_html']),
            'resolved_seeder_class' => $resolvedSeederClass,
            'definition_relative_path' => (string) ($target['definition_relative_path'] ?? ''),
        ];
    }

    private function packagePageCount(string $resolvedSeederClass): int
    {
        if (! Schema::hasTable('pages') || ! Schema::hasColumn('pages', 'seeder')) {
            return 0;
        }

        return Page::query()
            ->where('seeder', $resolvedSeederClass)
            ->count();
    }

    private function packageCategoryCount(string $resolvedSeederClass): int
    {
        if (! Schema::hasTable('page_categories') || ! Schema::hasColumn('page_categories', 'seeder')) {
            return 0;
        }

        return PageCategory::query()
            ->where('seeder', $resolvedSeederClass)
            ->count();
    }

    /**
     * @param  list<string>  $cleanupClasses
     */
    private function packageBlockCount(array $cleanupClasses): int
    {
        if (
            $cleanupClasses === []
            || ! Schema::hasTable('text_blocks')
            || ! Schema::hasColumn('text_blocks', 'seeder')
        ) {
            return 0;
        }

        return TextBlock::query()
            ->whereIn('seeder', $cleanupClasses)
            ->count();
    }

    private function nullableString(mixed $value): ?string
    {
        $resolved = trim((string) $value);

        return $resolved !== '' ? $resolved : null;
    }
}
