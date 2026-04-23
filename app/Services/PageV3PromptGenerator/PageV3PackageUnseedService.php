<?php

namespace App\Services\PageV3PromptGenerator;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\TextBlock;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Services\TheoryPagePromptLinkedTestsService;
use App\Support\Database\JsonPageDefinitionIndex;
use App\Support\Database\JsonPageRuntimeSeeder;
use App\Support\PackageSeed\AbstractJsonPackageUnseedService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class PageV3PackageUnseedService extends AbstractJsonPackageUnseedService
{
    public function __construct(
        private readonly SeedRunsService $seedRunsService,
        private readonly TheoryPagePromptLinkedTestsService $theoryPagePromptLinkedTestsService,
        private readonly JsonPageDefinitionIndex $definitionIndex,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function run(string $targetInput, array $options = []): array
    {
        $resolvedOptions = $this->normalizeUnseedOptions($options);
        $target = ['input' => trim($targetInput)];
        $result = $this->resultTemplate($target, $resolvedOptions);

        try {
            $target = $this->resolvePackageTarget($targetInput);
            $expectedSeederClass = $this->expectedSeederClass($target);
            $definition = $this->readDefinition((string) $target['definition_absolute_path']);
            $resolvedSeederClass = $this->resolveSeederClass($definition, $expectedSeederClass);
            $target['resolved_seeder_class'] = $resolvedSeederClass;
            $result = $this->resultTemplate($target, $resolvedOptions);
            $result['definition_summary'] = $this->definitionSummary($definition, $target, $resolvedSeederClass);

            $context = $this->buildContext(
                $resolvedSeederClass,
                $this->expandAdditionalCleanupClasses((array) ($options['additional_cleanup_classes'] ?? []))
            );
            $result['ownership'] = $context['ownership'];
            $result['impact']['warnings'] = $context['warnings'];
            $result['impact']['counts'] = $context['impact_counts'];

            if ($context['guard_error'] !== null) {
                $result['error'] = $context['guard_error'];

                return $result;
            }

            if (! $resolvedOptions['dry_run'] && ! $resolvedOptions['force']) {
                $result['error'] = $this->forceRequiredError();

                return $result;
            }

            if ($resolvedOptions['strict'] && $context['warnings'] !== []) {
                $result['error'] = $this->strictWarningFailure($context['warnings']);

                return $result;
            }

            if (! $context['ownership']['package_present_in_db'] && ! $context['ownership']['seed_run_present']) {
                return $result;
            }

            $execution = $this->runDeleteOperation(
                fn (): array => $this->executeUnseed(
                    $resolvedSeederClass,
                    $context['cleanup_classes'],
                    $resolvedOptions['dry_run']
                ),
                $resolvedOptions['dry_run']
            );

            $result['impact']['counts'] = $this->filterZeroCounts((array) ($execution['counts'] ?? []));
            $result['result'] = [
                'deleted' => (bool) ($execution['deleted'] ?? false),
                'rolled_back' => $resolvedOptions['dry_run'],
                'seed_run_removed' => (bool) ($execution['seed_run_removed'] ?? false),
            ];
        } catch (Throwable $exception) {
            $result['error'] = [
                'stage' => $target === ['input' => trim($targetInput)] ? 'target_resolution' : 'unseed',
                'message' => $exception->getMessage(),
                'exception_class' => $exception::class,
            ];
        }

        return $result;
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/Page_V3';
    }

    protected function reportDirectory(): string
    {
        return 'package-unseed-reports/page-v3';
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
    protected function readDefinition(string $definitionAbsolutePath): array
    {
        return (new JsonPageRuntimeSeeder($definitionAbsolutePath))->readDefinition();
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

        return [
            'content_type' => $contentType,
            'slug' => $slug !== '' ? $slug : null,
            'title' => $title !== '' ? $title : null,
            'type' => $this->nullableString($definition['type'] ?? data_get($config, 'type')),
            'category_slug' => $categorySlug !== '' ? $categorySlug : null,
            'category_parent_slug' => $parentSlug !== '' ? $parentSlug : null,
            'block_count' => count($blocks),
            'has_subtitle_block' => ! empty($config['subtitle_html']),
            'resolved_seeder_class' => $resolvedSeederClass,
            'definition_relative_path' => (string) ($target['definition_relative_path'] ?? ''),
        ];
    }

    /**
     * @param  list<string>  $additionalCleanupClasses
     * @return array{
     *   cleanup_classes: list<string>,
     *   ownership: array<string, bool>,
     *   warnings: list<string>,
     *   impact_counts: array<string, int>,
     *   guard_error: array<string, mixed>|null
     * }
     */
    protected function buildContext(
        string $resolvedSeederClass,
        array $additionalCleanupClasses = [],
    ): array
    {
        $cleanupClasses = collect([$resolvedSeederClass])
            ->merge($this->seedRunsService->relatedLocalizationClassesForTargetSeeder(
                $resolvedSeederClass,
                'page_localizations'
            ))
            ->merge($additionalCleanupClasses)
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $pageIds = $this->packagePageIds($resolvedSeederClass);
        $categoryIds = $this->packageCategoryIds($resolvedSeederClass);
        $pageCount = count($pageIds);
        $categoryCount = count($categoryIds);
        $blockCount = $this->directPackageBlockCount($cleanupClasses);
        $seedRunPresent = $this->seedRunPresent($resolvedSeederClass);
        $packagePresent = $pageCount > 0 || $categoryCount > 0 || $blockCount > 0;
        $warnings = [];

        if (! $seedRunPresent) {
            $warnings[] = 'Canonical seed_runs record is missing for the resolved seeder class.';
        }

        if (! $packagePresent) {
            $warnings[] = 'Package-owned database content is already absent.';
        }

        return [
            'cleanup_classes' => $cleanupClasses,
            'ownership' => [
                'seed_run_present' => $seedRunPresent,
                'package_present_in_db' => $packagePresent,
            ],
            'warnings' => array_values(array_unique(array_filter($warnings))),
            'impact_counts' => $this->filterZeroCounts([
                'Page' => $pageCount,
                'PageCategory' => $categoryCount,
                'TextBlock' => $blockCount,
            ]),
            'guard_error' => $this->dependencyGuardError(
                $resolvedSeederClass,
                $cleanupClasses,
                $pageIds,
                $categoryIds
            ),
        ];
    }

    /**
     * @param  list<string>  $classNames
     * @return list<string>
     */
    protected function expandAdditionalCleanupClasses(array $classNames): array
    {
        return collect($classNames)
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->flatMap(function (string $className): array {
                return array_merge(
                    [$className],
                    $this->seedRunsService->relatedLocalizationClassesForTargetSeeder($className, 'page_localizations')
                        ->all()
                );
            })
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $cleanupClasses
     * @param  list<int>  $pageIds
     * @param  list<int>  $categoryIds
     * @return array<string, mixed>|null
     */
    protected function dependencyGuardError(
        string $resolvedSeederClass,
        array $cleanupClasses,
        array $pageIds,
        array $categoryIds,
    ): ?array {
        $externalBlocks = $this->externalBlocksBoundToPages($cleanupClasses, $pageIds);

        if ($externalBlocks->isNotEmpty()) {
            return [
                'stage' => 'dependency_guard',
                'reason' => 'external_page_blocks',
                'message' => 'Package pages still contain text blocks owned outside the resolved package cleanup set.',
                'external_blocks' => $externalBlocks->take(6)->values()->all(),
            ];
        }

        $candidateBlockUuids = $this->candidateBlockUuids($cleanupClasses, $pageIds, $categoryIds);
        $referencingQuestions = $this->questionsReferencingBlockUuids($candidateBlockUuids);

        if ($referencingQuestions->isNotEmpty()) {
            return [
                'stage' => 'dependency_guard',
                'reason' => 'questions_reference_package_blocks',
                'message' => 'Questions still reference text blocks owned by the resolved package.',
                'referencing_questions' => $referencingQuestions->take(6)->values()->all(),
            ];
        }

        $linkedTests = $this->linkedTestsForPackagePages($resolvedSeederClass);

        if ($linkedTests->isNotEmpty()) {
            return [
                'stage' => 'dependency_guard',
                'reason' => 'tests_reference_package_pages',
                'message' => 'Saved grammar tests still reference theory pages owned by the resolved package.',
                'linked_tests' => $linkedTests->take(6)->values()->all(),
            ];
        }

        $externalPages = $this->externalPagesUsingCategories($cleanupClasses, $categoryIds);

        if ($externalPages->isNotEmpty()) {
            return [
                'stage' => 'dependency_guard',
                'reason' => 'external_pages_use_package_categories',
                'message' => 'Other pages still use categories owned by the resolved package.',
                'external_pages' => $externalPages->take(6)->values()->all(),
            ];
        }

        $externalChildren = $this->externalChildCategories($cleanupClasses, $categoryIds);

        if ($externalChildren->isNotEmpty()) {
            return [
                'stage' => 'dependency_guard',
                'reason' => 'external_child_categories',
                'message' => 'Other page categories still depend on categories owned by the resolved package.',
                'external_child_categories' => $externalChildren->take(6)->values()->all(),
            ];
        }

        return null;
    }

    /**
     * @param  list<string>  $cleanupClasses
     * @param  list<int>  $pageIds
     * @return Collection<int, array<string, mixed>>
     */
    protected function externalBlocksBoundToPages(array $cleanupClasses, array $pageIds): Collection
    {
        if ($pageIds === [] || ! Schema::hasTable('text_blocks')) {
            return collect();
        }

        return TextBlock::query()
            ->whereIn('page_id', $pageIds)
            ->where(function ($query) use ($cleanupClasses): void {
                $query->whereNull('seeder');

                if ($cleanupClasses !== []) {
                    $query->orWhereNotIn('seeder', $cleanupClasses);
                }
            })
            ->orderBy('id')
            ->get(['id', 'uuid', 'page_id', 'seeder'])
            ->map(fn (TextBlock $block): array => [
                'id' => (int) $block->getKey(),
                'uuid' => (string) ($block->uuid ?? ''),
                'page_id' => (int) ($block->page_id ?? 0),
                'seeder' => (string) ($block->seeder ?? ''),
            ]);
    }

    /**
     * @param  list<string>  $cleanupClasses
     * @param  list<int>  $pageIds
     * @param  list<int>  $categoryIds
     * @return list<string>
     */
    protected function candidateBlockUuids(array $cleanupClasses, array $pageIds, array $categoryIds): array
    {
        if (! Schema::hasTable('text_blocks')) {
            return [];
        }

        $query = TextBlock::query()
            ->select('uuid')
            ->where(function ($query) use ($cleanupClasses, $pageIds, $categoryIds): void {
                if ($cleanupClasses !== []) {
                    $query->whereIn('seeder', $cleanupClasses);
                }

                if ($pageIds !== []) {
                    $query->orWhereIn('page_id', $pageIds);
                }

                if ($categoryIds !== []) {
                    $query->orWhere(function ($categoryQuery) use ($categoryIds): void {
                        $categoryQuery
                            ->whereIn('page_category_id', $categoryIds)
                            ->whereNull('page_id');
                    });
                }
            });

        return $query
            ->pluck('uuid')
            ->filter(fn ($uuid) => is_string($uuid) && trim($uuid) !== '')
            ->map(fn ($uuid) => trim((string) $uuid))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $blockUuids
     * @return Collection<int, array<string, mixed>>
     */
    protected function questionsReferencingBlockUuids(array $blockUuids): Collection
    {
        if (
            $blockUuids === []
            || ! Schema::hasTable('questions')
            || ! Schema::hasColumn('questions', 'theory_text_block_uuid')
        ) {
            return collect();
        }

        return Question::query()
            ->whereIn('theory_text_block_uuid', $blockUuids)
            ->orderBy('id')
            ->get(['id', 'uuid', 'question', 'seeder', 'theory_text_block_uuid'])
            ->map(fn (Question $question): array => [
                'id' => (int) $question->getKey(),
                'uuid' => (string) ($question->uuid ?? ''),
                'seeder' => (string) ($question->seeder ?? ''),
                'theory_text_block_uuid' => (string) ($question->theory_text_block_uuid ?? ''),
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function linkedTestsForPackagePages(string $resolvedSeederClass): Collection
    {
        if (
            ! Schema::hasTable('pages')
            || ! Schema::hasTable('saved_grammar_tests')
            || ! Schema::hasTable('saved_grammar_test_questions')
        ) {
            return collect();
        }

        return Page::query()
            ->where('seeder', $resolvedSeederClass)
            ->orderBy('id')
            ->get()
            ->flatMap(function (Page $page): Collection {
                return $this->theoryPagePromptLinkedTestsService
                    ->findForPage($page)
                    ->map(function ($test) use ($page): array {
                        return [
                            'page_id' => (int) $page->getKey(),
                            'page_slug' => (string) ($page->slug ?? ''),
                            'test_slug' => (string) ($test->slug ?? ''),
                            'test_name' => (string) ($test->name ?? $test->slug ?? ''),
                        ];
                    });
            })
            ->unique(fn (array $item) => ($item['page_id'] ?? 0) . '|' . ($item['test_slug'] ?? ''))
            ->values();
    }

    /**
     * @param  list<string>  $cleanupClasses
     * @param  list<int>  $categoryIds
     * @return Collection<int, array<string, mixed>>
     */
    protected function externalPagesUsingCategories(array $cleanupClasses, array $categoryIds): Collection
    {
        if ($categoryIds === [] || ! Schema::hasTable('pages')) {
            return collect();
        }

        return Page::query()
            ->whereIn('page_category_id', $categoryIds)
            ->where(function ($query) use ($cleanupClasses): void {
                $query->whereNull('seeder');

                if ($cleanupClasses !== []) {
                    $query->orWhereNotIn('seeder', $cleanupClasses);
                }
            })
            ->orderBy('id')
            ->get(['id', 'slug', 'seeder', 'page_category_id'])
            ->map(fn (Page $page): array => [
                'id' => (int) $page->getKey(),
                'slug' => (string) ($page->slug ?? ''),
                'seeder' => (string) ($page->seeder ?? ''),
                'page_category_id' => (int) ($page->page_category_id ?? 0),
            ]);
    }

    /**
     * @param  list<string>  $cleanupClasses
     * @param  list<int>  $categoryIds
     * @return Collection<int, array<string, mixed>>
     */
    protected function externalChildCategories(array $cleanupClasses, array $categoryIds): Collection
    {
        if ($categoryIds === [] || ! Schema::hasTable('page_categories')) {
            return collect();
        }

        return PageCategory::query()
            ->whereIn('parent_id', $categoryIds)
            ->where(function ($query) use ($cleanupClasses): void {
                $query->whereNull('seeder');

                if ($cleanupClasses !== []) {
                    $query->orWhereNotIn('seeder', $cleanupClasses);
                }
            })
            ->orderBy('id')
            ->get(['id', 'slug', 'seeder', 'parent_id'])
            ->map(fn (PageCategory $category): array => [
                'id' => (int) $category->getKey(),
                'slug' => (string) ($category->slug ?? ''),
                'seeder' => (string) ($category->seeder ?? ''),
                'parent_id' => (int) ($category->parent_id ?? 0),
            ]);
    }

    /**
     * @param  list<string>  $cleanupClasses
     * @return array<string, mixed>
     */
    protected function executeUnseed(
        string $resolvedSeederClass,
        array $cleanupClasses,
        bool $dryRun,
    ): array {
        $deletionStats = $this->seedRunsService->deleteSeedDataForClasses($cleanupClasses);
        $counts = $this->filterZeroCounts([
            'TextBlock' => (int) ($deletionStats['blocks_deleted'] ?? 0),
            'Page' => (int) ($deletionStats['pages_deleted'] ?? 0),
            'PageCategory' => (int) ($deletionStats['categories_deleted'] ?? 0),
        ]);
        $seedRunRemoved = false;

        if (! $dryRun) {
            $seedRunRemoved = $this->removeSeedRunRecord($resolvedSeederClass);
        }

        return [
            'counts' => $counts,
            'deleted' => $counts !== [],
            'seed_run_removed' => $seedRunRemoved,
        ];
    }

    /**
     * @return list<int>
     */
    protected function packagePageIds(string $resolvedSeederClass): array
    {
        if (! Schema::hasTable('pages') || ! Schema::hasColumn('pages', 'seeder')) {
            return [];
        }

        return Page::query()
            ->where('seeder', $resolvedSeederClass)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    protected function packageCategoryIds(string $resolvedSeederClass): array
    {
        if (! Schema::hasTable('page_categories') || ! Schema::hasColumn('page_categories', 'seeder')) {
            return [];
        }

        return PageCategory::query()
            ->where('seeder', $resolvedSeederClass)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $cleanupClasses
     */
    protected function directPackageBlockCount(array $cleanupClasses): int
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

    protected function nullableString(mixed $value): ?string
    {
        $resolved = trim((string) $value);

        return $resolved !== '' ? $resolved : null;
    }
}
