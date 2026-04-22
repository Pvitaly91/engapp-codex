<?php

namespace App\Services\V3PromptGenerator;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\SavedGrammarTestQuestion;
use App\Modules\SeedRunsV2\Services\SeedRunsService;
use App\Support\Database\JsonRuntimeSeeder;
use App\Support\Database\JsonTestDefinitionIndex;
use App\Support\PackageSeed\AbstractJsonPackageUnseedService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class V3PackageUnseedService extends AbstractJsonPackageUnseedService
{
    public function __construct(
        private readonly SeedRunsService $seedRunsService,
        private readonly JsonTestDefinitionIndex $definitionIndex,
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

            $context = $this->buildContext($definition, $target, $resolvedSeederClass);
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
                    $context['canonical_saved_test'],
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
        return 'database/seeders/V3';
    }

    protected function reportDirectory(): string
    {
        return 'package-unseed-reports/v3';
    }

    /**
     * @return array<int, string>
     */
    protected function expectedLocales(): array
    {
        return ['uk', 'en', 'pl'];
    }

    /**
     * @param  array<string, mixed>  $target
     */
    private function expectedSeederClass(array $target): string
    {
        $relative = Str::after(
            str_replace('\\', '/', (string) $target['package_root_relative_path']),
            'database/seeders/V3/'
        );
        $segments = array_values(array_filter(explode('/', $relative)));
        $className = (string) array_pop($segments);
        $namespace = implode('\\', $segments);

        return 'Database\\Seeders\\V3\\' . ($namespace !== '' ? $namespace . '\\' : '') . $className;
    }

    /**
     * @return array<string, mixed>
     */
    private function readDefinition(string $definitionAbsolutePath): array
    {
        return (new JsonRuntimeSeeder($definitionAbsolutePath))->readDefinition();
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    private function definitionSummary(
        array $definition,
        array $target,
        string $resolvedSeederClass,
    ): array {
        $questions = array_values(array_filter((array) ($definition['questions'] ?? []), 'is_array'));
        $levels = array_values(array_unique(array_filter(array_map(
            static fn (array $question): string => trim((string) ($question['level'] ?? '')),
            $questions
        ))));

        natcasesort($levels);

        return [
            'question_count' => count($questions),
            'saved_test_slug' => $this->nullableString(data_get($definition, 'saved_test.slug')),
            'saved_test_uuid' => $this->nullableString(data_get($definition, 'saved_test.uuid')),
            'levels' => array_values($levels),
            'default_locale' => $this->nullableString(data_get($definition, 'defaults.default_locale')),
            'category_name' => $this->nullableString(data_get($definition, 'category.name')),
            'resolved_seeder_class' => $resolvedSeederClass,
            'definition_relative_path' => (string) ($target['definition_relative_path'] ?? ''),
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array{
     *   ownership: array<string, bool>,
     *   warnings: list<string>,
     *   impact_counts: array<string, int>,
     *   guard_error: array<string, mixed>|null,
     *   canonical_saved_test: SavedGrammarTest|null
     * }
     */
    private function buildContext(array $definition, array $target, string $resolvedSeederClass): array
    {
        $questionUuids = array_keys($this->definitionIndex->indexQuestions(
            $definition,
            (string) $target['definition_absolute_path'],
            $resolvedSeederClass
        )['items'] ?? []);
        $questionCount = Schema::hasTable('questions') && Schema::hasColumn('questions', 'seeder')
            ? Question::query()->where('seeder', $resolvedSeederClass)->count()
            : 0;
        $savedTestContext = $this->buildSavedTestContext($definition, $resolvedSeederClass, $questionUuids);
        $seedRunPresent = $this->seedRunPresent($resolvedSeederClass);
        $packagePresent = $questionCount > 0 || $savedTestContext['present'];
        $warnings = [];

        if (! $seedRunPresent) {
            $warnings[] = 'Canonical seed_runs record is missing for the resolved seeder class.';
        }

        if (! $packagePresent) {
            $warnings[] = 'Package-owned database content is already absent.';
        }

        if ($savedTestContext['warning'] !== null) {
            $warnings[] = $savedTestContext['warning'];
        }

        return [
            'ownership' => [
                'seed_run_present' => $seedRunPresent,
                'package_present_in_db' => $packagePresent,
            ],
            'warnings' => array_values(array_unique(array_filter($warnings))),
            'impact_counts' => $this->filterZeroCounts([
                'Question' => $questionCount,
                'SavedGrammarTest' => $savedTestContext['present'] ? 1 : 0,
                'SavedGrammarTestQuestion' => $savedTestContext['question_links_count'],
            ]),
            'guard_error' => $savedTestContext['guard_error'],
            'canonical_saved_test' => $savedTestContext['saved_test'],
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  list<string>  $packageQuestionUuids
     * @return array{
     *   present: bool,
     *   question_links_count: int,
     *   warning: string|null,
     *   guard_error: array<string, mixed>|null,
     *   saved_test: SavedGrammarTest|null
     * }
     */
    private function buildSavedTestContext(
        array $definition,
        string $resolvedSeederClass,
        array $packageQuestionUuids,
    ): array {
        $savedTestPayload = is_array($definition['saved_test'] ?? null) ? $definition['saved_test'] : [];
        $savedTestUuid = trim((string) ($savedTestPayload['uuid'] ?? ''));
        $savedTestSlug = trim((string) ($savedTestPayload['slug'] ?? ''));

        if (
            $savedTestPayload === []
            || $savedTestUuid === ''
            || $savedTestSlug === ''
            || ! Schema::hasTable('saved_grammar_tests')
            || ! Schema::hasTable('saved_grammar_test_questions')
        ) {
            $unresolvedReferences = $this->foreignSavedTestsForQuestionUuids($packageQuestionUuids, 0);

            return [
                'present' => false,
                'question_links_count' => 0,
                'warning' => $savedTestPayload === []
                    ? null
                    : 'Canonical saved grammar test metadata is incomplete or unavailable.',
                'guard_error' => $unresolvedReferences->isNotEmpty()
                    ? [
                        'stage' => 'dependency_guard',
                        'reason' => 'unresolved_saved_test_ownership',
                        'message' => 'Saved grammar tests still reference question UUIDs from the resolved package, but canonical saved_test ownership cannot be resolved from definition.json.',
                        'foreign_saved_tests' => $unresolvedReferences->take(6)->values()->all(),
                    ]
                    : null,
                'saved_test' => null,
            ];
        }

        $savedTest = SavedGrammarTest::query()
            ->with('questionLinks')
            ->where('uuid', $savedTestUuid)
            ->first();

        if (! $savedTest && $savedTestSlug !== '') {
            $savedTest = SavedGrammarTest::query()
                ->with('questionLinks')
                ->where('slug', $savedTestSlug)
                ->first();
        }

        if (! $savedTest) {
            return [
                'present' => false,
                'question_links_count' => 0,
                'warning' => 'Canonical saved grammar test record is already absent.',
                'guard_error' => null,
                'saved_test' => null,
            ];
        }

        $questionLinks = $savedTest->questionLinks
            ->pluck('question_uuid')
            ->filter(fn ($uuid) => is_string($uuid) && trim($uuid) !== '')
            ->map(fn ($uuid) => trim((string) $uuid))
            ->unique()
            ->values();
        $extraQuestionUuids = $questionLinks
            ->reject(fn (string $uuid) => in_array($uuid, $packageQuestionUuids, true))
            ->values();
        $normalizedSeederClasses = $this->normalizeSeederClasses($savedTest->filters['seeder_classes'] ?? []);
        $foreignSeederClasses = collect($normalizedSeederClasses)
            ->reject(fn (string $className) => $className === $resolvedSeederClass)
            ->values();
        $foreignSavedTests = $this->foreignSavedTestsForQuestionUuids(
            $packageQuestionUuids,
            (int) $savedTest->getKey()
        );

        if ($extraQuestionUuids->isNotEmpty()) {
            return [
                'present' => true,
                'question_links_count' => $questionLinks->count(),
                'warning' => null,
                'guard_error' => [
                    'stage' => 'dependency_guard',
                    'reason' => 'canonical_saved_test_expanded',
                    'message' => 'Canonical saved grammar test references question UUIDs outside the resolved package.',
                    'extra_question_uuids' => $extraQuestionUuids->take(6)->all(),
                ],
                'saved_test' => $savedTest,
            ];
        }

        if ($foreignSeederClasses->isNotEmpty()) {
            return [
                'present' => true,
                'question_links_count' => $questionLinks->count(),
                'warning' => null,
                'guard_error' => [
                    'stage' => 'dependency_guard',
                    'reason' => 'canonical_saved_test_shared',
                    'message' => 'Canonical saved grammar test also targets other seeder classes.',
                    'foreign_seeder_classes' => $foreignSeederClasses->take(6)->all(),
                ],
                'saved_test' => $savedTest,
            ];
        }

        if ($foreignSavedTests->isNotEmpty()) {
            return [
                'present' => true,
                'question_links_count' => $questionLinks->count(),
                'warning' => null,
                'guard_error' => [
                    'stage' => 'dependency_guard',
                    'reason' => 'external_saved_tests_reference_package',
                    'message' => 'Other saved grammar tests still reference question UUIDs from the resolved package.',
                    'foreign_saved_tests' => $foreignSavedTests->take(6)->values()->all(),
                ],
                'saved_test' => $savedTest,
            ];
        }

        return [
            'present' => true,
            'question_links_count' => $questionLinks->count(),
            'warning' => null,
            'guard_error' => null,
            'saved_test' => $savedTest,
        ];
    }

    /**
     * @param  list<string>  $questionUuids
     * @return Collection<int, array<string, mixed>>
     */
    private function foreignSavedTestsForQuestionUuids(array $questionUuids, int $canonicalSavedTestId): Collection
    {
        if ($questionUuids === [] || ! Schema::hasTable('saved_grammar_test_questions')) {
            return collect();
        }

        $foreignIds = SavedGrammarTestQuestion::query()
            ->whereIn('question_uuid', $questionUuids)
            ->where('saved_grammar_test_id', '!=', $canonicalSavedTestId)
            ->distinct()
            ->pluck('saved_grammar_test_id');

        if ($foreignIds->isEmpty() || ! Schema::hasTable('saved_grammar_tests')) {
            return collect();
        }

        return SavedGrammarTest::query()
            ->whereIn('id', $foreignIds->all())
            ->orderBy('slug')
            ->get(['id', 'uuid', 'slug', 'name'])
            ->map(fn (SavedGrammarTest $savedTest): array => [
                'id' => (int) $savedTest->getKey(),
                'uuid' => (string) ($savedTest->uuid ?? ''),
                'slug' => (string) ($savedTest->slug ?? ''),
                'name' => (string) ($savedTest->name ?? ''),
            ]);
    }

    /**
     * @param  mixed  $seederClasses
     * @return list<string>
     */
    private function normalizeSeederClasses(mixed $seederClasses): array
    {
        if (! is_array($seederClasses)) {
            return [];
        }

        return collect($seederClasses)
            ->map(fn ($className) => trim((string) $className))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function executeUnseed(
        string $resolvedSeederClass,
        ?SavedGrammarTest $canonicalSavedTest,
        bool $dryRun,
    ): array {
        $deletionStats = $this->seedRunsService->deleteSeedDataForClasses([$resolvedSeederClass]);
        $savedTestDeletionStats = $this->deleteCanonicalSavedTest($canonicalSavedTest);
        $seedRunRemoved = false;
        $counts = $this->filterZeroCounts([
            'Question' => (int) ($deletionStats['questions_deleted'] ?? 0),
            'SavedGrammarTest' => (int) ($savedTestDeletionStats['SavedGrammarTest'] ?? 0),
            'SavedGrammarTestQuestion' => (int) ($savedTestDeletionStats['SavedGrammarTestQuestion'] ?? 0),
        ]);

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
     * @return array<string, int>
     */
    private function deleteCanonicalSavedTest(?SavedGrammarTest $savedTest): array
    {
        if (! $savedTest || ! Schema::hasTable('saved_grammar_tests') || ! Schema::hasTable('saved_grammar_test_questions')) {
            return [
                'SavedGrammarTest' => 0,
                'SavedGrammarTestQuestion' => 0,
            ];
        }

        $questionLinksDeleted = $savedTest->questionLinks()->count();
        $savedTest->questionLinks()->delete();
        $savedTest->delete();

        return [
            'SavedGrammarTest' => 1,
            'SavedGrammarTestQuestion' => $questionLinksDeleted,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $resolved = trim((string) $value);

        return $resolved !== '' ? $resolved : null;
    }
}
