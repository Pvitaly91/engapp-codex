<?php

namespace App\Modules\SeedRunsV2\Services;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\TextBlock;
use App\Services\SeederPromptTheoryPageResolver;
use App\Services\SeederTestTargetResolver;
use App\Services\QuestionDeletionService;
use App\Support\Database\JsonPageLocalizationManager;
use App\Support\Database\JsonPageSeeder;
use App\Support\Database\JsonTestLocalizationManager;
use App\Support\PackageSeed\DryRunRollbackException;
use Database\Seeders\Page_v2\Concerns\GrammarPageSeeder as GrammarPageSeederBase;
use Database\Seeders\Page_v2\Concerns\PageCategoryDescriptionSeeder as PageCategoryDescriptionSeederBase;
use Database\Seeders\QuestionSeeder as QuestionSeederBase;
use Illuminate\Database\Seeder as LaravelSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
        private SeederPromptTheoryPageResolver $seederPromptTheoryPageResolver,
        private SeederTestTargetResolver $seederTestTargetResolver,
        private JsonTestLocalizationManager $jsonTestLocalizationManager,
        private JsonPageLocalizationManager $jsonPageLocalizationManager,
    )
    {
    }

    protected function isVirtualLocalizationSeeder(string $className): bool
    {
        return $this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)
            || $this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className);
    }

    protected function virtualLocalizationType(string $className): ?string
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return 'question_localizations';
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return 'page_localizations';
        }

        return null;
    }

    protected function virtualLocalizationClasses(): array
    {
        return collect($this->jsonTestLocalizationManager->virtualSeederClasses())
            ->merge($this->jsonPageLocalizationManager->virtualSeederClasses())
            ->unique()
            ->values()
            ->all();
    }

    protected function virtualLocalizationFilePath(string $className): ?string
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->filePathForClass($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->filePathForClass($className);
        }

        return null;
    }

    protected function applyVirtualLocalizationSeeder(string $className): array
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->applyVirtualSeeder($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->applyVirtualSeeder($className);
        }

        throw new \RuntimeException(__('Localization seeder :class was not found.', ['class' => $className]));
    }

    protected function removeVirtualLocalizationSeederData(string $className): array
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->removeVirtualSeederData($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->removeVirtualSeederData($className);
        }

        return [];
    }

    protected function localizationTargetSeederClass(string $className): ?string
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->targetSeederClassForVirtualSeeder($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->targetSeederClassForVirtualSeeder($className);
        }

        return null;
    }

    protected function localizationDescriptorForClass(string $className): ?array
    {
        if ($this->jsonTestLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonTestLocalizationManager->descriptorForClass($className);
        }

        if ($this->jsonPageLocalizationManager->isVirtualLocalizationSeeder($className)) {
            return $this->jsonPageLocalizationManager->descriptorForClass($className);
        }

        return null;
    }

    protected function normalizeLocalizationLocale(?string $locale): string
    {
        $normalized = strtolower(trim((string) $locale));

        return $normalized === 'ua' ? 'uk' : $normalized;
    }

    protected function localizationLocaleLabel(?string $locale): string
    {
        $normalized = $this->normalizeLocalizationLocale($locale);

        return $normalized !== '' ? Str::upper($normalized) : __('N/A');
    }

    protected function localizationTypeLabel(?string $type): string
    {
        return match ($type) {
            'question_localizations' => __('Локалізація питань'),
            'page_localizations' => __('Локалізація сторінки'),
            default => __('Локалізація'),
        };
    }

    protected function buildRelatedLocalizationMap(Collection $executedSeeders): Collection
    {
        return $executedSeeders
            ->map(function ($seedRun) {
                $className = (string) ($seedRun->class_name ?? '');
                $profileType = (string) data_get($seedRun, 'data_profile.type', '');

                if (! in_array($profileType, ['question_localizations', 'page_localizations'], true)) {
                    return null;
                }

                $targetClassName = $this->localizationTargetSeederClass($className);

                if (! filled($targetClassName)) {
                    return null;
                }

                $descriptor = $this->localizationDescriptorForClass($className);
                $locale = $this->normalizeLocalizationLocale($descriptor['locale'] ?? null);

                return [
                    'seed_run_id' => (int) ($seedRun->id ?? 0),
                    'class_name' => $className,
                    'display_name' => (string) ($seedRun->display_class_name ?? $this->formatSeederClassName($className)),
                    'display_basename' => (string) ($seedRun->display_class_basename ?? class_basename($className)),
                    'target_class_name' => $targetClassName,
                    'locale' => $locale,
                    'locale_label' => $this->localizationLocaleLabel($locale),
                    'type' => $profileType,
                    'type_label' => $this->localizationTypeLabel($profileType),
                    'ran_at' => $seedRun->ran_at_formatted
                        ?? optional($seedRun->ran_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->filter()
            ->groupBy('target_class_name')
            ->map(function (Collection $items) {
                return $items
                    ->sortBy(fn (array $item) => sprintf(
                        '%s|%s|%010d',
                        $item['locale'] ?? '',
                        $item['display_name'] ?? '',
                        (int) ($item['seed_run_id'] ?? 0)
                    ))
                    ->values();
            });
    }

    protected function buildAvailableLocalizationMap(iterable $targetSeeders): Collection
    {
        $targetLookup = collect($targetSeeders)
            ->map(function ($seeder) {
                if (is_string($seeder)) {
                    return trim($seeder);
                }

                return trim((string) data_get($seeder, 'class_name', ''));
            })
            ->filter()
            ->flip();

        if ($targetLookup->isEmpty()) {
            return collect();
        }

        return collect($this->virtualLocalizationClasses())
            ->map(function (string $className) {
                $type = $this->virtualLocalizationType($className);

                if (! in_array($type, ['question_localizations', 'page_localizations'], true)) {
                    return null;
                }

                $targetClassName = $this->localizationTargetSeederClass($className);

                if (! filled($targetClassName)) {
                    return null;
                }

                $descriptor = $this->localizationDescriptorForClass($className);
                $locale = $this->normalizeLocalizationLocale($descriptor['locale'] ?? null);

                return [
                    'class_name' => $className,
                    'display_name' => $this->formatSeederClassName($className),
                    'display_basename' => class_basename($className),
                    'target_class_name' => $targetClassName,
                    'locale' => $locale,
                    'locale_label' => $this->localizationLocaleLabel($locale),
                    'type' => $type,
                    'type_label' => $this->localizationTypeLabel($type),
                ];
            })
            ->filter(fn (?array $item) => $item !== null && $targetLookup->has($item['target_class_name'] ?? ''))
            ->groupBy('target_class_name')
            ->map(function (Collection $items) {
                return $items
                    ->sortBy(fn (array $item) => sprintf(
                        '%s|%s|%s',
                        $item['locale'] ?? '',
                        $item['display_name'] ?? '',
                        $item['class_name'] ?? ''
                    ))
                    ->values();
            });
    }

    protected function buildPendingLocalizationMap(iterable $targetSeeders, iterable $executedClassNames): Collection
    {
        $executedLookup = collect($executedClassNames)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->flip();

        return $this->buildAvailableLocalizationMap($targetSeeders)
            ->map(function (Collection $items) use ($executedLookup) {
                return $items
                    ->reject(fn (array $item) => $executedLookup->has((string) ($item['class_name'] ?? '')))
                    ->values();
            })
            ->filter(fn (Collection $items) => $items->isNotEmpty());
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

    public function normalizeSeederTab(?string $tab): string
    {
        return in_array($tab, ['localizations', 'theory-tests'], true) ? $tab : 'main';
    }

    protected function seederTabForClass(string $className): string
    {
        return in_array($this->virtualLocalizationType($className), ['question_localizations', 'page_localizations'], true)
            ? 'localizations'
            : 'main';
    }

    protected function filterSeedersForTab(Collection $seeders, string $tab): Collection
    {
        $normalizedTab = $this->normalizeSeederTab($tab);

        return $seeders
            ->filter(function ($seeder) use ($normalizedTab) {
                $className = (string) data_get($seeder, 'class_name', '');
                $seederTab = (string) data_get($seeder, 'seeder_tab', '');

                if ($seederTab === '' && $className !== '') {
                    $seederTab = $this->seederTabForClass($className);
                }

                return $this->normalizeSeederTab($seederTab) === $normalizedTab;
            })
            ->values();
    }

    protected function buildTheoryTestPages(Collection $executedSeeders, ?Collection $pendingSeeders = null): Collection
    {
        $pendingSeeders ??= collect();

        return $executedSeeders
            ->map(fn ($seedRun) => ['status' => 'executed', 'seeder' => $seedRun])
            ->merge($pendingSeeders->map(fn ($pendingSeeder) => ['status' => 'pending', 'seeder' => $pendingSeeder]))
            ->filter(function (array $entry) {
                return filled(data_get($entry, 'seeder.prompt_theory_target.url'))
                    || filled(data_get($entry, 'seeder.prompt_theory_target.title'));
            })
            ->groupBy(function (array $entry) {
                $url = trim((string) data_get($entry, 'seeder.prompt_theory_target.url', ''));

                if ($url !== '') {
                    return $url;
                }

                return trim((string) data_get(
                    $entry,
                    'seeder.prompt_theory_target.title',
                    data_get($entry, 'seeder.class_name', 'unknown-seeder')
                ));
            })
            ->map(function (Collection $seeders, string $groupKey) {
                $executedPageSeeders = $seeders
                    ->filter(fn (array $entry) => ($entry['status'] ?? null) === 'executed')
                    ->map(fn (array $entry) => $entry['seeder'])
                    ->sortByDesc(fn ($seedRun) => optional($seedRun->ran_at)->timestamp ?? 0)
                    ->values();

                $pendingPageSeeders = $seeders
                    ->filter(fn (array $entry) => ($entry['status'] ?? null) === 'pending')
                    ->map(fn (array $entry) => $entry['seeder'])
                    ->sortBy(fn ($pendingSeeder) => Str::lower((string) data_get(
                        $pendingSeeder,
                        'display_class_name',
                        data_get($pendingSeeder, 'class_name', '')
                    )))
                    ->values();

                $sortedSeeders = $executedPageSeeders
                    ->concat($pendingPageSeeders)
                    ->values();

                $firstSeeder = $executedPageSeeders->first() ?? $pendingPageSeeders->first();

                return [
                    'group_key' => $groupKey,
                    'page' => [
                        'label' => (string) data_get($firstSeeder, 'prompt_theory_target.label', __('Пов’язана сторінка теорії')),
                        'title' => (string) data_get($firstSeeder, 'prompt_theory_target.title', $groupKey),
                        'url' => (string) data_get($firstSeeder, 'prompt_theory_target.url', ''),
                    ],
                    'seeders' => $sortedSeeders,
                    'executed_seeders' => $executedPageSeeders,
                    'pending_seeders' => $pendingPageSeeders,
                    'seeders_count' => $sortedSeeders->count(),
                    'executed_seeders_count' => $executedPageSeeders->count(),
                    'pending_seeders_count' => $pendingPageSeeders->count(),
                    'question_count' => $sortedSeeders->sum(fn ($seedRun) => (int) ($seedRun->question_count ?? 0)),
                    'tests_count' => $sortedSeeders
                        ->pluck('test_target.url')
                        ->filter(fn ($url) => filled($url))
                        ->unique()
                        ->count(),
                    'latest_ran_at' => $sortedSeeders->max(
                        fn ($seedRun) => optional(data_get($seedRun, 'ran_at'))->timestamp ?? 0
                    ),
                ];
            })
            ->sort(function (array $left, array $right) {
                return [
                    $right['latest_ran_at'] ?? 0,
                    Str::lower((string) data_get($left, 'page.title', '')),
                ] <=> [
                    $left['latest_ran_at'] ?? 0,
                    Str::lower((string) data_get($right, 'page.title', '')),
                ];
            })
            ->values();
    }

    protected function buildSeederTabCounts(
        Collection $pendingSeeders,
        Collection $executedSeeders,
        ?Collection $theoryTestPages = null
    ): array
    {
        $counts = [];

        foreach (['main', 'localizations'] as $tab) {
            $pendingCount = $this->filterSeedersForTab($pendingSeeders, $tab)->count();
            $executedCount = $this->filterSeedersForTab($executedSeeders, $tab)->count();

            $counts[$tab] = [
                'pending' => $pendingCount,
                'executed' => $executedCount,
                'total' => $pendingCount + $executedCount,
            ];
        }

        $theoryTestPages ??= collect();
        $theoryPagesCount = $theoryTestPages->count();

        $counts['theory-tests'] = [
            'pending' => $theoryTestPages
                ->filter(fn (array $pageGroup) => (int) ($pageGroup['pending_seeders_count'] ?? 0) > 0)
                ->count(),
            'executed' => $theoryTestPages
                ->filter(fn (array $pageGroup) => (int) ($pageGroup['executed_seeders_count'] ?? 0) > 0)
                ->count(),
            'total' => $theoryPagesCount,
        ];

        return $counts;
    }

    protected function buildPendingSeederState(string $className, iterable $executedClassNames): array
    {
        $localizationType = $this->virtualLocalizationType($className);
        $requiredBaseSeeder = $this->localizationTargetSeederClass($className);
        $executedLookup = collect($executedClassNames)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->flip();

        $canExecute = true;
        $blockedReason = null;

        if (
            in_array($localizationType, ['question_localizations', 'page_localizations'], true)
            && filled($requiredBaseSeeder)
            && ! $executedLookup->has($requiredBaseSeeder)
        ) {
            $canExecute = false;
            $blockedReason = __('Спочатку виконайте основний сидер :seeder.', [
                'seeder' => $this->formatSeederClassName($requiredBaseSeeder),
            ]);
        }

        return [
            'seeder_tab' => $this->seederTabForClass($className),
            'required_base_seeder' => $requiredBaseSeeder,
            'required_base_display_name' => filled($requiredBaseSeeder)
                ? $this->formatSeederClassName($requiredBaseSeeder)
                : null,
            'can_execute' => $canExecute,
            'execution_block_reason' => $blockedReason,
        ];
    }

    protected function executionBlockedMessageForSeeder(string $className, iterable $executedClassNames): ?string
    {
        $state = $this->buildPendingSeederState($className, $executedClassNames);

        return ($state['can_execute'] ?? true)
            ? null
            : (string) ($state['execution_block_reason'] ?? __('Сидер тимчасово недоступний для виконання.'));
    }

    protected function executedSeederClasses(): Collection
    {
        if (! Schema::hasTable('seed_runs')) {
            return collect();
        }

        return DB::table('seed_runs')
            ->pluck('class_name')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();
    }

    public function assembleSeedRunOverview(?string $activeTab = null): array
    {
        $activeTab = $this->normalizeSeederTab($activeTab);
        $tableExists = Schema::hasTable('seed_runs');
        $executedSeeders = collect();
        $pendingSeeders = collect();
        $executedSeederHierarchy = collect();
        $recentSeedRunOrdinals = collect();
        $recentThreshold = now()->subDay();
        $seederTabCounts = $this->buildSeederTabCounts($pendingSeeders, $executedSeeders);
        $runnablePendingCount = 0;

        if (! $tableExists) {
            return [
                'tableExists' => false,
                'executedSeeders' => $executedSeeders,
                'pendingSeeders' => $pendingSeeders,
                'pendingSeederHierarchy' => collect(),
                'executedSeederHierarchy' => $executedSeederHierarchy,
                'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
                'activeSeederTab' => $activeTab,
                'seederTabCounts' => $seederTabCounts,
                'runnablePendingCount' => $runnablePendingCount,
                'theoryTestPages' => collect(),
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
                [$namespace, $baseName] = $this->splitSeederDisplayName($seedRun->display_class_name);
                $seedRun->display_class_namespace = $namespace;
                $seedRun->display_class_basename = $baseName;
                $seedRun->seeder_tab = $this->seederTabForClass($seedRun->class_name);

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
            ->filter()
            ->values();

        $pendingSeeders = collect($this->discoverSeederClasses(database_path('seeders')))
            ->reject(fn (string $class) => $executedClasses->contains($class))
            ->map(function (string $class) use ($executedClasses) {
                $displayName = $this->formatSeederClassName($class);
                [$namespace, $baseName] = $this->splitSeederDisplayName($displayName);
                $dataProfile = $this->describeSeederData($class);
                $dependencyState = $this->buildPendingSeederState($class, $executedClasses);

                return (object) array_merge([
                    'class_name' => $class,
                    'display_class_name' => $displayName,
                    'display_class_namespace' => $namespace,
                    'display_class_basename' => $baseName,
                    'supports_preview' => $this->seederSupportsPreview($class),
                    'data_type' => $dataProfile['type'] ?? 'unknown',
                ], $dependencyState);
            })
            ->values();

        $allDiscoveredSeederClasses = $executedClasses
            ->merge($pendingSeeders->pluck('class_name'))
            ->filter()
            ->unique()
            ->values();

        $promptTheoryTargets = $this->seederPromptTheoryPageResolver->resolveForSeeders(
            $allDiscoveredSeederClasses->all()
        );
        $testTargets = $this->seederTestTargetResolver->resolveForSeeders($allDiscoveredSeederClasses->all());

        $availableLocalizationMap = $this->buildAvailableLocalizationMap($pendingSeeders);

        $pendingSeeders = $pendingSeeders->map(function ($pendingSeeder) use ($availableLocalizationMap, $promptTheoryTargets, $testTargets) {
            $availableLocalizations = $availableLocalizationMap->get($pendingSeeder->class_name, collect());
            $pendingSeeder->available_localizations = $availableLocalizations instanceof Collection
                ? $availableLocalizations->all()
                : [];
            $pendingSeeder->available_localizations_count = count($pendingSeeder->available_localizations);
            $pendingSeeder->prompt_theory_target = $promptTheoryTargets->get($pendingSeeder->class_name);
            $pendingSeeder->test_target = $testTargets->get($pendingSeeder->class_name);

            return $pendingSeeder;
        });

        $questionCounts = collect();

        if (Schema::hasColumn('questions', 'seeder') && $executedSeeders->isNotEmpty()) {
            $questionCounts = Question::query()
                ->select('seeder', DB::raw('COUNT(*) as aggregate'))
                ->whereIn('seeder', $executedClasses->all())
                ->groupBy('seeder')
                ->pluck('aggregate', 'seeder');
        }

        $theoryPageTargets = $executedClasses->isEmpty()
            ? collect()
            : Page::query()
                ->with('category')
                ->whereIn('seeder', $executedClasses->all())
                ->where('type', 'theory')
                ->get()
                ->keyBy('seeder');

        $theoryCategoryTargets = $executedClasses->isEmpty()
            ? collect()
            : PageCategory::query()
                ->whereIn('seeder', $executedClasses->all())
                ->where('type', 'theory')
                ->get()
                ->keyBy('seeder');

        $executedSeeders = $executedSeeders->map(function ($seedRun) use ($questionCounts, $theoryPageTargets, $theoryCategoryTargets, $promptTheoryTargets) {
            $seedRun->data_profile = $this->describeSeederData($seedRun->class_name);
            $seedRun->question_count = ($seedRun->data_profile['type'] ?? 'unknown') === 'questions'
                ? (int) ($questionCounts[$seedRun->class_name] ?? 0)
                : 0;
            $seedRun->theory_target = $this->resolveTheoryTargetForSeeder(
                $seedRun->class_name,
                $theoryPageTargets,
                $theoryCategoryTargets
            );
            $seedRun->prompt_theory_target = $promptTheoryTargets->get($seedRun->class_name);

            return $seedRun;
        });

        $executedSeeders = $executedSeeders->map(function ($seedRun) use ($testTargets) {
            $seedRun->test_target = $testTargets->get($seedRun->class_name);

            return $seedRun;
        });

        $relatedLocalizationMap = $this->buildRelatedLocalizationMap($executedSeeders);
        $pendingLocalizationMap = $this->buildPendingLocalizationMap($executedSeeders, $executedClasses);

        $executedSeeders = $executedSeeders->map(function ($seedRun) use ($relatedLocalizationMap, $pendingLocalizationMap) {
            $relatedLocalizations = $relatedLocalizationMap->get($seedRun->class_name, collect());
            $seedRun->related_localizations = $relatedLocalizations instanceof Collection
                ? $relatedLocalizations->all()
                : [];
            $seedRun->related_localizations_count = count($seedRun->related_localizations);
            $pendingLocalizations = $pendingLocalizationMap->get($seedRun->class_name, collect());
            $seedRun->pending_localizations = $pendingLocalizations instanceof Collection
                ? $pendingLocalizations->all()
                : [];
            $seedRun->pending_localizations_count = count($seedRun->pending_localizations);

            return $seedRun;
        });

        $theoryTestPages = $this->buildTheoryTestPages($executedSeeders, $pendingSeeders);
        $seederTabCounts = $this->buildSeederTabCounts($pendingSeeders, $executedSeeders, $theoryTestPages);

        if ($activeTab === 'theory-tests') {
            $pendingSeeders = collect();
            $executedSeeders = collect();
            $runnablePendingCount = 0;
            $pendingSeederHierarchy = collect();
            $executedSeederHierarchy = collect();
        } else {
            $pendingSeeders = $this->filterSeedersForTab($pendingSeeders, $activeTab);
            $executedSeeders = $this->filterSeedersForTab($executedSeeders, $activeTab);
            $runnablePendingCount = $pendingSeeders
                ->filter(fn ($seeder) => (bool) data_get($seeder, 'can_execute', true))
                ->count();
            $pendingSeederHierarchy = $this->buildPendingSeederHierarchy($pendingSeeders);
            $executedSeederHierarchy = $this->buildSeederHierarchy($executedSeeders);
        }

        return [
            'tableExists' => true,
            'executedSeeders' => $executedSeeders,
            'pendingSeeders' => $pendingSeeders,
            'pendingSeederHierarchy' => $pendingSeederHierarchy,
            'executedSeederHierarchy' => $executedSeederHierarchy,
            'recentSeedRunOrdinals' => $recentSeedRunOrdinals,
            'activeSeederTab' => $activeTab,
            'seederTabCounts' => $seederTabCounts,
            'runnablePendingCount' => $runnablePendingCount,
            'theoryTestPages' => $theoryTestPages,
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

        $blockedMessage = $this->executionBlockedMessageForSeeder($className, $this->executedSeederClasses());

        if ($blockedMessage !== null) {
            return [
                'success' => false,
                'message' => $blockedMessage,
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
            $this->executeConcreteSeeder($className);
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

    public function refreshSeedersInFolder(array $classNames, ?string $folderLabel = null): array
    {
        $selectedClasses = collect($classNames)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($selectedClasses->isEmpty()) {
            return [
                'success' => false,
                'message' => __('No seeders were selected.'),
                'refreshed' => [],
                'ordered' => [],
                'errors' => [],
                'test_targets' => [],
            ];
        }

        $result = $this->refreshExecutedSeeders($selectedClasses);

        if (($result['selected_ran'] ?? collect())->isEmpty()) {
            return [
                'success' => false,
                'message' => $result['message'] ?? __('Не вдалося оновити дані сидерів у папці.'),
                'refreshed' => [],
                'ordered' => ($result['ordered'] ?? collect())->all(),
                'errors' => ($result['errors'] ?? collect())->all(),
                'test_targets' => [],
            ];
        }

        $label = $this->resolveFolderLabel($folderLabel);
        $message = __('Оновлено дані :count сидер(ів) у папці :folder.', [
            'count' => ($result['selected_ran'] ?? collect())->count(),
            'folder' => $label,
        ]);

        $localizationsRan = (int) ($result['localizations_ran_total'] ?? 0);
        if ($localizationsRan > 0) {
            $message .= ' ' . __('Повторно виконано :count сидер(ів) локалізації.', [
                'count' => $localizationsRan,
            ]);
        }

        if (($result['questions_deleted'] ?? 0) > 0) {
            $message .= ' ' . __('Видалено :count пов’язаних питань.', ['count' => $result['questions_deleted']]);
        }

        if (($result['blocks_deleted'] ?? 0) > 0) {
            $message .= ' ' . __('Видалено :count пов’язаних текстових блоків.', ['count' => $result['blocks_deleted']]);
        }

        if (($result['pages_deleted'] ?? 0) > 0) {
            $message .= ' ' . __('Видалено :count пов’язаних сторінок.', ['count' => $result['pages_deleted']]);
        }

        if (($result['categories_deleted'] ?? 0) > 0) {
            $message .= ' ' . __('Видалено :count пов’язаних категорій.', ['count' => $result['categories_deleted']]);
        }

        if (($result['hints_deleted'] ?? 0) > 0) {
            $message .= ' ' . __('Видалено :count пов’язаних підказок.', ['count' => $result['hints_deleted']]);
        }

        if (($result['explanations_deleted'] ?? 0) > 0) {
            $message .= ' ' . __('Видалено :count пов’язаних пояснень.', ['count' => $result['explanations_deleted']]);
        }

        $errors = $result['errors'] ?? collect();

        return [
            'success' => true,
            'status' => $errors->isNotEmpty() ? 'partial' : 'success',
            'message' => $message,
            'refreshed' => ($result['selected_ran'] ?? collect())->all(),
            'executed' => ($result['ran'] ?? collect())->all(),
            'ordered' => ($result['ordered'] ?? collect())->all(),
            'errors' => $errors->all(),
            'questions_deleted' => $result['questions_deleted'] ?? 0,
            'blocks_deleted' => $result['blocks_deleted'] ?? 0,
            'pages_deleted' => $result['pages_deleted'] ?? 0,
            'categories_deleted' => $result['categories_deleted'] ?? 0,
            'hints_deleted' => $result['hints_deleted'] ?? 0,
            'explanations_deleted' => $result['explanations_deleted'] ?? 0,
            'test_targets' => $this->resolveTestTargetsForClasses($result['ran'] ?? collect()),
        ];
    }

    /**
     * @param  iterable<int|string, mixed>  $classNames
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function refreshSeedersByClassNames(iterable $classNames, array $options = []): array
    {
        $selectedClasses = collect($classNames)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        return $this->refreshExecutedSeeders($selectedClasses, $options);
    }

    /**
     * @param  iterable<int, string>  $classNames
     * @return array<string, int>
     */
    public function deleteSeedDataForClasses(iterable $classNames): array
    {
        $normalized = collect($classNames)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return [
                'questions_deleted' => 0,
                'blocks_deleted' => 0,
                'pages_deleted' => 0,
                'categories_deleted' => 0,
                'hints_deleted' => 0,
                'explanations_deleted' => 0,
            ];
        }

        return $this->deleteSeederDataForRefresh($normalized);
    }

    /**
     * @param  iterable<int, string>  $classNames
     * @return array{questions_deleted:int}
     */
    public function deleteQuestionDataForClasses(iterable $classNames): array
    {
        $normalized = collect($classNames)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return [
                'questions_deleted' => 0,
            ];
        }

        return [
            'questions_deleted' => $this->deleteQuestionsForSeeders($normalized),
        ];
    }

    /**
     * @return Collection<int, string>
     */
    public function relatedLocalizationClassesForTargetSeeder(
        string $className,
        ?string $expectedType = null,
    ): Collection {
        $normalizedClassName = trim($className);
        $normalizedExpectedType = trim((string) $expectedType);

        if ($normalizedClassName === '') {
            return collect();
        }

        $available = $this->buildAvailableLocalizationMap([$normalizedClassName])
            ->get($normalizedClassName, collect());

        return collect($available)
            ->filter(function (array $item) use ($normalizedExpectedType): bool {
                if ($normalizedExpectedType === '') {
                    return true;
                }

                return trim((string) ($item['type'] ?? '')) === $normalizedExpectedType;
            })
            ->pluck('class_name')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();
    }

    public function runMissingSeeders(?string $activeTab = null): array
    {
        if (! Schema::hasTable('seed_runs')) {
            return [
                'success' => false,
                'message' => __('The seed_runs table does not exist.'),
                'executed' => [],
                'test_targets' => [],
            ];
        }

        $activeTab = $this->normalizeSeederTab($activeTab);
        $executedClasses = $this->executedSeederClasses();

        $pendingSeeders = collect($this->discoverSeederClasses(database_path('seeders')))
            ->reject(fn (string $class) => $executedClasses->contains($class))
            ->filter(fn (string $class) => $this->seederTabForClass($class) === $activeTab)
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
        $executedClasses = $this->executedSeederClasses();

        foreach ($orderedClassNames as $className) {
            if (! $this->ensureSeederClassIsLoaded($className)) {
                $errors->push(__('Seeder :class is not autoloadable.', ['class' => $className]));
                continue;
            }

            $blockedMessage = $this->executionBlockedMessageForSeeder($className, $executedClasses);

            if ($blockedMessage !== null) {
                $errors->push($blockedMessage);
                continue;
            }

            if ($this->isVirtualLocalizationSeeder($className)) {
                try {
                    $this->applyVirtualLocalizationSeeder($className);
                    $this->logVirtualSeederRun($className);
                    $ran->push($className);
                    $executedClasses->push($className);
                } catch (\Throwable $exception) {
                    report($exception);
                    $errors->push($this->formatSeederExecutionError($className, $exception));
                }

                continue;
            }

            $filePath = $this->resolveSeederFilePath($className);

            if (! $this->isInstantiableSeeder($className, $filePath)) {
                $errors->push(__('Seeder :class cannot be executed.', ['class' => $className]));
                continue;
            }

            try {
                $this->executeConcreteSeeder($className);
                $ran->push($className);
                $executedClasses->push($className);
            } catch (\Throwable $exception) {
                report($exception);
                $errors->push($this->formatSeederExecutionError($className, $exception));
            }
        }

        return [
            'ordered' => $orderedClassNames,
            'ran' => $ran,
            'errors' => $errors,
        ];
    }

    protected function executeConcreteSeeder(string $className): void
    {
        $seeder = app()->make($className);

        if (! $seeder instanceof LaravelSeeder || ! method_exists($seeder, 'run')) {
            throw new \RuntimeException(__('Seeder :class cannot be executed.', ['class' => $className]));
        }

        $seeder->setContainer(app());
        $seeder->run();
        $this->logVirtualSeederRun($className);
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

            $requiredBaseSeeder = trim((string) ($metadata['required_base_seeder'] ?? ''));
            if ($requiredBaseSeeder !== '' && $normalized->contains($requiredBaseSeeder)) {
                $dependencies->push($requiredBaseSeeder);
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
            $metadata['required_base_seeder'] = $this->localizationTargetSeederClass($className);

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

        $blockedMessage = $this->executionBlockedMessageForSeeder($className, $this->executedSeederClasses());

        if ($blockedMessage !== null) {
            return [
                'success' => false,
                'message' => $blockedMessage,
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
        $deletedHints = 0;
        $deletedExplanations = 0;
        $profile = $this->describeSeederData($seedRun->class_name);

        try {
            DB::transaction(function () use (
                $seedRun,
                &$deletedQuestions,
                &$deletedBlocks,
                &$deletedPages,
                &$deletedCategories,
                &$deletedHints,
                &$deletedExplanations,
                $profile
            ) {
                $classNames = collect([$seedRun->class_name]);

                if ($profile['type'] === 'question_localizations') {
                    $result = $this->removeVirtualLocalizationSeederData($seedRun->class_name);
                    $deletedHints = (int) ($result['deleted_hints'] ?? 0);
                    $deletedExplanations = (int) ($result['deleted_explanations'] ?? 0);
                } elseif ($profile['type'] === 'questions') {
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
            'question_localizations' => __('Deleted seed run and :hints hint(s), :explanations explanation(s).', [
                'hints' => $deletedHints,
                'explanations' => $deletedExplanations,
            ]),
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
            'deleted_hints' => $deletedHints,
            'deleted_explanations' => $deletedExplanations,
            'test_targets' => $this->resolveTestTargetsForClasses([$seedRun->class_name]),
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
        $profile = $this->describeSeederData($seedRun->class_name);
        $result = $this->refreshExecutedSeeders(collect([$className]));

        if (! ($result['selected_ran'] ?? collect())->contains($className)) {
            return [
                'success' => false,
                'message' => ($result['errors'] ?? collect())->isNotEmpty()
                    ? $this->formatDetailedErrorMessage($result['errors'])
                    : ($result['message'] ?? __('Не вдалося оновити дані сидера.')),
                'errors' => ($result['errors'] ?? collect())->all(),
            ];
        }

        $deletedQuestions = (int) ($result['questions_deleted'] ?? 0);
        $deletedBlocks = (int) ($result['blocks_deleted'] ?? 0);
        $deletedPages = (int) ($result['pages_deleted'] ?? 0);
        $deletedCategories = (int) ($result['categories_deleted'] ?? 0);
        $deletedHints = (int) ($result['hints_deleted'] ?? 0);
        $deletedExplanations = (int) ($result['explanations_deleted'] ?? 0);

        $message = match ($profile['type']) {
            'question_localizations' => __('Refreshed localization seeder :class. Deleted :hints hint(s) and :explanations explanation(s), then re-applied localization.', [
                'class' => $this->formatSeederClassName($seedRun->class_name),
                'hints' => $deletedHints,
                'explanations' => $deletedExplanations,
            ]),
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

        $relatedLocalizationsRan = (int) ($result['related_localizations_ran'] ?? 0);
        if ($relatedLocalizationsRan > 0 && ! in_array($profile['type'], ['question_localizations', 'page_localizations'], true)) {
            $message .= ' ' . __('Повторно виконано :count пов’язаних сидер(ів) локалізації.', [
                'count' => $relatedLocalizationsRan,
            ]);
        }

        $errors = $result['errors'] ?? collect();

        return [
            'success' => true,
            'status' => $errors->isNotEmpty() ? 'partial' : 'success',
            'message' => $message,
            'deleted_questions' => $deletedQuestions,
            'deleted_blocks' => $deletedBlocks,
            'deleted_pages' => $deletedPages,
            'deleted_categories' => $deletedCategories,
            'deleted_hints' => $deletedHints,
            'deleted_explanations' => $deletedExplanations,
            'related_localizations_ran' => $relatedLocalizationsRan,
            'errors' => $errors->all(),
            'test_targets' => $this->resolveTestTargetsForClasses($result['ran'] ?? collect()),
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function refreshExecutedSeeders(Collection $selectedClasses, array $options = []): array
    {
        $selectedClasses = $selectedClasses
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();
        $resolvedOptions = $this->normalizeRefreshOptions($options);

        $emptyResult = [
            'success' => false,
            'message' => __('No seeders were selected.'),
            'selected_classes' => collect(),
            'classes_to_refresh' => collect(),
            'related_localization_classes' => collect(),
            'selected_ran' => collect(),
            'ran' => collect(),
            'ordered' => collect(),
            'errors' => collect(),
            'questions_deleted' => 0,
            'blocks_deleted' => 0,
            'pages_deleted' => 0,
            'categories_deleted' => 0,
            'hints_deleted' => 0,
            'explanations_deleted' => 0,
            'localizations_ran_total' => 0,
            'related_localizations_ran' => 0,
            'rolled_back' => false,
        ];

        if ($selectedClasses->isEmpty()) {
            return $emptyResult;
        }

        $executedSeeders = $this->buildExecutedSeedersForRefresh();
        $classesToRefresh = $this->expandRefreshClassNames($selectedClasses, $executedSeeders);
        $autoRefreshedLocalizationClasses = $this->autoRefreshedRelatedLocalizationClasses($selectedClasses, $executedSeeders);
        $validationErrors = $this->validateSeederClassesForRefresh($classesToRefresh);

        if ($validationErrors->isNotEmpty()) {
            return array_merge($emptyResult, [
                'message' => $validationErrors->implode(' '),
                'selected_classes' => $selectedClasses,
                'classes_to_refresh' => $classesToRefresh,
                'errors' => $validationErrors,
            ]);
        }

        $usesTransaction = (bool) (
            $resolvedOptions['atomic']
            || $resolvedOptions['dry_run']
            || $resolvedOptions['rollback_on_error']
        );
        $result = null;
        $rollbackTriggered = false;
        $rollbackMessage = null;

        try {
            if ($usesTransaction) {
                DB::transaction(function () use (
                    $selectedClasses,
                    $classesToRefresh,
                    $autoRefreshedLocalizationClasses,
                    $resolvedOptions,
                    &$result,
                    &$rollbackTriggered,
                    &$rollbackMessage
                ): void {
                    $result = $this->executeRefreshCycle(
                        $selectedClasses,
                        $classesToRefresh,
                        $autoRefreshedLocalizationClasses,
                        $resolvedOptions
                    );

                    if ($this->shouldRollbackRefreshCycle($selectedClasses, $result, $resolvedOptions)) {
                        $rollbackTriggered = true;
                        $rollbackMessage = $this->refreshRollbackMessage($result);

                        throw new \RuntimeException($rollbackMessage);
                    }

                    if ($resolvedOptions['dry_run']) {
                        throw new DryRunRollbackException('Rollback-only refresh dry run completed.');
                    }
                });

                return array_merge($result ?? $emptyResult, [
                    'rolled_back' => false,
                ]);
            }

            $result = $this->executeRefreshCycle(
                $selectedClasses,
                $classesToRefresh,
                $autoRefreshedLocalizationClasses,
                $resolvedOptions
            );
        } catch (\Throwable $exception) {
            if ($exception instanceof DryRunRollbackException) {
                return array_merge($result ?? $emptyResult, [
                    'rolled_back' => true,
                ]);
            }

            if ($rollbackTriggered && is_array($result)) {
                $result['success'] = false;
                $result['message'] = $rollbackMessage ?? ($result['message'] ?? $exception->getMessage());
                $result['rolled_back'] = true;

                return $result;
            }

            report($exception);

            return array_merge($emptyResult, [
                'message' => $exception->getMessage(),
                'selected_classes' => $selectedClasses,
                'classes_to_refresh' => $classesToRefresh,
                'errors' => collect([$exception->getMessage()]),
                'rolled_back' => $usesTransaction,
            ]);
        }

        return array_merge($result ?? $emptyResult, [
            'rolled_back' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function normalizeRefreshOptions(array $options): array
    {
        return [
            'atomic' => (bool) ($options['atomic'] ?? false),
            'dry_run' => (bool) ($options['dry_run'] ?? false),
            'rollback_on_error' => (bool) ($options['rollback_on_error'] ?? false),
            'touch_seed_runs' => ! array_key_exists('touch_seed_runs', $options)
                || (bool) $options['touch_seed_runs'],
        ];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    protected function executeRefreshCycle(
        Collection $selectedClasses,
        Collection $classesToRefresh,
        Collection $autoRefreshedLocalizationClasses,
        array $options,
    ): array {
        $deletionStats = $this->deleteSeederDataForRefresh($classesToRefresh);
        $rerunResult = $this->executeSeedersInOrder($classesToRefresh);
        $ran = collect($rerunResult['ran'] ?? collect())->values();
        $ordered = collect($rerunResult['ordered'] ?? collect())->values();
        $errors = collect($rerunResult['errors'] ?? collect())
            ->filter()
            ->unique()
            ->values();

        if ($options['touch_seed_runs']) {
            try {
                $this->touchSeedRunTimestamps(
                    $ran
                        ->merge($autoRefreshedLocalizationClasses)
                        ->unique()
                        ->values()
                );
            } catch (\Throwable $exception) {
                report($exception);
                $errors = $errors->push($exception->getMessage())->filter()->unique()->values();
            }
        }

        $relatedLocalizationClasses = $classesToRefresh
            ->diff($selectedClasses)
            ->merge($autoRefreshedLocalizationClasses)
            ->unique()
            ->values();
        $refreshedLocalizationClasses = $ran
            ->filter(fn (string $className) => in_array($this->virtualLocalizationType($className), ['question_localizations', 'page_localizations'], true))
            ->merge($autoRefreshedLocalizationClasses)
            ->unique()
            ->values();

        return array_merge($deletionStats, [
            'success' => $ran->isNotEmpty(),
            'message' => $ran->isNotEmpty()
                ? __('Seeder data refreshed.')
                : __('No seeders were refreshed.'),
            'selected_classes' => $selectedClasses,
            'classes_to_refresh' => $classesToRefresh,
            'related_localization_classes' => $relatedLocalizationClasses,
            'selected_ran' => $ran->intersect($selectedClasses)->values(),
            'ran' => $ran,
            'ordered' => $ordered,
            'errors' => $errors,
            'localizations_ran_total' => $refreshedLocalizationClasses->count(),
            'related_localizations_ran' => $refreshedLocalizationClasses
                ->filter(fn (string $className) => $relatedLocalizationClasses->contains($className))
                ->count(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $options
     */
    protected function shouldRollbackRefreshCycle(
        Collection $selectedClasses,
        array $result,
        array $options,
    ): bool {
        if (! $options['rollback_on_error']) {
            return false;
        }

        $errors = collect($result['errors'] ?? collect())
            ->filter()
            ->unique()
            ->values();
        $selectedRan = collect($result['selected_ran'] ?? collect())
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        return $errors->isNotEmpty()
            || $selectedRan->count() !== $selectedClasses->count();
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function refreshRollbackMessage(array $result): string
    {
        $errors = collect($result['errors'] ?? collect())
            ->filter()
            ->unique()
            ->values();

        if ($errors->isNotEmpty()) {
            return $this->formatDetailedErrorMessage($errors);
        }

        return (string) ($result['message'] ?? __('Seeder refresh failed and was rolled back.'));
    }

    protected function expandRefreshClassNames(Collection $classNames, ?Collection $executedSeeders = null): Collection
    {
        $normalized = $classNames
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return collect();
        }

        $executedSeeders ??= $this->buildExecutedSeedersForRefresh();

        if ($executedSeeders->isEmpty()) {
            return $normalized;
        }

        $relatedLocalizationMap = $this->buildRelatedLocalizationMap($executedSeeders);
        $autoRefreshedLocalizationClasses = $this->autoRefreshedRelatedLocalizationClasses($normalized, $executedSeeders);

        $relatedLocalizationClasses = $normalized
            ->reject(fn (string $className) => in_array($this->virtualLocalizationType($className), ['question_localizations', 'page_localizations'], true))
            ->flatMap(function (string $className) use ($relatedLocalizationMap) {
                return collect($relatedLocalizationMap->get($className, collect()))
                    ->pluck('class_name');
            })
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->reject(fn (string $className) => $autoRefreshedLocalizationClasses->contains($className))
            ->unique()
            ->values();

        return $normalized
            ->merge($relatedLocalizationClasses)
            ->unique()
            ->values();
    }

    protected function autoRefreshedRelatedLocalizationClasses(Collection $classNames, ?Collection $executedSeeders = null): Collection
    {
        $normalized = $classNames
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return collect();
        }

        $executedSeeders ??= $this->buildExecutedSeedersForRefresh();

        if ($executedSeeders->isEmpty()) {
            return collect();
        }

        $relatedLocalizationMap = $this->buildRelatedLocalizationMap($executedSeeders);

        return $normalized
            ->reject(fn (string $className) => in_array($this->virtualLocalizationType($className), ['question_localizations', 'page_localizations'], true))
            ->filter(fn (string $className) => $this->refreshesPageLocalizationsWithinBaseSeeder($className))
            ->flatMap(function (string $className) use ($relatedLocalizationMap) {
                return collect($relatedLocalizationMap->get($className, collect()))
                    ->filter(fn (array $localization) => ($localization['type'] ?? null) === 'page_localizations')
                    ->pluck('class_name');
            })
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();
    }

    protected function refreshesPageLocalizationsWithinBaseSeeder(string $className): bool
    {
        if (! $this->ensureSeederClassIsLoaded($className)) {
            return false;
        }

        return is_subclass_of($className, JsonPageSeeder::class);
    }

    protected function buildExecutedSeedersForRefresh(): Collection
    {
        if (! Schema::hasTable('seed_runs')) {
            return collect();
        }

        return DB::table('seed_runs')
            ->orderByDesc('ran_at')
            ->get()
            ->map(function ($seedRun) {
                $className = trim((string) ($seedRun->class_name ?? ''));

                $seedRun->ran_at = $seedRun->ran_at ? Carbon::parse($seedRun->ran_at) : null;
                $seedRun->display_class_name = $this->formatSeederClassName($className);
                $seedRun->display_class_basename = class_basename($className);
                $seedRun->data_profile = $this->describeSeederData($className);

                return $seedRun;
            });
    }

    protected function validateSeederClassesForRefresh(Collection $classNames): Collection
    {
        if ($classNames->isEmpty()) {
            return collect();
        }

        $executedClasses = $this->executedSeederClasses();

        return $classNames
            ->map(function (string $className) use ($executedClasses) {
                if (! $this->ensureSeederClassIsLoaded($className)) {
                    return __('Seeder :class was not found.', ['class' => $className]);
                }

                $blockedMessage = $this->executionBlockedMessageForSeeder($className, $executedClasses);

                if ($blockedMessage !== null) {
                    return $blockedMessage;
                }

                if ($this->isVirtualLocalizationSeeder($className)) {
                    return null;
                }

                $filePath = $this->resolveSeederFilePath($className);

                return $this->isInstantiableSeeder($className, $filePath)
                    ? null
                    : __('Seeder :class cannot be executed.', ['class' => $className]);
            })
            ->filter()
            ->unique()
            ->values();
    }

    protected function deleteSeederDataForRefresh(Collection $classNames): array
    {
        $typeMap = $classNames->mapWithKeys(function (string $className) {
            $profile = $this->describeSeederData($className);

            return [$className => $profile['type'] ?? 'unknown'];
        });

        $questionLocalizationClasses = $typeMap
            ->filter(fn ($type) => $type === 'question_localizations')
            ->keys()
            ->values();
        $pageLocalizationClasses = $typeMap
            ->filter(fn ($type) => $type === 'page_localizations')
            ->keys()
            ->values();
        $questionClasses = $typeMap
            ->filter(fn ($type) => $type === 'questions')
            ->keys()
            ->values();
        $pageClasses = $typeMap
            ->filter(fn ($type) => $type === 'pages')
            ->keys()
            ->values();
        $unknownClasses = $typeMap
            ->filter(fn ($type) => ! in_array($type, ['question_localizations', 'page_localizations', 'questions', 'pages'], true))
            ->keys()
            ->values();

        $deletedQuestions = 0;
        $deletedBlocks = 0;
        $deletedPages = 0;
        $deletedCategories = 0;
        $deletedHints = 0;
        $deletedExplanations = 0;

        DB::transaction(function () use (
            $questionLocalizationClasses,
            $pageLocalizationClasses,
            $questionClasses,
            $pageClasses,
            $unknownClasses,
            &$deletedQuestions,
            &$deletedBlocks,
            &$deletedPages,
            &$deletedCategories,
            &$deletedHints,
            &$deletedExplanations
        ) {
            foreach ($questionLocalizationClasses as $className) {
                $result = $this->removeVirtualLocalizationSeederData($className);
                $deletedHints += (int) ($result['deleted_hints'] ?? 0);
                $deletedExplanations += (int) ($result['deleted_explanations'] ?? 0);
            }

            foreach ($pageLocalizationClasses as $className) {
                $result = $this->removeVirtualLocalizationSeederData($className);
                $deletedBlocks += (int) ($result['deleted_blocks'] ?? 0);
            }

            if ($questionClasses->isNotEmpty()) {
                $deletedQuestions += $this->deleteQuestionsForSeeders($questionClasses);
            }

            if ($pageClasses->isNotEmpty()) {
                $pageResult = $this->deletePageContentForSeeders($pageClasses);
                $deletedBlocks += $pageResult['blocks'];
                $deletedPages += $pageResult['pages_deleted'];
                $deletedCategories += $pageResult['categories_deleted'];
            }

            if ($unknownClasses->isNotEmpty()) {
                $deletedQuestions += $this->deleteQuestionsForSeeders($unknownClasses);
                $pageResult = $this->deletePageContentForSeeders($unknownClasses);
                $deletedBlocks += $pageResult['blocks'];
                $deletedPages += $pageResult['pages_deleted'];
                $deletedCategories += $pageResult['categories_deleted'];
            }
        });

        return [
            'questions_deleted' => $deletedQuestions,
            'blocks_deleted' => $deletedBlocks,
            'pages_deleted' => $deletedPages,
            'categories_deleted' => $deletedCategories,
            'hints_deleted' => $deletedHints,
            'explanations_deleted' => $deletedExplanations,
        ];
    }

    protected function touchSeedRunTimestamps(Collection $classNames): void
    {
        if ($classNames->isEmpty() || ! Schema::hasTable('seed_runs')) {
            return;
        }

        $timestamp = now();

        foreach ($classNames as $className) {
            DB::table('seed_runs')->updateOrInsert(
                ['class_name' => $className],
                ['ran_at' => $timestamp]
            );
        }
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
            'pending_seeder' => tap((object) [
                'class_name' => $fullClassName,
                'display_class_name' => $displayName,
                'display_class_namespace' => $displayNamespace,
                'display_class_basename' => $baseName,
                'supports_preview' => false,
            ], function ($pendingSeeder) use ($fullClassName) {
                $availableLocalizations = $this->buildAvailableLocalizationMap([$fullClassName])->get($fullClassName, collect());
                $pendingSeeder->available_localizations = $availableLocalizations instanceof Collection
                    ? $availableLocalizations->all()
                    : [];
                $pendingSeeder->available_localizations_count = count($pendingSeeder->available_localizations);
            }),
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

    protected function formatDetailedErrorMessage(Collection $errors): string
    {
        $messages = $errors
            ->map(fn ($message) => trim((string) $message))
            ->filter()
            ->values();

        if ($messages->isEmpty()) {
            return '';
        }

        if ($messages->count() === 1) {
            return (string) $messages->first();
        }

        return $messages
            ->values()
            ->map(fn (string $message, int $index) => ($index + 1) . '. ' . $message)
            ->implode("\n");
    }

    protected function formatSeederExecutionError(string $className, \Throwable $exception): string
    {
        $message = trim($exception->getMessage());
        $displayName = $this->formatSeederClassName($className);

        if ($message === '') {
            return $displayName . ': ' . __('Невідома помилка під час виконання сидера.');
        }

        if (Str::startsWith($message, $displayName . ':') || Str::startsWith($message, $className . ':')) {
            return $message;
        }

        return $displayName . ': ' . $message;
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
                $runnableClassNames = $children->flatMap(function ($child) {
                    return collect($child['runnable_class_names'] ?? []);
                })->unique()->values();
                $blockedSeederCount = $children->sum(fn ($child) => (int) ($child['blocked_seeder_count'] ?? 0));

                return [
                    'type' => 'folder',
                    'name' => $folder['name'],
                    // Convert to array for consistent Livewire serialization
                    'children' => $children->all(),
                    'seeder_count' => $seederCount,
                    'class_names' => $classNames->all(),
                    'runnable_class_names' => $runnableClassNames->all(),
                    'blocked_seeder_count' => $blockedSeederCount,
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
                    'data_type' => $pendingSeeder->data_type ?? 'unknown',
                    'seeder_tab' => $pendingSeeder->seeder_tab ?? 'main',
                    'required_base_seeder' => $pendingSeeder->required_base_seeder ?? null,
                    'required_base_display_name' => $pendingSeeder->required_base_display_name ?? null,
                    'can_execute' => $pendingSeeder->can_execute ?? true,
                    'execution_block_reason' => $pendingSeeder->execution_block_reason ?? null,
                    'available_localizations' => $pendingSeeder->available_localizations ?? [],
                    'available_localizations_count' => $pendingSeeder->available_localizations_count ?? 0,
                ];
                $canExecute = (bool) ($pendingSeeder->can_execute ?? true);

                return [
                    'type' => 'seeder',
                    'name' => $seeder['name'],
                    'pending_seeder' => $pendingSeederArray,
                    'seeder_count' => 1,
                    'class_names' => [$className],
                    'runnable_class_names' => $canExecute ? [$className] : [],
                    'blocked_seeder_count' => $canExecute ? 0 : 1,
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
                    'display_class_namespace' => $seedRun->display_class_namespace ?? null,
                    'display_class_basename' => $seedRun->display_class_basename ?? $seedRun->display_class_name,
                    'ran_at_formatted' => $seedRun->ran_at_formatted,
                    'question_count' => $seedRun->question_count ?? 0,
                    'theory_target' => $seedRun->theory_target ?? null,
                    'test_target' => $seedRun->test_target ?? null,
                    'seeder_tab' => $seedRun->seeder_tab ?? 'main',
                    'related_localizations' => $seedRun->related_localizations ?? [],
                    'related_localizations_count' => $seedRun->related_localizations_count ?? 0,
                    'pending_localizations' => $seedRun->pending_localizations ?? [],
                    'pending_localizations_count' => $seedRun->pending_localizations_count ?? 0,
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

        if ($this->virtualLocalizationType($className) === 'question_localizations') {
            return [
                'type' => 'question_localizations',
                'delete_button' => __('Видалити локалізації'),
                'delete_confirm' => __('Видалити лог та пов\'язані локалізації?'),
                'folder_delete_button' => __('Видалити локалізації'),
                'folder_delete_confirm' => __('Видалити всі локалізаційні сидери в папці «:folder» разом із локалізаціями?'),
            ];
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
