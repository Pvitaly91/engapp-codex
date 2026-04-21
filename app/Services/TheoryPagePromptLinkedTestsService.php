<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Support\ComposeModeEligibility;
use App\Support\Database\JsonTestSeeder;
use App\Support\PromptGeneratorFilterNormalizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class TheoryPagePromptLinkedTestsService
{
    private const LEVEL_PAIRS = [
        ['A1', 'A2'],
        ['A2', 'B1'],
        ['B1', 'B2'],
        ['B2', 'C1'],
        ['C1', 'C2'],
    ];

    private const QUESTIONS_PER_TEST = 15;

    /**
     * @var array<string, array<string, mixed>|null>
     */
    private array $definitionCache = [];

    public function buildForPage(Page $page): Collection
    {
        $linkedTests = $this->findForPage($page);
        $directLinkedTests = $this->extractDirectLinkedTests($linkedTests);

        if ($directLinkedTests->isNotEmpty()) {
            return $directLinkedTests->values();
        }

        $definitionsBySeeder = $this->promptLinkedSeederDefinitionsForPage($page);
        $seederClasses = $this->aggregateSeederClassesForPage($linkedTests, $definitionsBySeeder);

        if ($seederClasses->isEmpty()) {
            return $directLinkedTests->values();
        }

        $baseFilters = $this->aggregateBaseFilters($page, $linkedTests, $definitionsBySeeder);
        $questionRows = Question::query()
            ->whereIn('seeder', $seederClasses->all())
            ->whereNotNull('level')
            ->get(['level', 'type']);

        $aggregatedTests = collect(self::LEVEL_PAIRS)
            ->map(function (array $levelPair) use ($page, $baseFilters, $seederClasses, $questionRows) {
                $availableRows = $questionRows->whereIn('level', $levelPair);
                $availableCount = $availableRows->count();

                if ($availableCount <= 0) {
                    return null;
                }

                $containsComposeQuestions = $availableRows
                    ->contains(fn ($row) => (string) ($row->type ?? '') === Question::TYPE_COMPOSE_TOKENS);
                $containsStandardQuestions = $availableRows
                    ->contains(fn ($row) => (string) ($row->type ?? '') !== Question::TYPE_COMPOSE_TOKENS);

                return $this->buildAggregatedVirtualTest(
                    $page,
                    $levelPair,
                    $seederClasses,
                    $baseFilters,
                    $availableCount,
                    $containsComposeQuestions && $containsStandardQuestions
                );
            })
            ->filter()
            ->values();

        return $aggregatedTests;
    }

    public function findForPage(Page $page): Collection
    {
        if ((int) $page->getKey() <= 0) {
            return collect();
        }

        return $this->deduplicateLinkedTests(
            $this->loadTestsForPage($page)
                ->map(fn (SavedGrammarTest $test) => $this->hydrateDisplayMetadata($test))
                ->values()
        );
    }

    protected function deduplicateLinkedTests(Collection $tests): Collection
    {
        $kept = collect();
        $seen = [];

        foreach ($tests as $test) {
            $key = $this->deduplicationKeyForTest($test);

            if ($key !== null && array_key_exists($key, $seen)) {
                continue;
            }

            if ($key !== null) {
                $seen[$key] = true;
            }

            $kept->push($test);
        }

        return $kept->values();
    }

    protected function deduplicationKeyForTest(SavedGrammarTest $test): ?string
    {
        $filters = is_array($test->filters) ? $test->filters : [];
        $seederClasses = $this->normalizeSeederClasses($filters['seeder_classes'] ?? []);

        if (count($seederClasses) === 1) {
            return 'seeder:' . $seederClasses[0];
        }

        $uuid = trim((string) ($test->uuid ?? ''));

        if ($uuid !== '') {
            return 'uuid:' . Str::lower($uuid);
        }

        $slug = trim((string) ($test->slug ?? ''));

        return $slug !== '' ? 'slug:' . Str::lower($slug) : null;
    }

    protected function extractDirectLinkedTests(Collection $linkedTests): Collection
    {
        return $linkedTests
            ->filter(fn (SavedGrammarTest $test) => $this->shouldDisplayDirectly($test))
            ->values();
    }

    protected function shouldDisplayDirectly(SavedGrammarTest $test): bool
    {
        $filters = is_array($test->filters) ? $test->filters : [];
        $lessonType = Str::lower(trim((string) ($filters['lesson_type'] ?? '')));
        $courseSlug = Str::lower(trim((string) ($filters['course_slug'] ?? '')));

        if ($lessonType === 'polyglot' || Str::startsWith($courseSlug, 'polyglot-')) {
            return ComposeModeEligibility::supportsFilters($filters);
        }

        return false;
    }

    /**
     * @param  mixed  $seederClasses
     * @return array<int, string>
     */
    protected function normalizeSeederClassValues(mixed $seederClasses): array
    {
        if (! is_array($seederClasses)) {
            return [];
        }

        return collect($seederClasses)
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique(fn (string $className) => Str::lower($className))
            ->values()
            ->all();
    }

    /**
     * @param  mixed  $seederClasses
     * @return array<int, string>
     */
    protected function normalizeSeederClasses(mixed $seederClasses): array
    {
        return collect($this->normalizeSeederClassValues($seederClasses))
            ->map(fn (string $className) => Str::lower($className))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function aggregateSeederClassesForPage(Collection $linkedTests, Collection $definitionsBySeeder): Collection
    {
        $linkedSeederClasses = $linkedTests
            ->flatMap(function (SavedGrammarTest $test) {
                $filters = is_array($test->filters) ? $test->filters : [];

                return $this->normalizeSeederClassValues($filters['seeder_classes'] ?? []);
            });

        $questionUuids = $linkedTests
            ->flatMap(fn (SavedGrammarTest $test) => $test->questionLinks->pluck('question_uuid'))
            ->filter(fn ($uuid) => is_string($uuid) && trim($uuid) !== '')
            ->unique()
            ->values();

        $questionSeederClasses = $questionUuids->isEmpty()
            ? collect()
            : Question::query()
                ->whereIn('uuid', $questionUuids->all())
                ->whereNotNull('seeder')
                ->pluck('seeder');

        return $linkedSeederClasses
            ->merge($questionSeederClasses)
            ->merge($definitionsBySeeder->keys())
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique(fn (string $className) => Str::lower($className))
            ->values();
    }

    protected function aggregateBaseFilters(Page $page, Collection $linkedTests, Collection $definitionsBySeeder): array
    {
        $referenceFilters = $this->referenceFiltersForAggregatedTests($linkedTests, $definitionsBySeeder);
        $promptGenerator = PromptGeneratorFilterNormalizer::normalize($referenceFilters['prompt_generator'] ?? null);

        if (! is_array($promptGenerator)) {
            $promptGenerator = [];
        }

        $promptGenerator['source_type'] = 'theory_page';
        $promptGenerator['theory_page_id'] = (int) $page->getKey();
        $promptGenerator['theory_page_ids'] = [(int) $page->getKey()];
        $promptGenerator['theory_page'] = array_filter([
            'id' => (int) $page->getKey(),
            'slug' => $page->slug,
            'title' => $page->title,
            'page_seeder_class' => $page->seeder,
            'category_slug_path' => $this->pageCategorySlugPath($page),
        ], fn ($value) => filled($value));

        return array_filter([
            'categories' => Arr::get($referenceFilters, 'categories', []),
            'difficulty_from' => Arr::get($referenceFilters, 'difficulty_from', 1),
            'difficulty_to' => Arr::get($referenceFilters, 'difficulty_to', 10),
            'manual_input' => (bool) Arr::get($referenceFilters, 'manual_input', false),
            'autocomplete_input' => (bool) Arr::get($referenceFilters, 'autocomplete_input', false),
            'check_one_input' => (bool) Arr::get($referenceFilters, 'check_one_input', false),
            'builder_input' => (bool) Arr::get($referenceFilters, 'builder_input', false),
            'include_ai' => (bool) Arr::get($referenceFilters, 'include_ai', false),
            'only_ai' => (bool) Arr::get($referenceFilters, 'only_ai', false),
            'include_ai_v2' => (bool) Arr::get($referenceFilters, 'include_ai_v2', false),
            'only_ai_v2' => (bool) Arr::get($referenceFilters, 'only_ai_v2', false),
            'question_types' => Arr::get($referenceFilters, 'question_types', []),
            'blank_count_from' => Arr::get($referenceFilters, 'blank_count_from'),
            'blank_count_to' => Arr::get($referenceFilters, 'blank_count_to'),
            'preferred_view' => Arr::get($referenceFilters, 'preferred_view'),
            'prompt_generator' => $promptGenerator,
        ], fn ($value) => $value !== null);
    }

    protected function referenceFiltersForAggregatedTests(Collection $linkedTests, Collection $definitionsBySeeder): array
    {
        $linkedTestFilters = $linkedTests
            ->map(fn (SavedGrammarTest $test) => is_array($test->filters) ? $test->filters : [])
            ->first(fn (array $filters) => $filters !== []);

        if (is_array($linkedTestFilters) && $linkedTestFilters !== []) {
            return $linkedTestFilters;
        }

        foreach ($definitionsBySeeder as $definition) {
            $filters = $this->definitionFilters(is_array($definition) ? $definition : []);

            if ($filters !== []) {
                return $filters;
            }
        }

        return [];
    }

    protected function buildAggregatedVirtualTest(
        Page $page,
        array $levelPair,
        Collection $seederClasses,
        array $baseFilters,
        int $availableCount,
        bool $isMixedPolyglotTest = false
    ): VirtualSavedTest {
        [$levelFrom, $levelTo] = $levelPair;

        $filters = $baseFilters;
        $filters['levels'] = [$levelFrom, $levelTo];
        $filters['seeder_classes'] = $seederClasses->values()->all();
        $filters['num_questions'] = min(self::QUESTIONS_PER_TEST, $availableCount);
        $filters['randomize_filtered'] = true;
        $filters['__meta'] = array_merge(
            is_array($filters['__meta'] ?? null) ? $filters['__meta'] : [],
            [
                'mode' => 'filters',
                'aggregated_theory_page_test' => true,
                'theory_page_id' => (int) $page->getKey(),
                'theory_page_mixed_polyglot_test' => $isMixedPolyglotTest,
            ]
        );

        return (new VirtualSavedTest(
            sprintf('%s (%s-%s)', $page->title, $levelFrom, $levelTo),
            sprintf('theory-page-%d-%s-%s', (int) $page->getKey(), strtolower($levelFrom), strtolower($levelTo)),
            $filters
        ))->setTotalQuestionsAvailable($availableCount);
    }

    protected function buildFallbackTestsForPage(Page $page, Collection $linkedTests): Collection
    {
        $linkedSeederClasses = $linkedTests
            ->flatMap(fn (SavedGrammarTest $test) => $this->normalizeSeederClasses(
                is_array($test->filters) ? ($test->filters['seeder_classes'] ?? []) : []
            ))
            ->unique()
            ->values();

        $definitionsBySeeder = $this->promptLinkedSeederDefinitionsForPage($page);

        if ($definitionsBySeeder->isEmpty()) {
            return collect();
        }

        $missingSeederClasses = $definitionsBySeeder
            ->keys()
            ->reject(fn (string $className) => $linkedSeederClasses->contains(Str::lower($className)))
            ->values();

        if ($missingSeederClasses->isEmpty()) {
            return collect();
        }

        $questionRows = Question::query()
            ->whereIn('seeder', $missingSeederClasses->all())
            ->whereNotNull('seeder')
            ->get(['seeder', 'level'])
            ->groupBy('seeder');

        return $missingSeederClasses
            ->map(function (string $className) use ($definitionsBySeeder, $questionRows) {
                $rows = collect($questionRows->get($className, collect()));
                $availableCount = $rows->count();

                if ($availableCount <= 0) {
                    return null;
                }

                return $this->buildFallbackVirtualTest(
                    $className,
                    $definitionsBySeeder->get($className, []),
                    $availableCount,
                    $rows->pluck('level')->filter()->map(fn ($level) => (string) $level)->all()
                );
            })
            ->filter()
            ->values();
    }

    protected function promptLinkedSeederDefinitionsForPage(Page $page): Collection
    {
        if (! Schema::hasTable('questions') || ! Schema::hasColumn('questions', 'seeder')) {
            return collect();
        }

        $seederClasses = Question::query()
            ->whereNotNull('seeder')
            ->distinct()
            ->orderBy('seeder')
            ->pluck('seeder');

        return $seederClasses->mapWithKeys(function (string $className) use ($page) {
            $definition = $this->loadSeederDefinition($className);

            if (! is_array($definition)) {
                return [];
            }

            $filters = $this->definitionFilters($definition);

            if ($filters === [] || ! $this->filtersReferencePage($filters, $page)) {
                return [];
            }

            return [$className => $definition];
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function loadSeederDefinition(string $className): ?array
    {
        if (array_key_exists($className, $this->definitionCache)) {
            return $this->definitionCache[$className];
        }

        if (! class_exists($className) || ! is_subclass_of($className, JsonTestSeeder::class)) {
            return $this->definitionCache[$className] = null;
        }

        try {
            $instance = app()->make($className);

            if (! method_exists($instance, 'resolvedDefinitionPath')) {
                return $this->definitionCache[$className] = null;
            }

            $definitionPath = $instance->resolvedDefinitionPath();

            if (! is_string($definitionPath) || $definitionPath === '' || ! is_file($definitionPath)) {
                return $this->definitionCache[$className] = null;
            }

            $decoded = json_decode((string) file_get_contents($definitionPath), true);

            return $this->definitionCache[$className] = is_array($decoded) ? $decoded : null;
        } catch (Throwable) {
            return $this->definitionCache[$className] = null;
        }
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    protected function definitionFilters(array $definition): array
    {
        $filters = Arr::get($definition, 'saved_test.filters', Arr::get($definition, 'filters', []));

        if (! is_array($filters)) {
            return [];
        }

        $normalizedPromptGenerator = PromptGeneratorFilterNormalizer::normalize(
            $filters['prompt_generator'] ?? null
        );

        if ($normalizedPromptGenerator !== null || array_key_exists('prompt_generator', $filters)) {
            $filters['prompt_generator'] = $normalizedPromptGenerator;
        }

        return $filters;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<int, string>  $availableLevels
     */
    protected function buildFallbackVirtualTest(
        string $className,
        array $definition,
        int $availableCount,
        array $availableLevels,
    ): ?VirtualSavedTest {
        $savedTest = Arr::get($definition, 'saved_test', []);
        $filters = $this->definitionFilters($definition);

        if (! is_array($savedTest)) {
            $savedTest = [];
        }

        $name = trim((string) ($savedTest['name'] ?? ''));
        $slug = trim((string) ($savedTest['slug'] ?? ''));

        if ($name === '') {
            $baseName = preg_replace('/Seeder$/', '', class_basename($className)) ?: class_basename($className);
            $name = Str::headline($baseName);
        }

        if ($slug === '') {
            $slug = 'virtual-seeder-' . Str::slug(class_basename($className)) . '-' . substr(md5($className), 0, 8);
        }

        $configuredSeederClasses = collect($filters['seeder_classes'] ?? [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if (! $configuredSeederClasses->contains($className)) {
            $configuredSeederClasses->push($className);
        }

        $filters['seeder_classes'] = $configuredSeederClasses->all();
        $filters['levels'] = $this->normalizeLevels($filters['levels'] ?? $availableLevels);
        $filters['num_questions'] = min(
            max((int) ($filters['num_questions'] ?? $availableCount), 1),
            $availableCount
        );
        $filters['randomize_filtered'] = (bool) ($filters['randomize_filtered'] ?? false);
        $filters['__meta'] = array_merge(
            is_array($filters['__meta'] ?? null) ? $filters['__meta'] : [],
            ['mode' => 'filters']
        );

        return (new VirtualSavedTest(
            $name,
            $slug,
            $filters,
            $this->nullableString($savedTest['description'] ?? null)
        ))->setTotalQuestionsAvailable($availableCount);
    }

    /**
     * @param  mixed  $levels
     * @return array<int, string>
     */
    protected function normalizeLevels(mixed $levels): array
    {
        $order = array_flip(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']);

        if (! is_array($levels)) {
            $levels = is_string($levels) && trim($levels) !== '' ? [$levels] : [];
        }

        return collect($levels)
            ->map(fn ($level) => trim((string) $level))
            ->filter()
            ->unique()
            ->sortBy(fn (string $level) => $order[$level] ?? 999)
            ->values()
            ->all();
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function sortPriority(mixed $test): int
    {
        return method_exists($test, 'isVirtual') && $test->isVirtual() ? 1 : 0;
    }

    protected function loadTestsForPage(Page $page): Collection
    {
        $pageId = (int) $page->getKey();
        $pageSlug = $this->normalizedPageSlug($page);
        $categorySlugPath = $this->pageCategorySlugPath($page);
        $pageSeederClass = $this->normalizedPageSeederClass($page);

        try {
            $tests = SavedGrammarTest::query()
                ->with('questionLinks')
                ->withCount('questionLinks')
                ->where(function (Builder $query) use ($pageId, $pageSlug, $categorySlugPath, $pageSeederClass): void {
                    $query->where('filters->prompt_generator->theory_page_id', $pageId)
                        ->orWhere('filters->prompt_generator->theory_page->id', $pageId)
                        ->orWhereJsonContains('filters->prompt_generator->theory_page_ids', $pageId);

                    if ($pageSeederClass !== '') {
                        $query->orWhere('filters->prompt_generator->theory_page->page_seeder_class', $pageSeederClass);
                    }

                    if ($pageSlug !== '') {
                        $query->orWhere(function (Builder $slugQuery) use ($pageSlug, $categorySlugPath): void {
                            $slugQuery->where('filters->prompt_generator->theory_page->slug', $pageSlug);

                            if ($categorySlugPath !== '') {
                                $slugQuery->where(
                                    'filters->prompt_generator->theory_page->category_slug_path',
                                    $categorySlugPath
                                );
                            }
                        });
                    }
                })
                ->orderByDesc('updated_at')
                ->orderBy('name')
                ->get();

            if ($tests->isNotEmpty()) {
                return $tests;
            }
        } catch (Throwable) {
        }

        return SavedGrammarTest::query()
            ->with('questionLinks')
            ->withCount('questionLinks')
            ->get()
            ->filter(fn (SavedGrammarTest $test) => $this->filtersReferencePage($test->filters ?? [], $page))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    protected function filtersReferencePage(array $filters, Page $page): bool
    {
        $promptGenerator = PromptGeneratorFilterNormalizer::normalize($filters['prompt_generator'] ?? null);

        if (! is_array($promptGenerator)) {
            return false;
        }

        if ($this->matchesPageSeederClass($promptGenerator, $page)) {
            return true;
        }

        $pageId = (int) $page->getKey();
        $singleId = (int) ($promptGenerator['theory_page_id'] ?? ($promptGenerator['theory_page']['id'] ?? 0));

        if ($singleId === $pageId) {
            return true;
        }

        $pageIds = $promptGenerator['theory_page_ids'] ?? [];

        if (is_array($pageIds) && collect($pageIds)->map(fn ($value) => (int) $value)->contains($pageId)) {
            return true;
        }

        return $this->matchesPageSlugPath($promptGenerator, $page);
    }

    /**
     * @param  array<string, mixed>  $promptGenerator
     */
    protected function matchesPageSeederClass(array $promptGenerator, Page $page): bool
    {
        $expectedSeederClass = $this->normalizedPageSeederClass($page);
        $configuredSeederClass = trim((string) Arr::get($promptGenerator, 'theory_page.page_seeder_class', ''));

        if ($expectedSeederClass === '' || $configuredSeederClass === '' || $configuredSeederClass !== $expectedSeederClass) {
            return false;
        }

        $configuredSlug = $this->normalizedPromptGeneratorPageSlug($promptGenerator);

        if ($configuredSlug !== '' && $configuredSlug !== $this->normalizedPageSlug($page)) {
            return false;
        }

        $configuredCategorySlugPath = $this->normalizedPromptGeneratorCategorySlugPath($promptGenerator);
        $expectedCategorySlugPath = $this->pageCategorySlugPath($page);

        if (
            $configuredCategorySlugPath !== ''
            && $expectedCategorySlugPath !== ''
            && $configuredCategorySlugPath !== $expectedCategorySlugPath
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $promptGenerator
     */
    protected function matchesPageSlugPath(array $promptGenerator, Page $page): bool
    {
        $configuredSlug = $this->normalizedPromptGeneratorPageSlug($promptGenerator);
        $expectedSlug = $this->normalizedPageSlug($page);

        if ($configuredSlug === '' || $expectedSlug === '' || $configuredSlug !== $expectedSlug) {
            return false;
        }

        $configuredCategorySlugPath = $this->normalizedPromptGeneratorCategorySlugPath($promptGenerator);
        $expectedCategorySlugPath = $this->pageCategorySlugPath($page);

        if ($configuredCategorySlugPath === '' || $expectedCategorySlugPath === '') {
            return true;
        }

        return $configuredCategorySlugPath === $expectedCategorySlugPath;
    }

    protected function normalizedPageSeederClass(Page $page): string
    {
        return trim((string) ($page->seeder ?? ''));
    }

    protected function normalizedPageSlug(Page $page): string
    {
        return Str::lower(trim((string) ($page->slug ?? '')));
    }

    protected function pageCategorySlugPath(Page $page): string
    {
        $page->loadMissing('category');

        $category = $page->category;

        if (! $category) {
            return '';
        }

        $segments = [];
        $current = $category;
        $depth = 0;

        while ($current && $depth < 10) {
            $slug = trim((string) ($current->slug ?? ''));

            if ($slug !== '') {
                array_unshift($segments, $slug);
            }

            $current->loadMissing('parent');
            $current = $current->parent;
            $depth++;
        }

        return $this->normalizeCategorySlugPath(implode('/', $segments));
    }

    /**
     * @param  array<string, mixed>  $promptGenerator
     */
    protected function normalizedPromptGeneratorPageSlug(array $promptGenerator): string
    {
        return Str::lower(trim((string) Arr::get($promptGenerator, 'theory_page.slug', '')));
    }

    /**
     * @param  array<string, mixed>  $promptGenerator
     */
    protected function normalizedPromptGeneratorCategorySlugPath(array $promptGenerator): string
    {
        return $this->normalizeCategorySlugPath((string) Arr::get($promptGenerator, 'theory_page.category_slug_path', ''));
    }

    protected function normalizeCategorySlugPath(string $categorySlugPath): string
    {
        $segments = array_values(array_filter(
            explode('/', str_replace('\\', '/', Str::lower(trim($categorySlugPath)))),
            'strlen'
        ));

        return implode('/', $segments);
    }

    protected function hydrateDisplayMetadata(SavedGrammarTest $test): SavedGrammarTest
    {
        $filters = is_array($test->filters) ? $test->filters : [];
        $actualQuestionCount = (int) ($test->question_links_count ?? 0);

        if (! isset($filters['num_questions']) && $actualQuestionCount > 0) {
            $filters['num_questions'] = $actualQuestionCount;
            $test->filters = $filters;
        }

        $test->setAttribute(
            'total_questions_available',
            $actualQuestionCount > 0 ? $actualQuestionCount : (int) ($filters['num_questions'] ?? 0)
        );

        return $test;
    }
}
