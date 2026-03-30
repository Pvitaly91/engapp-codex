<?php

namespace App\Modules\SeedRunsV2\Services;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\TextBlock;
use App\Services\SeederTestTargetResolver;
use App\Services\QuestionDeletionService;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonPageSeeder;
use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder as GrammarPageSeederBase;
use Database\Seeders\Page_v2\Concerns\PageCategoryDescriptionSeeder as PageCategoryDescriptionSeederBase;
use Database\Seeders\QuestionSeeder as QuestionSeederBase;
use Illuminate\Database\Seeder as LaravelSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Service for managing seed runs and seeder operations.
 * Extracted from SeedRunController to be shared with Livewire components.
 */
class SeedRunsService
{
    private ?array $seederClassMap = null;

    public function __construct(
        private QuestionDeletionService $questionDeletionService,
        private SeederTestTargetResolver $seederTestTargetResolver,
        private JsonPageLocalizationManager $jsonPageLocalizationManager,
    )
    {
    }

    protected function isVirtualLocalizationSeeder(string $className): bool
    {
        return $this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className);
    }

    protected function virtualLocalizationType(string $className): ?string
    {
        return $this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)
            ? 'page_localizations'
            : null;
    }

    protected function virtualLocalizationClasses(): array
    {
        return collect($this->jsonPageLocalizationManager->virtualSeederClasses())
            ->unique()
            ->values()
            ->all();
    }

    protected function virtualLocalizationFilePath(string $className): ?string
    {
        return $this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)
            ? $this->jsonPageLocalizationManager->filePathForClass($className)
            : null;
    }

    protected function applyVirtualLocalizationSeeder(string $className): array
    {
        if (! $this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            throw new \RuntimeException(__('Localization seeder :class was not found.', ['class' => $className]));
        }

        return $this->jsonPageLocalizationManager->applyVirtualSeeder($className);
    }

    protected function removeVirtualLocalizationSeederData(string $className): array
    {
        if (! $this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return [];
        }

        return $this->jsonPageLocalizationManager->removeVirtualSeederData($className);
    }

    protected function logVirtualSeederRun(string $className): void
    {
        if (! Schema::hasTable('seed_runs')) {
            return;
        }

        DB::table('seed_runs')->updateOrInsert(
            ['class_name' => $className],
            ['ran_at' => now()]
        );
    }

    public function assembleSeedRunOverview(): array
    {
        $tableExists = Schema::hasTable('seed_runs');
        $executedSeeders = collect();
        $pendingSeeders = collect();
        $executedSeederHierarchy = collect();
        $recentSeedRunOrdinals = collect();
        $recentThreshold = now()->subDay();

        if (! $tableExists) {
            return [
                'tableExists' => false,
                'executedSeeders' => $executedSeeders,
                'pendingSeeders' => $pendingSeeders,
                'pendingSeederHierarchy' => collect(),
                'executedSeederHierarchy' => $executedSeederHierarchy,
                'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
            ];
        }

        $executedSeeders = DB::table('seed_runs')
            ->orderByDesc('ran_at')
            ->get()
            ->map(function ($seedRun) {
                // Parse Carbon for sorting/filtering, then pre-format for Livewire serialization
                $seedRun->ran_at = $seedRun->ran_at ? Carbon::parse($seedRun->ran_at) : null;
                $seedRun->ran_at_formatted = $seedRun->ran_at?->format('Y-m-d H:i:s');
                $seedRun->display_class_name = $this->formatSeederClassName($seedRun->class_name);

                return $seedRun;
            });

        $recentSeedRuns = $executedSeeders
            ->filter(fn ($seedRun) => optional($seedRun->ran_at)->greaterThanOrEqualTo($recentThreshold))
            ->sortByDesc(fn ($seedRun) => optional($seedRun->ran_at)->timestamp ?? 0)
            ->values();

        $recentSeedRunOrdinals = $recentSeedRuns
            ->mapWithKeys(fn ($seedRun, $index) => [$seedRun->id => $index + 1]);

        $executedClasses = $executedSeeders
            ->pluck('class_name')
            ->all();

        $pendingSeeders = collect($this->discoverSeederClasses(database_path('seeders')))
            ->reject(fn (string $class) => in_array($class, $executedClasses, true))
            ->map(function (string $class) {
                $displayName = $this->formatSeederClassName($class);
                [$namespace, $baseName] = $this->splitSeederDisplayName($displayName);

                return (object) [
                    'class_name' => $class,
                    'display_class_name' => $displayName,
                    'display_class_namespace' => $namespace,
                    'display_class_basename' => $baseName,
                    'supports_preview' => $this->seederSupportsPreview($class),
                ];
            })
            ->values();

        $pendingSeederHierarchy = $this->buildPendingSeederHierarchy($pendingSeeders);

        $questionCounts = collect();

        if (Schema::hasColumn('questions', 'seeder') && $executedSeeders->isNotEmpty()) {
            $questionCounts = Question::query()
                ->select('seeder', DB::raw('COUNT(*) as aggregate'))
                ->whereIn('seeder', $executedClasses)
                ->groupBy('seeder')
                ->pluck('aggregate', 'seeder');
        }

        $theoryPageTargets = $executedClasses === []
            ? collect()
            : Page::query()
                ->with('category')
                ->whereIn('seeder', $executedClasses)
                ->where('type', 'theory')
                ->get()
                ->keyBy('seeder');

        $theoryCategoryTargets = $executedClasses === []
            ? collect()
            : PageCategory::query()
                ->whereIn('seeder', $executedClasses)
                ->where('type', 'theory')
                ->get()
                ->keyBy('seeder');

        $executedSeeders = $executedSeeders->map(function ($seedRun) use ($questionCounts, $theoryPageTargets, $theoryCategoryTargets) {
            $seedRun->question_count = (int) ($questionCounts[$seedRun->class_name] ?? 0);
            $seedRun->data_profile = $this->describeSeederData($seedRun->class_name);
            $seedRun->theory_target = $this->resolveTheoryTargetForSeeder(
                $seedRun->class_name,
                $theoryPageTargets,
                $theoryCategoryTargets
            );

            return $seedRun;
        });

        $testTargets = $this->seederTestTargetResolver->resolveForSeeders($executedClasses);

        $executedSeeders = $executedSeeders->map(function ($seedRun) use ($testTargets) {
            $seedRun->test_target = $testTargets->get($seedRun->class_name);

            return $seedRun;
        });

        $executedSeederHierarchy = $this->buildSeederHierarchy($executedSeeders);

        return [
            'tableExists' => true,
            'executedSeeders' => $executedSeeders,
            'pendingSeeders' => $pendingSeeders,
            'pendingSeederHierarchy' => $pendingSeederHierarchy,
            'executedSeederHierarchy' => $executedSeederHierarchy,
            'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
        ];
    }

    public function runSeeder(string $className): array
    {
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return [
                'success' => false,
                'message' => __('Seeder :class was not found.', ['class' => $className]),
                'test_targets' => [],
            ];
        }

        if ($this->isVirtualLocalizationSeeder($className)) {
            try {
                $this->applyVirtualLocalizationSeeder($className);
                $this->logVirtualSeederRun($className);
            } catch (\Throwable $exception) {
                report($exception);

                return ['success' => false, 'message' => $exception->getMessage()];
            }

            return [
                'success' => true,
                'message' => __('Seeder :class executed successfully.', ['class' => $className]),
                'test_targets' => $this->resolveTestTargetsForClasses([$className]),
            ];
        }

        $filePath = $this->resolveSeederFilePath($className);

        if (! $this->isInstantiableSeeder($className, $filePath)) {
            return [
                'success' => false,
                'message' => __('Seeder :class cannot be executed.', ['class' => $className]),
                'test_targets' => [],
            ];
        }

        try {
            Artisan::call('db:seed', ['--class' => $className]);
        } catch (\Throwable $exception) {
            report($exception);
            return ['success' => false, 'message' => $exception->getMessage(), 'test_targets' => []];
        }

        return [
            'success' => true,
            'message' => __('Seeder :class executed successfully.', ['class' => $className]),
            'test_targets' => $this->resolveTestTargetsForClasses([$className]),
        ];
    }

    public function runSeedersInFolder(array $classNames, ?string $folderLabel = null): array
    {
        $normalized = collect($classNames)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return [
                'success' => false,
                'message' => __('No seeders were selected.'),
                'executed' => [],
                'errors' => [],
                'test_targets' => [],
            ];
        }

        $result = $this->executeSeedersInOrder($normalized);
        $label = $this->resolveFolderLabel($folderLabel);
        $message = $result['ran']->isNotEmpty()
            ? __('Executed :count seeder(s) from folder :folder.', [
                'count' => $result['ran']->count(),
                'folder' => $label,
            ])
            : __('No seeders were executed from folder :folder.', ['folder' => $label]);

        return [
            'success' => $result['ran']->isNotEmpty(),
            'message' => $message,
            'executed' => $result['ran']->all(),
            'ordered' => $result['ordered']->all(),
            'errors' => $result['errors']->all(),
            'test_targets' => $this->resolveTestTargetsForClasses($result['ran']),
        ];
    }

    public function runMissingSeeders(): array
    {
        if (! Schema::hasTable('seed_runs')) {
            return [
                'success' => false,
                'message' => __('The seed_runs table does not exist.'),
                'executed' => [],
                'test_targets' => [],
            ];
        }

        $executedClasses = DB::table('seed_runs')
            ->pluck('class_name')
            ->all();

        $pendingSeeders = collect($this->discoverSeederClasses(database_path('seeders')))
            ->reject(fn (string $class) => in_array($class, $executedClasses, true))
            ->values();
        $result = $this->executeSeedersInOrder($pendingSeeders);
        $ran = $result['ran'];
        $errors = $result['errors'];

        $message = $ran->isNotEmpty()
            ? __('Executed :count seeder(s): :classes', ['count' => $ran->count(), 'classes' => $ran->implode(', ')])
            : __('No seeders were executed.');

        return [
            'success' => $ran->isNotEmpty(),
            'message' => $message,
            'executed' => $ran->all(),
            'errors' => $errors->all(),
            'test_targets' => $this->resolveTestTargetsForClasses($ran),
        ];
    }

    protected function executeSeedersInOrder(Collection $classNames): array
    {
        $orderedClassNames = $this->orderSeedersForExecution($classNames);
        $ran = collect();
        $errors = collect();

        foreach ($orderedClassNames as $className) {
            if (! $this->ensureSeederClassIsLoaded($className)) {
                $errors->push(__('Seeder :class is not autoloadable.', ['class' => $className]));
                continue;
            }

            if ($this->isVirtualLocalizationSeeder($className)) {
                try {
                    $this->applyVirtualLocalizationSeeder($className);
                    $this->logVirtualSeederRun($className);
                    $ran->push($className);
                } catch (\Throwable $exception) {
                    report($exception);
                    $errors->push($exception->getMessage());
                }

                continue;
            }

            $filePath = $this->resolveSeederFilePath($className);

            if (! $this->isInstantiableSeeder($className, $filePath)) {
                $errors->push(__('Seeder :class cannot be executed.', ['class' => $className]));
                continue;
            }

            try {
                Artisan::call('db:seed', ['--class' => $className]);
                $ran->push($className);
            } catch (\Throwable $exception) {
                report($exception);
                $errors->push($exception->getMessage());
            }
        }

        return [
            'ordered' => $orderedClassNames,
            'ran' => $ran,
            'errors' => $errors,
        ];
    }

    protected function orderSeedersForExecution(Collection $classNames): Collection
    {
        $normalized = $classNames
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return collect();
        }

        $metadataByClass = $normalized->mapWithKeys(fn (string $className) => [
            $className => $this->buildSeederExecutionMetadata($className),
        ]);

        $categoryProviders = $metadataByClass
            ->filter(fn (array $metadata) => filled($metadata['provided_category_slug'] ?? null))
            ->mapWithKeys(fn (array $metadata, string $className) => [
                (string) $metadata['provided_category_slug'] => $className,
            ]);

        $dependents = [];
        $inDegree = [];

        foreach ($normalized as $className) {
            $metadata = $metadataByClass->get($className, []);
            $dependencies = collect();

            $parentSlug = trim((string) ($metadata['parent_category_slug'] ?? ''));
            if ($parentSlug !== '' && $categoryProviders->has($parentSlug)) {
                $dependencies->push($categoryProviders->get($parentSlug));
            }

            $targetSlug = trim((string) ($metadata['target_category_slug'] ?? ''));
            if ($targetSlug !== '' && $categoryProviders->has($targetSlug)) {
                $dependencies->push($categoryProviders->get($targetSlug));
            }

            $resolvedDependencies = $dependencies
                ->filter(fn ($dependency) => is_string($dependency) && $dependency !== '' && $dependency !== $className)
                ->unique()
                ->values()
                ->all();

            $inDegree[$className] = count($resolvedDependencies);

            foreach ($resolvedDependencies as $dependencyClass) {
                $dependents[$dependencyClass] ??= [];
                $dependents[$dependencyClass][] = $className;
            }
        }

        $ready = $this->sortSeedersByExecutionPriority(
            array_keys(array_filter($inDegree, fn (int $degree) => $degree === 0)),
            $metadataByClass
        );

        $ordered = [];

        while ($ready !== []) {
            $currentClass = array_shift($ready);
            $ordered[] = $currentClass;

            foreach ($dependents[$currentClass] ?? [] as $dependentClass) {
                $inDegree[$dependentClass]--;

                if ($inDegree[$dependentClass] === 0) {
                    $ready[] = $dependentClass;
                }
            }

            $ready = $this->sortSeedersByExecutionPriority(array_values(array_unique($ready)), $metadataByClass);
        }

        $remaining = array_values(array_diff($normalized->all(), $ordered));

        if ($remaining !== []) {
            $ordered = array_merge(
                $ordered,
                $this->sortSeedersByExecutionPriority($remaining, $metadataByClass)
            );
        }

        return collect($ordered)->values();
    }

    /**
     * @param  array<int, string>  $classNames
     * @param  Collection<string, array<string, mixed>>  $metadataByClass
     * @return array<int, string>
     */
    protected function sortSeedersByExecutionPriority(array $classNames, Collection $metadataByClass): array
    {
        usort($classNames, function (string $leftClass, string $rightClass) use ($metadataByClass) {
            $left = $metadataByClass->get($leftClass, []);
            $right = $metadataByClass->get($rightClass, []);

            $groupOrder = [
                'category' => 0,
                'page' => 1,
                'other' => 2,
                'localization' => 3,
            ];

            $leftGroup = $groupOrder[$left['group'] ?? 'other'] ?? 99;
            $rightGroup = $groupOrder[$right['group'] ?? 'other'] ?? 99;

            if ($leftGroup !== $rightGroup) {
                return $leftGroup <=> $rightGroup;
            }

            $leftHasParent = filled($left['parent_category_slug'] ?? null) ? 1 : 0;
            $rightHasParent = filled($right['parent_category_slug'] ?? null) ? 1 : 0;

            if ($leftHasParent !== $rightHasParent) {
                return $leftHasParent <=> $rightHasParent;
            }

            $leftDepth = (int) ($left['folder_depth'] ?? 0);
            $rightDepth = (int) ($right['folder_depth'] ?? 0);

            if ($leftDepth !== $rightDepth) {
                return $leftDepth <=> $rightDepth;
            }

            return strcmp(
                (string) ($left['sort_key'] ?? $leftClass),
                (string) ($right['sort_key'] ?? $rightClass)
            );
        });

        return $classNames;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSeederExecutionMetadata(string $className): array
    {
        $displayName = $this->formatSeederClassName($className);
        $segments = array_values(array_filter(explode('\\', $displayName), 'strlen'));

        $metadata = [
            'group' => 'other',
            'folder_depth' => max(0, count($segments) - 1),
            'sort_key' => Str::lower($displayName),
            'provided_category_slug' => null,
            'parent_category_slug' => null,
            'target_category_slug' => null,
        ];

        if ($this->isVirtualLocalizationSeeder($className)) {
            $metadata['group'] = 'localization';

            return $metadata;
        }

        if (! $this->ensureSeederClassIsLoaded($className)) {
            return $metadata;
        }

        if (is_subclass_of($className, JsonPageSeeder::class)) {
            return array_merge($metadata, $this->buildJsonSeederExecutionMetadata($className));
        }

        $categorySlug = $this->resolveCategorySlugForSeeder($className);

        if (
            is_subclass_of($className, PageCategoryDescriptionSeederBase::class)
            || ($categorySlug !== null && Str::contains(class_basename($className), 'Category'))
        ) {
            $metadata['group'] = 'category';
            $metadata['provided_category_slug'] = $categorySlug;

            return $metadata;
        }

        if (is_subclass_of($className, GrammarPageSeederBase::class)) {
            $metadata['group'] = 'page';
            $metadata['target_category_slug'] = $this->resolveGrammarPageCategorySlug($className);

            return $metadata;
        }

        return $metadata;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildJsonSeederExecutionMetadata(string $className): array
    {
        $definition = $this->loadJsonSeederDefinition($className);

        if ($definition === null) {
            return [];
        }

        $contentType = Str::lower(trim((string) ($definition['content_type'] ?? '')));

        if ($contentType === '') {
            $contentType = is_array($definition['page'] ?? null) ? 'page' : 'category';
        }

        if ($contentType === 'category') {
            return [
                'group' => 'category',
                'provided_category_slug' => trim((string) ($definition['slug'] ?? data_get($definition, 'category.slug', ''))) ?: null,
                'parent_category_slug' => trim((string) data_get($definition, 'category.parent_slug', '')) ?: null,
                'target_category_slug' => null,
            ];
        }

        return [
            'group' => 'page',
            'provided_category_slug' => null,
            'parent_category_slug' => null,
            'target_category_slug' => trim((string) data_get($definition, 'page.category.slug', '')) ?: null,
        ];
    }

    protected function loadJsonSeederDefinition(string $className): ?array
    {
        $definitionPath = $this->invokeSeederMethod($className, 'definitionPath');

        if (! is_string($definitionPath) || $definitionPath === '' || ! File::exists($definitionPath)) {
            return null;
        }

        $decoded = json_decode((string) File::get($definitionPath), true);

        return is_array($decoded) ? $decoded : null;
    }

    protected function resolveGrammarPageCategorySlug(string $className): ?string
    {
        $pageConfig = $this->invokeSeederMethod($className, 'page');

        if (is_array($pageConfig)) {
            $categorySlug = trim((string) data_get($pageConfig, 'category.slug', ''));

            if ($categorySlug !== '') {
                return $categorySlug;
            }
        }

        $categoryConfig = $this->invokeSeederMethod($className, 'category');

        if (! is_array($categoryConfig)) {
            return null;
        }

        $categorySlug = trim((string) ($categoryConfig['slug'] ?? ''));

        return $categorySlug !== '' ? $categorySlug : null;
    }

    protected function resolveFolderLabel(?string $label): string
    {
        $label = trim((string) $label);

        return $label !== '' ? $label : __('selected folder');
    }

    public function markAsExecuted(string $className): array
    {
        if (! Schema::hasTable('seed_runs')) {
            return ['success' => false, 'message' => __('The seed_runs table does not exist.'), 'test_targets' => []];
        }

        if (! $this->ensureSeederClassIsLoaded($className)) {
            return [
                'success' => false,
                'message' => __('Seeder :class was not found.', ['class' => $className]),
                'test_targets' => [],
            ];
        }

        try {
            DB::table('seed_runs')->updateOrInsert(
                ['class_name' => $className],
                ['ran_at' => now()]
            );
        } catch (\Throwable $exception) {
            report($exception);
            return ['success' => false, 'message' => $exception->getMessage(), 'test_targets' => []];
        }

        return [
            'success' => true,
            'message' => __('Seeder :class marked as executed.', ['class' => $className]),
            'test_targets' => $this->resolveTestTargetsForClasses([$className]),
        ];
    }

    public function destroySeedRun(int $seedRunId): array
    {
        if (! Schema::hasTable('seed_runs')) {
            return ['success' => false, 'message' => __('The seed_runs table does not exist.')];
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            return ['success' => false, 'message' => __('Seed run record was not found.')];
        }

        $className = $seedRun->class_name;
        $deleted = DB::table('seed_runs')->where('id', $seedRunId)->delete();

        if (! $deleted) {
            return ['success' => false, 'message' => __('Seed run record was not found.')];
        }

        $filePath = $this->resolveSeederFilePath($className);
        $fileExists = $filePath && File::exists($filePath);

        return [
            'success' => true,
            'message' => __('Seed run entry removed.'),
            'returns_to_pending' => $fileExists,
            'class_name' => $className,
        ];
    }

    public function destroySeedRunWithData(int $seedRunId): array
    {
        if (! Schema::hasTable('seed_runs')) {
            return ['success' => false, 'message' => __('The seed_runs table does not exist.')];
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            return ['success' => false, 'message' => __('Seed run record was not found.')];
        }

        $deletedQuestions = 0;
        $deletedBlocks = 0;
        $deletedPages = 0;
        $deletedCategories = 0;
        $profile = $this->describeSeederData($seedRun->class_name);

        try {
            DB::transaction(function () use ($seedRun, &$deletedQuestions, &$deletedBlocks, &$deletedPages, &$deletedCategories, $profile) {
                $classNames = collect([$seedRun->class_name]);

                if ($profile['type'] === 'questions') {
                    $deletedQuestions = $this->deleteQuestionsForSeeders($classNames);
                } elseif ($profile['type'] === 'page_localizations') {
                    $result = $this->removeVirtualLocalizationSeederData($seedRun->class_name);
                    $deletedBlocks = (int) ($result['deleted_blocks'] ?? 0);
                } elseif ($profile['type'] === 'pages') {
                    $pageResult = $this->deletePageContentForSeeders($classNames);
                    $deletedBlocks = $pageResult['blocks'];
                    $deletedPages = $pageResult['pages_deleted'];
                    $deletedCategories = $pageResult['categories_deleted'];
                } else {
                    $deletedQuestions = $this->deleteQuestionsForSeeders($classNames);
                    $pageResult = $this->deletePageContentForSeeders($classNames);
                    $deletedBlocks = $pageResult['blocks'];
                    $deletedPages = $pageResult['pages_deleted'];
                    $deletedCategories = $pageResult['categories_deleted'];
                }

                DB::table('seed_runs')->where('id', $seedRun->id)->delete();
            });
        } catch (\Throwable $exception) {
            report($exception);
            return ['success' => false, 'message' => $exception->getMessage(), 'test_targets' => []];
        }

        $message = match ($profile['type']) {
            'page_localizations' => __('Deleted seed run and :count localization block(s).', ['count' => $deletedBlocks]),
            'pages' => __('Deleted seed run and :blocks text block(s).', ['blocks' => $deletedBlocks])
                . ($deletedPages > 0 ? ' ' . __('Deleted :count page(s).', ['count' => $deletedPages]) : '')
                . ($deletedCategories > 0 ? ' ' . __('Deleted :count category record(s).', ['count' => $deletedCategories]) : ''),
            'questions' => __('Deleted seed run and :count question(s).', ['count' => $deletedQuestions]),
            default => __('Deleted seed run. Questions: :q, Blocks: :b', ['q' => $deletedQuestions, 'b' => $deletedBlocks]),
        };

        return [
            'success' => true,
            'message' => $message,
            'deleted_questions' => $deletedQuestions,
            'deleted_blocks' => $deletedBlocks,
            'deleted_pages' => $deletedPages,
            'deleted_categories' => $deletedCategories,
            'test_targets' => $this->resolveTestTargetsForClasses([$className]),
        ];
    }

    public function refreshSeeder(int $seedRunId): array
    {
        if (! Schema::hasTable('seed_runs')) {
            return ['success' => false, 'message' => __('The seed_runs table does not exist.')];
        }

        $seedRun = DB::table('seed_runs')->where('id', $seedRunId)->first();

        if (! $seedRun) {
            return ['success' => false, 'message' => __('Seed run record was not found.')];
        }

        $className = $seedRun->class_name;

        if (! $this->ensureSeederClassIsLoaded($className)) {
            return ['success' => false, 'message' => __('Seeder :class was not found.', ['class' => $className])];
        }

        $isLocalizationSeeder = $this->isVirtualLocalizationSeeder($className);
        $filePath = $this->resolveSeederFilePath($className);

        if (! $isLocalizationSeeder && ! $this->isInstantiableSeeder($className, $filePath)) {
            return ['success' => false, 'message' => __('Seeder :class cannot be executed.', ['class' => $className])];
        }

        $deletedQuestions = 0;
        $deletedBlocks = 0;
        $deletedPages = 0;
        $deletedCategories = 0;
        $profile = $this->describeSeederData($seedRun->class_name);

        try {
            // Delete old data in a transaction
            DB::transaction(function () use ($seedRun, &$deletedQuestions, &$deletedBlocks, &$deletedPages, &$deletedCategories, $profile) {
                $classNames = collect([$seedRun->class_name]);

                if ($profile['type'] === 'questions') {
                    $deletedQuestions = $this->deleteQuestionsForSeeders($classNames);
                } elseif ($profile['type'] === 'page_localizations') {
                    $result = $this->removeVirtualLocalizationSeederData($seedRun->class_name);
                    $deletedBlocks = (int) ($result['deleted_blocks'] ?? 0);
                } elseif ($profile['type'] === 'pages') {
                    $pageResult = $this->deletePageContentForSeeders($classNames);
                    $deletedBlocks = $pageResult['blocks'];
                    $deletedPages = $pageResult['pages_deleted'];
                    $deletedCategories = $pageResult['categories_deleted'];
                } else {
                    $deletedQuestions = $this->deleteQuestionsForSeeders($classNames);
                    $pageResult = $this->deletePageContentForSeeders($classNames);
                    $deletedBlocks = $pageResult['blocks'];
                    $deletedPages = $pageResult['pages_deleted'];
                    $deletedCategories = $pageResult['categories_deleted'];
                }
            });

            // Re-run the seeder outside the transaction
            if ($isLocalizationSeeder) {
                $this->applyVirtualLocalizationSeeder($className);
                $this->logVirtualSeederRun($className);
            } else {
                Artisan::call('db:seed', ['--class' => $className]);
            }

            // Update the ran_at timestamp
            DB::table('seed_runs')
                ->where('id', $seedRun->id)
                ->update(['ran_at' => now()]);
        } catch (\Throwable $exception) {
            report($exception);
            return ['success' => false, 'message' => $exception->getMessage()];
        }

        $message = match ($profile['type']) {
            'page_localizations' => __('Refreshed localization seeder :class. Rebuilt :count localized block(s).', [
                'class' => $this->formatSeederClassName($seedRun->class_name),
                'count' => $deletedBlocks,
            ]),
            'pages' => __('Refreshed seeder :class. Deleted :blocks text block(s) and regenerated content.', [
                'class' => $this->formatSeederClassName($seedRun->class_name),
                'blocks' => $deletedBlocks,
            ]) . ($deletedPages > 0 ? ' ' . __('Deleted :count page record(s).', ['count' => $deletedPages]) : '')
                . ($deletedCategories > 0 ? ' ' . __('Deleted :count category record(s).', ['count' => $deletedCategories]) : ''),
            'questions' => __('Refreshed seeder :class. Deleted :count question(s) and regenerated them.', [
                'class' => $this->formatSeederClassName($seedRun->class_name),
                'count' => $deletedQuestions,
            ]),
            default => __('Refreshed seeder :class.', ['class' => $this->formatSeederClassName($seedRun->class_name)]),
        };

        return [
            'success' => true,
            'message' => $message,
            'deleted_questions' => $deletedQuestions,
            'deleted_blocks' => $deletedBlocks,
            'deleted_pages' => $deletedPages,
            'deleted_categories' => $deletedCategories,
        ];
    }

    public function getSeederFile(string $className): array
    {
        $filePath = $this->resolveSeederFilePath($className);

        if (! $filePath || ! File::exists($filePath)) {
            return ['success' => false, 'message' => __('Файл для сидера :class не знайдено.', ['class' => $className])];
        }

        if (! is_readable($filePath)) {
            return ['success' => false, 'message' => __('Файл сидера :class недоступний для читання.', ['class' => $className])];
        }

        try {
            $contents = File::get($filePath);
        } catch (\Throwable $exception) {
            report($exception);
            return ['success' => false, 'message' => __('Не вдалося прочитати файл сидера :class.', ['class' => $className])];
        }

        $lastModified = null;

        try {
            $timestamp = File::lastModified($filePath);
            if ($timestamp) {
                $lastModified = Carbon::createFromTimestamp($timestamp)->toDateTimeString();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return [
            'success' => true,
            'class_name' => $className,
            'display_class_name' => $this->formatSeederClassName($className),
            'path' => $this->makeSeederFileDisplayPath($filePath),
            'contents' => $contents,
            'last_modified' => $lastModified,
        ];
    }

    public function updateSeederFile(string $className, string $contents): array
    {
        $filePath = $this->resolveSeederFilePath($className);

        if (! $filePath || ! File::exists($filePath)) {
            return ['success' => false, 'message' => __('Файл для сидера :class не знайдено.', ['class' => $className])];
        }

        if (! File::isWritable($filePath)) {
            return ['success' => false, 'message' => __('Файл сидера :class доступний лише для читання.', ['class' => $className])];
        }

        try {
            File::put($filePath, $contents, true);
        } catch (\Throwable $exception) {
            report($exception);
            return ['success' => false, 'message' => __('Не вдалося зберегти файл сидера :class.', ['class' => $className])];
        }

        clearstatcache(true, $filePath);

        $lastModified = null;

        try {
            $timestamp = File::lastModified($filePath);
            if ($timestamp) {
                $lastModified = Carbon::createFromTimestamp($timestamp)->toDateTimeString();
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $freshContents = $contents;

        try {
            $freshContents = File::get($filePath);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return [
            'success' => true,
            'message' => __('Файл сидера успішно збережено.'),
            'path' => $this->makeSeederFileDisplayPath($filePath),
            'last_modified' => $lastModified,
            'contents' => $freshContents,
        ];
    }

    public function storeSeederFile(string $className, string $contents, string $folder = ''): array
    {
        if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $className)) {
            return ['success' => false, 'message' => __('Назва класу має починатися з великої літери.')];
        }

        $baseDir = database_path('seeders');
        $targetDir = $baseDir;

        if ($folder !== '') {
            $parts = preg_split('/[\/\\\\]+/', $folder);
            $sanitizedParts = [];

            foreach ($parts as $part) {
                $part = trim($part);

                if ($part === '' || $part === '.' || $part === '..') {
                    continue;
                }

                if (! preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $part)) {
                    return ['success' => false, 'message' => __('Назва папки може містити лише літери, цифри та підкреслення, і має починатися з літери.')];
                }

                $sanitizedParts[] = $part;
            }

            $folder = implode(DIRECTORY_SEPARATOR, $sanitizedParts);

            if ($folder !== '') {
                $targetDir = $baseDir . DIRECTORY_SEPARATOR . $folder;

                $resolvedPath = realpath(dirname($targetDir));

                if ($resolvedPath !== false && strpos($resolvedPath, realpath($baseDir)) !== 0) {
                    return ['success' => false, 'message' => __('Невалідний шлях до папки.')];
                }
            }
        }

        $namespace = 'Database\\Seeders';

        if ($folder !== '') {
            $namespaceParts = explode(DIRECTORY_SEPARATOR, $folder);
            $namespaceParts = array_filter($namespaceParts, fn ($part) => $part !== '');

            if (! empty($namespaceParts)) {
                $namespace .= '\\' . implode('\\', $namespaceParts);
            }
        }

        $fullClassName = $namespace . '\\' . $className;
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $className . '.php';

        if (File::exists($filePath)) {
            return ['success' => false, 'message' => __('Файл сидера :class вже існує.', ['class' => $fullClassName])];
        }

        if (! File::isDirectory($targetDir)) {
            try {
                File::makeDirectory($targetDir, 0755, true);
            } catch (\Throwable $exception) {
                report($exception);
                return ['success' => false, 'message' => __('Не вдалося створити директорію для сидера.')];
            }
        }

        try {
            File::put($filePath, $contents, true);
        } catch (\Throwable $exception) {
            report($exception);
            return ['success' => false, 'message' => __('Не вдалося створити файл сидера :class.', ['class' => $fullClassName])];
        }

        clearstatcache(true, $filePath);

        $this->seederClassMap = null;

        $displayName = $this->formatSeederClassName($fullClassName);
        [$displayNamespace, $baseName] = $this->splitSeederDisplayName($displayName);

        return [
            'success' => true,
            'message' => __('Файл сидера успішно створено.'),
            'class_name' => $fullClassName,
            'path' => $this->makeSeederFileDisplayPath($filePath),
            'pending_seeder' => (object) [
                'class_name' => $fullClassName,
                'display_class_name' => $displayName,
                'display_class_namespace' => $displayNamespace,
                'display_class_basename' => $baseName,
                'supports_preview' => false,
            ],
        ];
    }

    public function deleteSeederFile(string $className, bool $deleteQuestions = false): array
    {
        return $this->removeSeederFileAndAssociatedRuns($className, null, $deleteQuestions);
    }

    public function getSeederFolders(): array
    {
        $baseDir = database_path('seeders');
        return $this->discoverSeederFolders($baseDir, $baseDir);
    }

    public function formatSeederClassName(string $className): string
    {
        $shortName = Str::after($className, 'Database\\Seeders\\');

        return $shortName !== '' ? $shortName : $className;
    }

    public function splitSeederDisplayName(string $displayName): array
    {
        if (! Str::contains($displayName, '\\')) {
            return [null, $displayName];
        }

        return [
            Str::beforeLast($displayName, '\\'),
            Str::afterLast($displayName, '\\'),
        ];
    }

    protected function discoverSeederFolders(string $baseDir, string $currentDir): array
    {
        $folders = [];

        if (! File::isDirectory($currentDir)) {
            return $folders;
        }

        $directories = File::directories($currentDir);

        foreach ($directories as $directory) {
            $relativePath = str_replace($baseDir . DIRECTORY_SEPARATOR, '', $directory);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            $folders[] = $relativePath;

            $subFolders = $this->discoverSeederFolders($baseDir, $directory);
            $folders = array_merge($folders, $subFolders);
        }

        sort($folders);

        return $folders;
    }

    protected function buildPendingSeederHierarchy(Collection $pendingSeeders): Collection
    {
        $root = [
            'folders' => [],
            'seeders' => [],
        ];

        foreach ($pendingSeeders as $seeder) {
            $segments = array_values(array_filter(explode('\\', $seeder->display_class_name), 'strlen'));

            if (empty($segments)) {
                $root['seeders'][] = [
                    'name' => $seeder->display_class_name,
                    'seeder' => $seeder,
                ];

                continue;
            }

            $current =& $root;

            foreach ($segments as $index => $segment) {
                $isLast = $index === count($segments) - 1;

                if ($isLast) {
                    $current['seeders'][] = [
                        'name' => $segment,
                        'seeder' => $seeder,
                    ];

                    continue;
                }

                if (! isset($current['folders'][$segment])) {
                    $current['folders'][$segment] = [
                        'name' => $segment,
                        'folders' => [],
                        'seeders' => [],
                    ];
                }

                $current =& $current['folders'][$segment];
            }

            unset($current);
        }

        return $this->normalizePendingSeederHierarchy($root);
    }

    protected function normalizePendingSeederHierarchy(array $node, string $path = ''): Collection
    {
        $folders = collect($node['folders'] ?? [])
            ->sortBy(fn ($folder) => $folder['name'])
            ->map(function ($folder) use ($path) {
                $folderPath = ltrim(($path !== '' ? $path . '/' : '') . $folder['name'], '/');
                $children = $this->normalizePendingSeederHierarchy($folder, $folderPath);
                $seederCount = $children->sum(fn ($child) => (int) ($child['seeder_count'] ?? 0));
                $classNames = $children->flatMap(function ($child) {
                    return collect($child['class_names'] ?? []);
                })->unique()->values();

                return [
                    'type' => 'folder',
                    'name' => $folder['name'],
                    // Convert to array for consistent Livewire serialization
                    'children' => $children->all(),
                    'seeder_count' => $seederCount,
                    'class_names' => $classNames->all(),
                    'path' => $folderPath,
                ];
            });

        $seeders = collect($node['seeders'] ?? [])
            ->sortBy(fn ($seeder) => $seeder['name'])
            ->map(function ($seeder) use ($path) {
                $pendingSeeder = $seeder['seeder'];
                $className = $pendingSeeder->class_name ?? '';
                $fullPath = ltrim(($path !== '' ? $path . '/' : '') . $seeder['name'], '/');

                // Convert stdClass to array for consistent Livewire serialization
                $pendingSeederArray = [
                    'class_name' => $pendingSeeder->class_name ?? '',
                    'display_class_name' => $pendingSeeder->display_class_name ?? '',
                    'display_class_namespace' => $pendingSeeder->display_class_namespace ?? null,
                    'display_class_basename' => $pendingSeeder->display_class_basename ?? '',
                    'supports_preview' => $pendingSeeder->supports_preview ?? false,
                ];

                return [
                    'type' => 'seeder',
                    'name' => $seeder['name'],
                    'pending_seeder' => $pendingSeederArray,
                    'seeder_count' => 1,
                    'class_names' => [$className],
                    'path' => $fullPath,
                ];
            });

        return $folders->values()->merge($seeders->values())->values();
    }

    protected function buildSeederHierarchy(Collection $seedRuns): Collection
    {
        $root = [
            'folders' => [],
            'seeders' => [],
        ];

        foreach ($seedRuns as $seedRun) {
            $segments = array_values(array_filter(explode('\\', $seedRun->display_class_name), 'strlen'));

            if (empty($segments)) {
                $root['seeders'][] = [
                    'name' => $seedRun->display_class_name,
                    'seed_run' => $seedRun,
                ];

                continue;
            }

            $current =& $root;

            foreach ($segments as $index => $segment) {
                $isLast = $index === count($segments) - 1;

                if ($isLast) {
                    $current['seeders'][] = [
                        'name' => $segment,
                        'seed_run' => $seedRun,
                    ];

                    continue;
                }

                if (! isset($current['folders'][$segment])) {
                    $current['folders'][$segment] = [
                        'name' => $segment,
                        'folders' => [],
                        'seeders' => [],
                    ];
                }

                $current =& $current['folders'][$segment];
            }

            unset($current);
        }

        return $this->normalizeSeederHierarchy($root);
    }

    protected function normalizeSeederHierarchy(array $node, string $path = ''): Collection
    {
        $folders = collect($node['folders'] ?? [])
            ->sortBy(fn ($folder) => $folder['name'])
            ->map(function ($folder) use ($path) {
                $folderPath = ltrim(($path !== '' ? $path . '/' : '') . $folder['name'], '/');
                $children = $this->normalizeSeederHierarchy($folder, $folderPath);
                $seedRunIds = $children->flatMap(function ($child) {
                    return collect($child['seed_run_ids'] ?? []);
                })->unique()->values();
                $classNames = $children->flatMap(function ($child) {
                    return collect($child['class_names'] ?? []);
                })->unique()->values();
                $folderProfile = $this->describeFolderData($classNames);

                return [
                    'type' => 'folder',
                    'name' => $folder['name'],
                    // Convert to array for consistent Livewire serialization
                    'children' => $children->all(),
                    'seeder_count' => $seedRunIds->count(),
                    'seed_run_ids' => $seedRunIds->all(),
                    'class_names' => $classNames->all(),
                    'path' => $folderPath,
                    'folder_profile' => $folderProfile,
                ];
            });

        $seeders = collect($node['seeders'] ?? [])
            ->sortBy(fn ($seeder) => $seeder['name'])
            ->map(function ($seeder) use ($path) {
                $seedRun = $seeder['seed_run'];
                $seedRunIds = [$seedRun->id];
                $classNames = [$seedRun->class_name];
                $fullPath = ltrim(($path !== '' ? $path . '/' : '') . $seeder['name'], '/');

                // Convert stdClass to array for consistent Livewire serialization
                $seedRunArray = [
                    'id' => $seedRun->id,
                    'class_name' => $seedRun->class_name,
                    'display_class_name' => $seedRun->display_class_name,
                    'ran_at_formatted' => $seedRun->ran_at_formatted,
                    'question_count' => $seedRun->question_count ?? 0,
                    'theory_target' => $seedRun->theory_target ?? null,
                    'test_target' => $seedRun->test_target ?? null,
                ];

                return [
                    'type' => 'seeder',
                    'name' => $seeder['name'],
                    'seed_run' => $seedRunArray,
                    'seeder_count' => 1,
                    'seed_run_ids' => $seedRunIds,
                    'class_names' => $classNames,
                    'path' => $fullPath,
                    'data_profile' => $this->describeSeederData($seedRun->class_name),
                ];
            });

        return $folders->values()->merge($seeders->values())->values();
    }

    /**
     * @param  iterable<int, string>  $classNames
     * @return array<int, array<string, mixed>>
     */
    protected function resolveTestTargetsForClasses(iterable $classNames): array
    {
        return $this->seederTestTargetResolver->resolveForSeeders($classNames)
            ->filter(fn ($target) => filled($target['url'] ?? null))
            ->unique(fn ($target) => (string) ($target['url'] ?? ''))
            ->values()
            ->all();
    }

    protected function resolveTheoryTargetForSeeder(
        string $className,
        ?Collection $theoryPageTargets = null,
        ?Collection $theoryCategoryTargets = null,
    ): ?array {
        if (! $this->isTheorySiteSeeder($className)) {
            return null;
        }

        $page = $theoryPageTargets?->get($className);

        if (! $page instanceof Page) {
            $page = $this->findTheoryPageForSeeder($className);
        }

        if ($page instanceof Page && filled($page->slug) && filled($page->category?->slug)) {
            return [
                'type' => 'page',
                'label' => __('Сторінка теорії'),
                'url' => localized_route('theory.show', [$page->category->slug, $page->slug]),
                'title' => $page->title,
            ];
        }

        $category = $theoryCategoryTargets?->get($className);

        if (! $category instanceof PageCategory) {
            $category = $this->findTheoryCategoryForSeeder($className);
        }

        if ($category instanceof PageCategory && filled($category->slug)) {
            return [
                'type' => 'category',
                'label' => __('Категорія теорії'),
                'url' => localized_route('theory.category', $category->slug),
                'title' => $category->title,
            ];
        }

        return null;
    }

    protected function findTheoryPageForSeeder(string $className): ?Page
    {
        $page = Page::query()
            ->with('category')
            ->where('seeder', $className)
            ->where('type', 'theory')
            ->orderBy('id')
            ->first();

        if ($page instanceof Page && filled($page->slug) && filled($page->category?->slug)) {
            return $page;
        }

        $slug = $this->resolvePageSlugForSeeder($className);

        if (! filled($slug)) {
            return null;
        }

        $page = Page::query()
            ->with('category')
            ->where('slug', $slug)
            ->where('type', 'theory')
            ->orderBy('id')
            ->first();

        return $page instanceof Page && filled($page->slug) && filled($page->category?->slug)
            ? $page
            : null;
    }

    protected function findTheoryCategoryForSeeder(string $className): ?PageCategory
    {
        $category = PageCategory::query()
            ->where('seeder', $className)
            ->where('type', 'theory')
            ->orderBy('id')
            ->first();

        if ($category instanceof PageCategory && filled($category->slug)) {
            return $category;
        }

        $slug = $this->resolveCategorySlugForSeeder($className);

        if (! filled($slug)) {
            return null;
        }

        $category = PageCategory::query()
            ->where('slug', $slug)
            ->where('type', 'theory')
            ->orderBy('id')
            ->first();

        return $category instanceof PageCategory && filled($category->slug)
            ? $category
            : null;
    }

    protected function isTheorySiteSeeder(string $className): bool
    {
        return Str::startsWith($className, [
            'Database\\Seeders\\Page_v2\\',
            'Database\\Seeders\\Page_V3\\',
        ]);
    }

    protected function resolveCategorySlugForSeeder(string $className): ?string
    {
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return null;
        }

        if (is_subclass_of($className, JsonPageSeeder::class)) {
            $definition = $this->loadJsonSeederDefinition($className);
            $contentType = Str::lower(trim((string) ($definition['content_type'] ?? '')));

            if ($definition && ($contentType === 'category' || (is_array($definition['category'] ?? null) && ! is_array($definition['page'] ?? null)))) {
                $slug = trim((string) ($definition['slug'] ?? data_get($definition, 'category.slug', '')));

                return $slug !== '' ? $slug : null;
            }

            return null;
        }

        if (is_subclass_of($className, PageCategoryDescriptionSeederBase::class) || Str::contains(class_basename($className), 'Category')) {
            $slug = $this->invokeSeederMethod($className, 'previewCategorySlug');

            return is_string($slug) && $slug !== '' ? $slug : null;
        }

        return null;
    }

    protected function invokeSeederMethod(string $className, string $method): mixed
    {
        try {
            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract() || ! $reflection->isInstantiable() || ! $reflection->hasMethod($method)) {
                return null;
            }

            $instance = app()->make($className);
            $methodReflection = $reflection->getMethod($method);

            if (method_exists($methodReflection, 'setAccessible')) {
                $methodReflection->setAccessible(true);
            }

            return $methodReflection->invoke($instance);
        } catch (\Throwable) {
            return null;
        }
    }

    public function describeSeederData(string $className): array
    {
        $default = [
            'type' => 'unknown',
            'delete_button' => __('Видалити з даними'),
            'delete_confirm' => __('Видалити лог та пов\'язані дані?'),
            'folder_delete_button' => __('Видалити з даними'),
            'folder_delete_confirm' => __('Видалити всі сидери в папці «:folder» та пов\'язані дані?'),
        ];

        if (! $this->ensureSeederClassIsLoaded($className)) {
            return $default;
        }

        if ($this->virtualLocalizationType($className) === 'page_localizations') {
            return [
                'type' => 'page_localizations',
                'delete_button' => __('Видалити локалізації'),
                'delete_confirm' => __('Видалити лог та пов\'язані локалізації?'),
                'folder_delete_button' => __('Видалити локалізації'),
                'folder_delete_confirm' => __('Видалити всі локалізаційні сидери в папці «:folder» разом із локалізаціями?'),
            ];
        }

        if (is_subclass_of($className, QuestionSeederBase::class)) {
            return [
                'type' => 'questions',
                'delete_button' => __('Видалити з питаннями'),
                'delete_confirm' => __('Видалити лог та пов\'язані питання?'),
                'folder_delete_button' => __('Видалити з питаннями'),
                'folder_delete_confirm' => __('Видалити всі сидери в папці «:folder» разом із питаннями?'),
            ];
        }

        if ($this->classProvidesGrammarPages($className)) {
            return [
                'type' => 'pages',
                'delete_button' => __('Видалити зі сторінками'),
                'delete_confirm' => __('Видалити лог та пов\'язані сторінки й блоки?'),
                'folder_delete_button' => __('Видалити зі сторінками'),
                'folder_delete_confirm' => __('Видалити всі сидери в папці «:folder» разом із сторінками та блоками?'),
            ];
        }

        return $default;
    }

    protected function describeFolderData(Collection $classNames): array
    {
        $default = [
            'type' => 'unknown',
            'delete_button' => __('Видалити з даними'),
            'delete_confirm' => __('Видалити всі сидери в папці «:folder» та пов\'язані дані?'),
        ];

        if ($classNames->isEmpty()) {
            return $default;
        }

        $profiles = $classNames->map(fn ($class) => $this->describeSeederData($class));
        $types = $profiles->pluck('type')->filter()->unique();

        if ($types->count() === 1) {
            $type = $types->first();
            $profile = $profiles->firstWhere('type', $type);

            if ($profile) {
                return [
                    'type' => $type,
                    'delete_button' => $profile['folder_delete_button'],
                    'delete_confirm' => $profile['folder_delete_confirm'],
                ];
            }
        }

        return $default;
    }

    protected function seederSupportsPreview(string $className): bool
    {
        if ($this->isVirtualLocalizationSeeder($className)) {
            return true;
        }

        if (! $this->ensureSeederClassIsLoaded($className)) {
            return false;
        }

        if (! is_subclass_of($className, LaravelSeeder::class)) {
            return false;
        }

        return method_exists($className, 'run');
    }

    protected function removeSeederFileAndAssociatedRuns(string $className, ?bool $seedRunsTableExists = null, bool $deleteQuestions = false): array
    {
        if (! is_bool($seedRunsTableExists)) {
            $seedRunsTableExists = Schema::hasTable('seed_runs');
        }

        $filePath = $this->resolveSeederFilePath($className);

        if (! $filePath || ! File::exists($filePath)) {
            return [
                'status' => 'missing',
                'class' => $className,
                'message' => __('Файл для сидера :class не знайдено.', ['class' => $className]),
                'runs_deleted' => 0,
                'questions_deleted' => 0,
            ];
        }

        try {
            if (! File::delete($filePath)) {
                return [
                    'status' => 'error',
                    'class' => $className,
                    'message' => __('Не вдалося видалити файл сидера :class.', ['class' => $className]),
                    'runs_deleted' => 0,
                    'questions_deleted' => 0,
                ];
            }
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'status' => 'error',
                'class' => $className,
                'message' => __('Не вдалося видалити файл сидера :class.', ['class' => $className]),
                'runs_deleted' => 0,
                'questions_deleted' => 0,
            ];
        }

        // Ensure subsequent class discovery reflects the removal without requiring a full page reload
        $this->seederClassMap = null;

        $runsDeleted = 0;
        $questionsDeleted = 0;
        $questionDeletionFailed = false;

        if ($deleteQuestions) {
            try {
                $questionsDeleted = $this->deleteQuestionsForSeeders(collect([$className]));
            } catch (\Throwable $exception) {
                report($exception);
                $questionDeletionFailed = true;
            }
        }

        if ($seedRunsTableExists) {
            try {
                $runsDeleted = DB::table('seed_runs')
                    ->where('class_name', $className)
                    ->delete();
            } catch (\Throwable $exception) {
                report($exception);

                return [
                    'status' => 'partial',
                    'class' => $className,
                    'message' => __('Файл сидера :class видалено, але не вдалося оновити seed_runs. Будь ласка, перевірте журнал.', ['class' => $className]),
                    'runs_deleted' => 0,
                    'questions_deleted' => $questionsDeleted,
                ];
            }
        }

        if ($questionDeletionFailed) {
            return [
                'status' => 'partial',
                'class' => $className,
                'message' => __('Файл сидера :class видалено, але не вдалося видалити пов\'язані питання.', ['class' => $className]),
                'runs_deleted' => $runsDeleted,
                'questions_deleted' => $questionsDeleted,
            ];
        }

        $message = __('Файл сидера :class успішно видалено.', ['class' => $className]);

        if ($runsDeleted > 0) {
            $message .= ' ' . __('Пов\'язаний запис seed_runs видалено.');
        }

        if ($questionsDeleted > 0) {
            $message .= ' ' . trans_choice(
                '{1}Видалено :count пов\'язане питання.|[2,4]Видалено :count пов\'язані питання.|[5,*]Видалено :count пов\'язаних питань.',
                $questionsDeleted,
                ['count' => $questionsDeleted]
            );
        }

        return [
            'status' => 'success',
            'class' => $className,
            'message' => $message,
            'runs_deleted' => $runsDeleted,
            'questions_deleted' => $questionsDeleted,
        ];
    }

    protected function deleteQuestionsForSeeders(Collection $classNames): int
    {
        if ($classNames->isEmpty() || ! Schema::hasColumn('questions', 'seeder')) {
            return 0;
        }

        $deleted = 0;

        Question::query()
            ->whereIn('seeder', $classNames)
            ->orderBy('id')
            ->chunkById(100, function ($questions) use (&$deleted) {
                foreach ($questions as $question) {
                    $this->questionDeletionService->deleteQuestion($question);
                    $deleted++;
                }
            });

        return $deleted;
    }

    protected function deletePageContentForSeeders(Collection $classNames): array
    {
        $hasTextBlockTable = Schema::hasTable('text_blocks');
        $hasPagesTable = Schema::hasTable('pages');
        $hasPageCategoriesTable = Schema::hasTable('page_categories');

        if ($classNames->isEmpty() || (! $hasTextBlockTable && ! $hasPagesTable && ! $hasPageCategoriesTable)) {
            return ['blocks' => 0, 'pages_deleted' => 0, 'categories_deleted' => 0];
        }

        $classNames = $this->expandGrammarPageSeederClasses($classNames);

        $deletedBlocks = 0;
        $deletedPages = 0;
        $deletedCategories = 0;
        $processedPageIds = collect();
        $explicitCategoryIds = collect();
        $categoriesToEvaluate = collect();

        foreach ($classNames as $className) {
            if ($hasTextBlockTable) {
                TextBlock::query()
                    ->where('seeder', $className)
                    ->orderBy('id')
                    ->chunkById(100, function ($blocks) use (&$deletedBlocks) {
                        foreach ($blocks as $block) {
                            $block->delete();
                            $deletedBlocks++;
                        }
                    });
            }

            if ($hasPagesTable) {
                $pages = Page::query()
                    ->where('seeder', $className)
                    ->get();

                if ($pages->isEmpty()) {
                    $slug = $this->resolvePageSlugForSeeder($className);

                    if ($slug !== null) {
                        $page = Page::query()->where('slug', $slug)->first();

                        if ($page) {
                            $pages = collect([$page]);
                        }
                    }
                }

                foreach ($pages as $page) {
                    if ($processedPageIds->contains($page->id)) {
                        continue;
                    }

                    $processedPageIds->push($page->id);

                    if ($hasPageCategoriesTable && ! is_null($page->page_category_id)) {
                        $categoriesToEvaluate->put((int) $page->page_category_id, $page->page_category_id);
                    }

                    if ($hasTextBlockTable) {
                        $deletedBlocks += TextBlock::query()
                            ->where('page_id', $page->id)
                            ->delete();
                    }

                    $page->delete();
                    $deletedPages++;
                }
            }

            if (! $hasPageCategoriesTable) {
                continue;
            }

            $categories = PageCategory::query()
                ->where('seeder', $className)
                ->get();

            foreach ($categories as $category) {
                $categoriesToEvaluate->put($category->id, $category->id);
                $explicitCategoryIds->push($category->id);
            }
        }

        if ($hasPageCategoriesTable && $categoriesToEvaluate->isNotEmpty()) {
            $categoryIds = $categoriesToEvaluate->keys()->map(fn ($id) => (int) $id)->unique()->values();
            $explicitCategoryIds = $explicitCategoryIds->map(fn ($id) => (int) $id)->unique()->values();

            $categories = PageCategory::query()
                ->whereIn('id', $categoryIds)
                ->get()
                ->sortByDesc(fn (PageCategory $category) => $this->resolvePageCategoryDepth($category));

            foreach ($categories as $category) {
                $result = $this->deletePageCategoryRecord(
                    $category,
                    $explicitCategoryIds->contains($category->id),
                    $hasTextBlockTable,
                    $hasPagesTable,
                    $hasPageCategoriesTable
                );

                $deletedBlocks += $result['blocks'];
                $deletedCategories += $result['categories_deleted'];
            }
        }

        return [
            'blocks' => $deletedBlocks,
            'pages_deleted' => $deletedPages,
            'categories_deleted' => $deletedCategories,
        ];
    }

    protected function deletePageCategoryRecord(
        PageCategory $category,
        bool $forceDelete,
        bool $hasTextBlockTable,
        bool $hasPagesTable,
        bool $hasPageCategoriesTable,
    ): array {
        $category = PageCategory::query()->find($category->id);

        if (! $category) {
            return ['blocks' => 0, 'categories_deleted' => 0];
        }

        if (! $forceDelete) {
            $hasRemainingPages = $hasPagesTable
                && Page::query()->where('page_category_id', $category->id)->exists();
            $hasRemainingChildren = $hasPageCategoriesTable
                && PageCategory::query()->where('parent_id', $category->id)->exists();

            if ($hasRemainingPages || $hasRemainingChildren) {
                return ['blocks' => 0, 'categories_deleted' => 0];
            }
        }

        $deletedBlocks = 0;

        if ($hasTextBlockTable) {
            TextBlock::query()
                ->where('page_category_id', $category->id)
                ->whereNotNull('page_id')
                ->update(['page_category_id' => null]);

            $deletedBlocks += TextBlock::query()
                ->where('page_category_id', $category->id)
                ->whereNull('page_id')
                ->delete();
        }

        if ($hasPagesTable) {
            Page::query()
                ->where('page_category_id', $category->id)
                ->update(['page_category_id' => null]);
        }

        if ($hasPageCategoriesTable) {
            PageCategory::query()
                ->where('parent_id', $category->id)
                ->update(['parent_id' => null]);
        }

        $category->delete();

        return [
            'blocks' => $deletedBlocks,
            'categories_deleted' => 1,
        ];
    }

    protected function resolvePageCategoryDepth(PageCategory $category): int
    {
        $depth = 0;
        $parentId = $category->parent_id;
        $visited = [];

        while ($parentId && ! in_array($parentId, $visited, true)) {
            $visited[] = $parentId;
            $depth++;
            $parentId = PageCategory::query()
                ->whereKey($parentId)
                ->value('parent_id');
        }

        return $depth;
    }

    protected function expandGrammarPageSeederClasses(Collection $classNames): Collection
    {
        return $classNames
            ->flatMap(function ($className) {
                $classes = collect([$className]);

                if (! $this->ensureSeederClassIsLoaded($className)) {
                    return $classes;
                }

                if (is_subclass_of($className, GrammarPageSeederBase::class)) {
                    return $classes;
                }

                $aggregateClasses = $this->resolveAggregateSeederClasses($className);

                if ($aggregateClasses->isEmpty()) {
                    return $classes;
                }

                return $classes->merge($aggregateClasses);
            })
            ->filter(fn ($class) => is_string($class) && $class !== '')
            ->unique()
            ->values();
    }

    protected function resolvePageSlugForSeeder(string $className): ?string
    {
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return null;
        }

        try {
            $reflection = new \ReflectionClass($className);

            if ($reflection->isAbstract()) {
                return null;
            }

            if (! $reflection->isSubclassOf(GrammarPageSeederBase::class)) {
                return null;
            }

            $slug = $this->invokeSeederMethod($className, 'slug');

            return is_string($slug) ? $slug : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function resolveSeederFilePath(string $className): ?string
    {
        $candidatePaths = collect();

        if ($this->isVirtualLocalizationSeeder($className)) {
            $candidatePaths->push($this->virtualLocalizationFilePath($className));
        }

        $map = $this->getSeederClassMap();

        if ($map->has($className)) {
            $candidatePaths->push($map->get($className));
        }

        if (Str::startsWith($className, 'Database\\Seeders\\')) {
            $relative = Str::after($className, 'Database\\Seeders\\');
            $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
            $candidatePaths->push(database_path('seeders/' . $relativePath));
        }

        if (class_exists($className, false) || $this->classExistsSafely($className)) {
            try {
                $reflection = new \ReflectionClass($className);
                $fileName = $reflection->getFileName();

                if ($fileName) {
                    $candidatePaths->push($fileName);
                }
            } catch (\ReflectionException $exception) {
                report($exception);
            }
        }

        return $candidatePaths
            ->filter(fn ($path) => filled($path))
            ->map(fn ($path) => realpath($path) ?: $path)
            ->unique()
            ->first(function ($path) {
                return File::exists($path) && $this->isSeederFilePathAllowed($path);
            });
    }

    protected function isSeederFilePathAllowed(string $path): bool
    {
        $realPath = realpath($path);

        if ($realPath === false) {
            return false;
        }

        $basePath = realpath(base_path());

        if (! $basePath) {
            return false;
        }

        $normalizedBase = rtrim($basePath, DIRECTORY_SEPARATOR);

        if ($realPath === $normalizedBase) {
            return true;
        }

        return Str::startsWith($realPath, $normalizedBase . DIRECTORY_SEPARATOR);
    }

    protected function makeSeederFileDisplayPath(string $filePath): string
    {
        $realPath = realpath($filePath) ?: $filePath;
        $realPath = str_replace('\\', DIRECTORY_SEPARATOR, $realPath);
        $databaseSeedersPath = realpath(database_path('seeders'));

        if ($databaseSeedersPath) {
            $normalizedSeeders = rtrim(str_replace('\\', DIRECTORY_SEPARATOR, $databaseSeedersPath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if (Str::startsWith($realPath, $normalizedSeeders)) {
                $relative = Str::after($realPath, $normalizedSeeders);

                if ($relative !== $realPath) {
                    return 'database/seeders/' . str_replace(['\\', DIRECTORY_SEPARATOR], '/', $relative);
                }
            }
        }

        $basePath = realpath(base_path());

        if ($basePath) {
            $normalizedBase = rtrim(str_replace('\\', DIRECTORY_SEPARATOR, $basePath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if (Str::startsWith($realPath, $normalizedBase)) {
                $relative = Str::after($realPath, $normalizedBase);

                if ($relative !== $realPath) {
                    return ltrim(str_replace(['\\', DIRECTORY_SEPARATOR], '/', $relative), '/');
                }
            }
        }

        return basename($realPath);
    }

    protected function classProvidesGrammarPages(string $className): bool
    {
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return false;
        }

        if (is_subclass_of($className, GrammarPageSeederBase::class)) {
            return true;
        }

        return $this->resolveAggregateSeederClasses($className)
            ->contains(fn ($class) => is_string($class) && $this->ensureSeederClassIsLoaded($class) && class_exists($class, false) && is_subclass_of($class, GrammarPageSeederBase::class));
    }

    protected function resolveAggregateSeederClasses(string $className): Collection
    {
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return collect();
        }

        try {
            $reflection = new \ReflectionClass($className);

            $constant = collect($reflection->getReflectionConstants())
                ->firstWhere(fn (\ReflectionClassConstant $constant) => $constant->getName() === 'SEEDERS');

            if (! $constant) {
                return collect();
            }

            $value = $constant->getValue();

            if (! is_array($value)) {
                return collect();
            }

            return collect($value)
                ->filter(fn ($class) => is_string($class) && $class !== '');
        } catch (\Throwable) {
            return collect();
        }
    }

    private function discoverSeederClasses(string $directory): array
    {
        $normalizedDirectory = str_replace('\\', '/', realpath($directory) ?: $directory);
        $virtualLocalizations = collect($this->virtualLocalizationClasses())
            ->filter(function (string $className) use ($normalizedDirectory) {
                $path = $this->virtualLocalizationFilePath($className);

                if (! $path) {
                    return false;
                }

                $normalizedPath = str_replace('\\', '/', realpath($path) ?: $path);

                return $normalizedPath === $normalizedDirectory
                    || Str::startsWith($normalizedPath, rtrim($normalizedDirectory, '/') . '/');
            });

        if (! is_dir($directory)) {
            return $virtualLocalizations->values()->all();
        }

        return $this->getSeederClassMap()
            ->filter(fn (string $path, string $class) => $this->isInstantiableSeeder($class, $path))
            ->keys()
            ->merge($virtualLocalizations)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function classFromFile(SplFileInfo $file): ?string
    {
        $contents = File::get($file->getPathname());

        if ($contents === false) {
            return null;
        }

        if (! preg_match('/^namespace\s+([^;]+);/m', $contents, $namespaceMatch)) {
            return null;
        }

        if (! preg_match('/^\s*(?:final\s+)?(?:abstract\s+)?class\s+(\w+)/mi', $contents, $classMatch)) {
            return null;
        }

        $namespace = trim($namespaceMatch[1]);
        $className = trim($classMatch[1]);

        if ($namespace === '' || $className === '') {
            return null;
        }

        return $namespace . '\\' . $className;
    }

    private function isInstantiableSeeder(string $class, ?string $filePath = null): bool
    {
        try {
            if (! $this->ensureSeederClassIsLoaded($class)) {
                if (! $filePath) {
                    $filePath = $this->getSeederClassMap()->get($class);
                }

                if (! $filePath || ! is_file($filePath)) {
                    return false;
                }

                try {
                    require_once $filePath;
                } catch (\Throwable) {
                    return false;
                }

                if (! class_exists($class, false)) {
                    return false;
                }
            }

            $reflection = new \ReflectionClass($class);

            if (! $reflection->isInstantiable()) {
                return false;
            }

            return $reflection->isSubclassOf(\Illuminate\Database\Seeder::class);
        } catch (\ReflectionException) {
            return false;
        }
    }

    protected function classExistsSafely(string $className): bool
    {
        if (class_exists($className, false)) {
            return true;
        }

        return @class_exists($className);
    }

    private function getSeederClassMap(): Collection
    {
        if (is_array($this->seederClassMap)) {
            return collect($this->seederClassMap);
        }

        $directory = database_path('seeders');

        if (! is_dir($directory)) {
            $this->seederClassMap = [];

            return collect();
        }

        $map = collect(File::allFiles($directory))
            ->filter(fn (SplFileInfo $file) => $file->getExtension() === 'php')
            ->mapWithKeys(function (SplFileInfo $file) {
                $class = $this->classFromFile($file);

                return $class ? [$class => $file->getPathname()] : [];
            })
            ->all();

        $this->seederClassMap = $map;

        return collect($map);
    }

    public function ensureSeederClassIsLoaded(string $className): bool
    {
        if ($this->isVirtualLocalizationSeeder($className)) {
            return true;
        }

        if ($this->classExistsSafely($className)) {
            return true;
        }

        $filePath = $this->resolveSeederFilePath($className);

        if (! $filePath) {
            $filePath = $this->getSeederClassMap()->get($className);
        }

        if (! $filePath || ! is_file($filePath)) {
            return false;
        }

        try {
            require_once $filePath;
        } catch (\Throwable) {
            return false;
        }

        return $this->classExistsSafely($className);
    }
}
