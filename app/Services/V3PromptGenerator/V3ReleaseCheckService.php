<?php

namespace App\Services\V3PromptGenerator;

use App\Support\Database\JsonTestDefinitionIndex;
use App\Support\PromptGeneratorFilterNormalizer;
use App\Support\ReleaseCheck\AbstractJsonPackageReleaseCheckService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class V3ReleaseCheckService extends AbstractJsonPackageReleaseCheckService
{
    public function __construct(
        private readonly JsonTestDefinitionIndex $definitionIndex,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function run(string $targetInput, string $profile = 'release'): array
    {
        $resolvedProfile = $this->normalizeProfile($profile);
        $target = $this->resolvePackageTarget($targetInput);
        $checks = [];
        $expectedSeederClass = $this->expectedSeederClass((string) $target['package_root_relative_path']);
        $className = basename((string) $target['package_root_absolute_path']);

        $checks[] = $this->requiredFilesCheck($target);
        $checks[] = $this->loaderCheck((string) $target['loader_absolute_path'], $className);
        $checks[] = $this->realSeederCheck((string) $target['real_seeder_absolute_path'], $expectedSeederClass, $className);

        $definitionPayload = null;
        $questionIndex = null;
        $questionUuids = [];
        $definitionLoad = $this->loadJsonFile((string) $target['definition_absolute_path'], 'V3 definition');

        if ($definitionLoad['error'] !== null) {
            $checks[] = $this->makeCheck(
                self::STATUS_FAIL,
                'v3.definition.json',
                'V3 definition JSON is readable',
                ['error' => $definitionLoad['error']]
            );
        } else {
            /** @var array<string, mixed> $definitionPayload */
            $definitionPayload = $definitionLoad['data'];

            $checks[] = $this->makeCheck(
                self::STATUS_PASS,
                'v3.definition.json',
                'V3 definition JSON is readable',
                ['path' => $target['definition_relative_path']]
            );

            $checks = array_merge(
                $checks,
                $this->definitionChecks($definitionPayload, $target, $expectedSeederClass, $resolvedProfile)
            );

            if (is_array($definitionPayload['questions'] ?? null)) {
                [$questionIndex, $questionUuids, $questionStructureCheck] = $this->questionStructureCheck(
                    $definitionPayload,
                    (string) $target['definition_absolute_path'],
                    $expectedSeederClass
                );
                $checks[] = $questionStructureCheck;
                $checks[] = $this->savedTestCheck($definitionPayload, $questionUuids, $expectedSeederClass);
            }
        }

        $checks = array_merge(
            $checks,
            $this->localizationChecks(
                $target,
                $expectedSeederClass,
                $resolvedProfile,
                $questionIndex,
                $questionUuids
            )
        );

        return [
            'target' => $target,
            'profile' => $resolvedProfile,
            'checks' => $checks,
            'summary' => $this->summarizeChecks($checks),
            'artifacts' => [
                'report_path' => null,
            ],
        ];
    }

    protected function packageRootRelativePath(): string
    {
        return 'database/seeders/V3';
    }

    protected function reportDirectory(): string
    {
        return 'release-checks/v3';
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
     * @return array<string, mixed>
     */
    private function requiredFilesCheck(array $target): array
    {
        $expectedPaths = array_merge([
            (string) $target['loader_absolute_path'],
            (string) $target['real_seeder_absolute_path'],
            (string) $target['definition_absolute_path'],
        ], array_values((array) $target['localizations']));

        $missing = [];

        foreach ($expectedPaths as $absolutePath) {
            if (! File::exists($absolutePath)) {
                $missing[] = $this->relativePath($absolutePath);
            }
        }

        return $this->makeCheck(
            $missing === [] ? self::STATUS_PASS : self::STATUS_FAIL,
            'v3.package.files',
            'Required V3 package files exist',
            $missing === []
                ? ['package_root' => $target['package_root_relative_path']]
                : ['missing' => $missing]
        );
    }

    private function loaderCheck(string $loaderAbsolutePath, string $className): array
    {
        if (! File::exists($loaderAbsolutePath)) {
            return $this->makeCheck(
                self::STATUS_FAIL,
                'v3.loader.stub',
                'Top-level V3 loader stub matches the package contract',
                ['missing' => $this->relativePath($loaderAbsolutePath)]
            );
        }

        $contents = File::get($loaderAbsolutePath);
        $expectedRequire = "require_once __DIR__ . '/{$className}/{$className}.php';";
        $matches = str_contains($contents, $expectedRequire);

        return $this->makeCheck(
            $matches ? self::STATUS_PASS : self::STATUS_FAIL,
            'v3.loader.stub',
            'Top-level V3 loader stub matches the package contract',
            $matches
                ? ['path' => $this->relativePath($loaderAbsolutePath)]
                : ['expected' => $expectedRequire]
        );
    }

    private function realSeederCheck(string $realSeederAbsolutePath, string $expectedSeederClass, string $className): array
    {
        if (! File::exists($realSeederAbsolutePath)) {
            return $this->makeCheck(
                self::STATUS_FAIL,
                'v3.real_seeder.runtime',
                'Package-local V3 seeder extends JsonTestSeeder',
                ['missing' => $this->relativePath($realSeederAbsolutePath)]
            );
        }

        $contents = File::get($realSeederAbsolutePath);
        preg_match('/namespace\s+([^;]+);/', $contents, $namespaceMatches);
        preg_match('/class\s+([A-Za-z0-9_]+)\s+extends\s+JsonTestSeeder/', $contents, $classMatches);
        $actualSeederClass = trim(((string) ($namespaceMatches[1] ?? '')) . '\\' . ((string) ($classMatches[1] ?? '')), '\\');
        $matchesContract = str_contains($contents, 'extends JsonTestSeeder')
            && str_contains($contents, "return __DIR__ . '/definition.json';")
            && $actualSeederClass === $expectedSeederClass
            && (($classMatches[1] ?? null) === $className);

        return $this->makeCheck(
            $matchesContract ? self::STATUS_PASS : self::STATUS_FAIL,
            'v3.real_seeder.runtime',
            'Package-local V3 seeder extends JsonTestSeeder',
            $matchesContract
                ? ['class' => $expectedSeederClass]
                : [
                    'expected' => $expectedSeederClass,
                    'actual' => $actualSeederClass !== '' ? $actualSeederClass : null,
                ]
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<string, mixed>  $target
     * @return array<int, array<string, mixed>>
     */
    private function definitionChecks(
        array $definition,
        array $target,
        string $expectedSeederClass,
        string $profile,
    ): array {
        $checks = [];
        $resolvedSeederClass = $this->definitionIndex->resolveSeederClassName($definition, $expectedSeederClass);
        $configuredUuidNamespace = trim((string) ($definition['seeder']['uuid_namespace'] ?? ''));
        $questions = $definition['questions'] ?? null;
        $questionCount = is_array($questions) ? count(array_filter($questions, 'is_array')) : 0;

        $checks[] = $this->makeCheck(
            $resolvedSeederClass === $expectedSeederClass ? self::STATUS_PASS : self::STATUS_FAIL,
            'v3.definition.seeder_class',
            'definition.json points to the expected V3 seeder class',
            [
                'expected' => $expectedSeederClass,
                'actual' => $resolvedSeederClass !== '' ? $resolvedSeederClass : null,
            ]
        );

        $checks[] = $this->makeCheck(
            $configuredUuidNamespace !== '' ? self::STATUS_PASS : self::STATUS_WARN,
            'v3.definition.uuid_namespace',
            'definition.json declares seeder.uuid_namespace explicitly',
            $configuredUuidNamespace !== ''
                ? ['uuid_namespace' => $configuredUuidNamespace]
                : ['definition' => $target['definition_relative_path']]
        );

        $checks[] = $this->makeCheck(
            is_array($questions) ? self::STATUS_PASS : self::STATUS_FAIL,
            'v3.definition.questions_array',
            'definition.json contains a questions array',
            ['question_count' => $questionCount]
        );

        if (is_array($questions)) {
            $checks[] = $this->makeCheck(
                $this->readinessStatus($questionCount > 0, $profile),
                'v3.questions.readiness',
                'The base V3 definition has questions ready for seeding',
                ['question_count' => $questionCount]
            );
        }

        return $checks;
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array{0: array<string, mixed>|null, 1: array<int, string>, 2: array<string, mixed>}
     */
    private function questionStructureCheck(
        array $definition,
        string $definitionAbsolutePath,
        string $expectedSeederClass,
    ): array {
        $questions = (array) ($definition['questions'] ?? []);
        $issues = [];
        $questionArrayCount = 0;

        foreach ($questions as $offset => $questionDefinition) {
            if (! is_array($questionDefinition)) {
                $issues[] = 'questions.' . $offset . ' must be an object.';

                continue;
            }

            $questionArrayCount++;

            if (trim((string) ($questionDefinition['question'] ?? '')) === '') {
                $issues[] = 'questions.' . $offset . '.question must not be empty.';
            }

            if (! $this->hasResolvableMarkers($questionDefinition)) {
                $issues[] = 'questions.' . $offset . ' must define at least one answer marker.';
            }
        }

        $questionIndex = null;
        $questionUuids = [];

        try {
            $questionIndex = $this->definitionIndex->indexQuestions(
                $definition,
                $definitionAbsolutePath,
                $expectedSeederClass
            );
            $questionUuids = array_keys((array) ($questionIndex['items'] ?? []));

            if (count($questionUuids) !== $questionArrayCount) {
                $issues[] = 'Question UUIDs collide or duplicate across the questions array.';
            }
        } catch (\Throwable $exception) {
            $issues[] = $exception->getMessage();
        }

        return [
            $questionIndex,
            $questionUuids,
            $this->makeCheck(
                $issues === [] ? self::STATUS_PASS : self::STATUS_FAIL,
                'v3.questions.structure',
                'Each V3 question is structurally seedable',
                $issues === []
                    ? ['question_count' => $questionArrayCount]
                    : ['issues' => array_slice($issues, 0, 8)]
            ),
        ];
    }

    /**
     * @param  array<string, mixed>  $questionDefinition
     */
    private function hasResolvableMarkers(array $questionDefinition): bool
    {
        $markers = $questionDefinition['markers'] ?? null;

        if (is_array($markers) && $markers !== []) {
            foreach ($markers as $markerDefinition) {
                if (is_array($markerDefinition) && trim((string) ($markerDefinition['answer'] ?? '')) !== '') {
                    return true;
                }
            }
        }

        $answers = $questionDefinition['answers'] ?? null;

        if (is_array($answers) && $answers !== []) {
            foreach ($answers as $marker => $answerDefinition) {
                $markerName = trim((string) $marker);
                $answer = is_array($answerDefinition)
                    ? trim((string) ($answerDefinition['answer'] ?? ''))
                    : trim((string) $answerDefinition);

                if ($markerName !== '' && $answer !== '') {
                    return true;
                }
            }
        }

        return trim((string) ($questionDefinition['correct'] ?? '')) !== '';
    }

    /**
     * @param  array<string, mixed>  $definition
     * @param  array<int, string>  $questionUuids
     * @return array<string, mixed>
     */
    private function savedTestCheck(array $definition, array $questionUuids, string $expectedSeederClass): array
    {
        $savedTest = $definition['saved_test'] ?? null;

        if (! is_array($savedTest) || $savedTest === []) {
            return $this->makeCheck(
                self::STATUS_FAIL,
                'v3.saved_test.contract',
                'saved_test is present and internally consistent',
                ['missing' => 'saved_test']
            );
        }

        $issues = [];
        $warnings = [];
        $uuid = trim((string) ($savedTest['uuid'] ?? ''));
        $slug = trim((string) ($savedTest['slug'] ?? ''));
        $name = trim((string) ($savedTest['name'] ?? ''));

        if ($uuid === '' || $slug === '' || $name === '') {
            $issues[] = 'saved_test must define uuid, slug, and name.';
        }

        if ($uuid !== '' && strlen($uuid) > 36) {
            $issues[] = 'saved_test.uuid must be at most 36 characters.';
        }

        $orderedQuestionUuids = [];
        $questionUuidPayload = $savedTest['question_uuids'] ?? [];

        if (is_array($questionUuidPayload) && $questionUuidPayload !== []) {
            foreach ($questionUuidPayload as $questionUuid) {
                $clean = trim((string) $questionUuid);

                if ($clean !== '' && ! in_array($clean, $orderedQuestionUuids, true)) {
                    $orderedQuestionUuids[] = $clean;
                }
            }

            $missingQuestionUuids = array_values(array_diff($questionUuids, $orderedQuestionUuids));
            $unknownQuestionUuids = array_values(array_diff($orderedQuestionUuids, $questionUuids));

            if ($missingQuestionUuids !== [] || $unknownQuestionUuids !== []) {
                $issues[] = 'saved_test.question_uuids must contain the same UUID set as questions.';
            }
        } elseif ($questionUuids !== []) {
            $warnings[] = 'saved_test.question_uuids is empty; runtime will fallback to the full question UUID list.';
        }

        $filters = is_array($savedTest['filters'] ?? null) ? $savedTest['filters'] : [];
        $seederClasses = array_values(array_filter(array_map(
            static fn (mixed $value): string => trim((string) $value),
            (array) ($filters['seeder_classes'] ?? [])
        )));

        if (! in_array($expectedSeederClass, $seederClasses, true)) {
            $warnings[] = 'saved_test.filters.seeder_classes does not include the package seeder class.';
        }

        if ($questionUuids !== []
            && array_key_exists('num_questions', $filters)
            && (int) $filters['num_questions'] !== count($questionUuids)) {
            $warnings[] = 'saved_test.filters.num_questions does not match the definition question count.';
        }

        if (array_key_exists('prompt_generator', $filters)) {
            $validation = PromptGeneratorFilterNormalizer::validateTheoryPagePayload(
                $filters['prompt_generator'],
                'saved_test.filters.prompt_generator'
            );

            if ($validation['errors'] !== []) {
                foreach ($validation['errors'] as $error) {
                    $issues[] = (string) ($error['field'] ?? 'saved_test.filters.prompt_generator')
                        . ': '
                        . (string) ($error['message'] ?? 'Invalid theory-page linkage.');
                }
            }
        } elseif ($this->definitionLooksTheoryLinked($definition)) {
            $issues[] = 'saved_test.filters.prompt_generator is required for theory_page sourced V3 packages.';
        }

        return $this->makeCheck(
            $issues !== []
                ? self::STATUS_FAIL
                : ($warnings !== [] ? self::STATUS_WARN : self::STATUS_PASS),
            'v3.saved_test.contract',
            'saved_test is present and internally consistent',
            [
                'issues' => array_slice($issues, 0, 6),
                'warnings' => array_slice($warnings, 0, 6),
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function definitionLooksTheoryLinked(array $definition): bool
    {
        foreach (array_keys((array) ($definition['sources'] ?? [])) as $sourceKey) {
            if (Str::startsWith((string) $sourceKey, 'theory_page_')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $target
     * @param  array<string, mixed>|null  $questionIndex
     * @param  array<int, string>  $questionUuids
     * @return array<int, array<string, mixed>>
     */
    private function localizationChecks(
        array $target,
        string $expectedSeederClass,
        string $profile,
        ?array $questionIndex,
        array $questionUuids,
    ): array {
        $checks = [];
        $expectedQuestionCount = count($questionUuids);

        foreach ((array) $target['localizations'] as $locale => $absolutePath) {
            $relativePath = $this->relativePath((string) $absolutePath);

            if (! File::exists((string) $absolutePath)) {
                $checks[] = $this->makeCheck(
                    self::STATUS_FAIL,
                    'v3.localization.' . $locale . '.contract',
                    strtoupper((string) $locale) . ' localization file matches the V3 package contract',
                    ['missing' => $relativePath]
                );

                continue;
            }

            $load = $this->loadJsonFile((string) $absolutePath, strtoupper((string) $locale) . ' V3 localization');

            if ($load['error'] !== null) {
                $checks[] = $this->makeCheck(
                    self::STATUS_FAIL,
                    'v3.localization.' . $locale . '.contract',
                    strtoupper((string) $locale) . ' localization file matches the V3 package contract',
                    ['error' => $load['error']]
                );

                continue;
            }

            /** @var array<string, mixed> $payload */
            $payload = $load['data'];
            $questions = $payload['questions'] ?? null;
            $localeValue = $this->normalizeLocale((string) ($payload['locale'] ?? ''));
            $targetSeederClass = trim((string) ($payload['target']['seeder_class'] ?? ''));
            $definitionPathSetting = trim((string) ($payload['target']['definition_path'] ?? ''));
            $definitionPathCandidate = $definitionPathSetting !== ''
                ? dirname((string) $absolutePath) . DIRECTORY_SEPARATOR . $definitionPathSetting
                : '';
            $resolvedDefinitionPath = $definitionPathCandidate !== ''
                ? $this->normalizePath((string) (realpath($definitionPathCandidate) ?: $definitionPathCandidate))
                : '';
            $expectedLocalizationClass = $this->expectedLocalizationSeederClass($expectedSeederClass, (string) $locale);
            $actualLocalizationClass = trim((string) ($payload['seeder']['class'] ?? ''));
            $issues = [];

            if (! is_array($questions)) {
                $issues[] = 'questions must be an array.';
            }

            if ($localeValue !== $this->normalizeLocale((string) $locale)) {
                $issues[] = 'locale must match the file name.';
            }

            if ($targetSeederClass !== $expectedSeederClass) {
                $issues[] = 'target.seeder_class must match the package seeder class.';
            }

            if ($resolvedDefinitionPath !== $this->normalizePath((string) $target['definition_absolute_path'])) {
                $issues[] = 'target.definition_path must resolve to the package definition.json file.';
            }

            if ($actualLocalizationClass !== $expectedLocalizationClass) {
                $issues[] = 'seeder.class must match the expected localization seeder class.';
            }

            $checks[] = $this->makeCheck(
                $issues === [] ? self::STATUS_PASS : self::STATUS_FAIL,
                'v3.localization.' . $locale . '.contract',
                strtoupper((string) $locale) . ' localization file matches the V3 package contract',
                $issues === []
                    ? ['path' => $relativePath]
                    : ['issues' => array_slice($issues, 0, 6)]
            );

            if (! is_array($questions) || $questionIndex === null) {
                continue;
            }

            $matchedQuestionUuids = [];
            $unresolvedReferences = [];

            foreach ($questions as $offset => $questionReference) {
                if (! is_array($questionReference)) {
                    $unresolvedReferences[] = 'questions.' . $offset;

                    continue;
                }

                $resolvedQuestionUuid = $this->definitionIndex->resolveIndexedQuestionUuid($questionIndex, $questionReference);

                if ($resolvedQuestionUuid === null) {
                    $unresolvedReferences[] = 'questions.' . $offset;

                    continue;
                }

                $matchedQuestionUuids[$resolvedQuestionUuid] = true;
            }

            $matchedCount = count($matchedQuestionUuids);
            $coverageReady = $expectedQuestionCount > 0
                && $matchedCount === $expectedQuestionCount
                && $unresolvedReferences === [];

            $checks[] = $this->makeCheck(
                $coverageReady
                    ? self::STATUS_PASS
                    : $this->readinessStatus(false, $profile),
                'v3.localization.' . $locale . '.coverage',
                strtoupper((string) $locale) . ' localization covers the V3 question set',
                [
                    'expected' => $expectedQuestionCount,
                    'matched' => $matchedCount,
                    'unresolved' => array_slice($unresolvedReferences, 0, 6),
                ]
            );
        }

        return $checks;
    }

    private function expectedSeederClass(string $packageRootRelativePath): string
    {
        $relative = Str::after(str_replace('\\', '/', $packageRootRelativePath), 'database/seeders/V3/');
        $segments = array_values(array_filter(explode('/', $relative)));
        $className = (string) array_pop($segments);
        $namespace = implode('\\', $segments);

        return 'Database\\Seeders\\V3\\' . ($namespace !== '' ? $namespace . '\\' : '') . $className;
    }

    private function expectedLocalizationSeederClass(string $expectedSeederClass, string $locale): string
    {
        $className = Str::afterLast($expectedSeederClass, '\\');
        $namespace = Str::beforeLast(
            Str::after($expectedSeederClass, 'Database\\Seeders\\V3\\'),
            '\\' . $className
        );
        $localizationClass = Str::replaceLast('Seeder', 'LocalizationSeeder', $className);
        $localeNamespace = Str::ucfirst($this->normalizeLocale($locale));

        return 'Database\\Seeders\\V3\\Localizations\\'
            . $localeNamespace
            . '\\'
            . ($namespace !== '' ? $namespace . '\\' : '')
            . $localizationClass;
    }

    private function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        return $normalized === 'ua' ? 'uk' : $normalized;
    }
}
