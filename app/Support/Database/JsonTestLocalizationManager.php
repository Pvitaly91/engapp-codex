<?php

namespace App\Support\Database;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Finder\SplFileInfo;

class JsonTestLocalizationManager
{
    /**
     * @var array<string, array<string, mixed>>|null
     */
    private ?array $descriptorCache = null;

    public function __construct(private readonly JsonTestDefinitionIndex $definitionIndex)
    {
    }

    public function isVirtualLocalizationSeeder(string $className): bool
    {
        return $this->descriptorForClass($className) !== null;
    }

    public function virtualSeederClasses(): array
    {
        return $this->descriptorMap()->keys()->all();
    }

    public function descriptorForClass(string $className): ?array
    {
        return $this->descriptorMap()->get($className);
    }

    public function filePathForClass(string $className): ?string
    {
        return $this->descriptorForClass($className)['path'] ?? null;
    }

    public function mergeDefinitionLocalizations(
        array $definition,
        ?string $definitionPath = null,
        ?string $fallbackSeederClass = null,
    ): array {
        $baseSeederClass = $this->definitionIndex->resolveSeederClassName($definition, $fallbackSeederClass);
        $baseIndex = $this->definitionIndex->indexQuestions($definition, $definitionPath, $baseSeederClass);
        $questionIndexesByUuid = [];

        foreach (array_keys($baseIndex['items'] ?? []) as $arrayIndex => $uuid) {
            $questionIndexesByUuid[$uuid] = $arrayIndex;
        }

        foreach ($this->descriptorMap() as $descriptor) {
            $localizationDefinition = $this->loadLocalizationDefinition((string) $descriptor['path']);

            if (! $this->matchesTarget($localizationDefinition, $definitionPath, $baseSeederClass)) {
                continue;
            }

            $locale = $this->normalizeLocale((string) ($localizationDefinition['locale'] ?? ''));

            if ($locale === '') {
                continue;
            }

            foreach ($this->resolveLocalizationQuestions($localizationDefinition, $baseIndex) as $resolvedQuestion) {
                $uuid = $resolvedQuestion['uuid'];
                $questionArrayIndex = $questionIndexesByUuid[$uuid] ?? null;

                if (! is_int($questionArrayIndex) || ! isset($definition['questions'][$questionArrayIndex])) {
                    continue;
                }

                $payload = $resolvedQuestion['payload'];
                $existingLocalePayload = $definition['questions'][$questionArrayIndex]['localizations'][$locale] ?? [];

                if (! is_array($existingLocalePayload)) {
                    $existingLocalePayload = [];
                }

                if (array_key_exists('hint_provider', $payload)) {
                    $existingLocalePayload['hint_provider'] = $payload['hint_provider'];
                }

                if (array_key_exists('hints', $payload)) {
                    $existingLocalePayload['hints'] = $payload['hints'];
                }

                if (array_key_exists('explanations', $payload)) {
                    $existingLocalePayload['explanations'] = array_replace_recursive(
                        is_array($existingLocalePayload['explanations'] ?? null) ? $existingLocalePayload['explanations'] : [],
                        $payload['explanations']
                    );
                }

                $definition['questions'][$questionArrayIndex]['localizations'][$locale] = $existingLocalePayload;
            }
        }

        return $definition;
    }

    public function applyVirtualSeeder(string $className): array
    {
        $definition = $this->loadVirtualSeederDefinition($className);
        $baseIndex = $this->targetDefinitionIndex($definition);
        $resolvedQuestions = $this->resolveLocalizationQuestions($definition, $baseIndex);
        $questionsByUuid = Question::query()
            ->whereIn('uuid', collect($resolvedQuestions)->pluck('uuid')->all())
            ->get()
            ->keyBy('uuid');

        $questionsUpdated = 0;
        $hintUpserts = 0;
        $explanationUpserts = 0;
        $missingQuestions = 0;

        foreach ($resolvedQuestions as $resolvedQuestion) {
            /** @var Question|null $question */
            $question = $questionsByUuid->get($resolvedQuestion['uuid']);

            if (! $question) {
                $missingQuestions++;

                continue;
            }

            $questionsUpdated++;
            $payload = $resolvedQuestion['payload'];
            $locale = $payload['locale'];
            $provider = $payload['hint_provider'];
            $hintText = $this->formatHints($payload['hints']);

            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'provider' => $provider,
                        'locale' => $locale,
                    ],
                    ['hint' => $hintText]
                );

                $hintUpserts++;
            }

            foreach ($payload['explanations'] as $marker => $markerExplanations) {
                if (! is_array($markerExplanations)) {
                    continue;
                }

                $correctAnswer = $resolvedQuestion['indexed_question']['answers'][$marker] ?? null;

                if (! is_string($correctAnswer) || trim($correctAnswer) === '') {
                    continue;
                }

                foreach ($markerExplanations as $wrongAnswer => $text) {
                    $clean = trim((string) $text);

                    if ($clean === '') {
                        continue;
                    }

                    foreach ($this->explanationLanguagesForLocale($locale) as $language) {
                        ChatGPTExplanation::updateOrCreate(
                            [
                                'question' => $question->question,
                                'wrong_answer' => (string) $wrongAnswer,
                                'correct_answer' => $correctAnswer,
                                'language' => $language,
                            ],
                            ['explanation' => $clean]
                        );

                        $explanationUpserts++;
                    }
                }
            }
        }

        if ($questionsUpdated === 0) {
            throw new RuntimeException('Локалізацію не застосовано: цільові питання ще не створені або не знайдені в базі.');
        }

        return [
            'locale' => $this->normalizeLocale((string) ($definition['locale'] ?? '')),
            'questions_updated' => $questionsUpdated,
            'hints_upserted' => $hintUpserts,
            'explanations_upserted' => $explanationUpserts,
            'missing_questions' => $missingQuestions,
        ];
    }

    public function removeVirtualSeederData(string $className): array
    {
        $definition = $this->loadVirtualSeederDefinition($className);
        $baseIndex = $this->targetDefinitionIndex($definition);
        $resolvedQuestions = $this->resolveLocalizationQuestions($definition, $baseIndex);
        $questionsByUuid = Question::query()
            ->whereIn('uuid', collect($resolvedQuestions)->pluck('uuid')->all())
            ->get()
            ->keyBy('uuid');

        $deletedHints = 0;
        $deletedExplanations = 0;

        foreach ($resolvedQuestions as $resolvedQuestion) {
            /** @var Question|null $question */
            $question = $questionsByUuid->get($resolvedQuestion['uuid']);

            if (! $question) {
                continue;
            }

            $payload = $resolvedQuestion['payload'];
            $locale = $payload['locale'];
            $provider = $payload['hint_provider'];
            $correctAnswers = collect($resolvedQuestion['indexed_question']['answers'] ?? [])
                ->filter(fn ($answer) => is_string($answer) && trim($answer) !== '')
                ->unique()
                ->values()
                ->all();

            $deletedHints += QuestionHint::query()
                ->where('question_id', $question->id)
                ->where('provider', $provider)
                ->where('locale', $locale)
                ->delete();

            if ($correctAnswers === []) {
                continue;
            }

            $deletedExplanations += ChatGPTExplanation::query()
                ->where('question', $question->question)
                ->whereIn('correct_answer', $correctAnswers)
                ->whereIn('language', $this->explanationLanguagesForLocale($locale))
                ->delete();
        }

        return [
            'locale' => $this->normalizeLocale((string) ($definition['locale'] ?? '')),
            'deleted_hints' => $deletedHints,
            'deleted_explanations' => $deletedExplanations,
        ];
    }

    public function buildVirtualSeederPreview(string $className): array
    {
        $definition = $this->loadVirtualSeederDefinition($className);
        $baseIndex = $this->targetDefinitionIndex($definition);
        $resolvedQuestions = $this->resolveLocalizationQuestions($definition, $baseIndex);
        $questionsByUuid = Question::query()
            ->whereIn('uuid', collect($resolvedQuestions)->pluck('uuid')->all())
            ->pluck('id', 'uuid');

        $questions = collect($resolvedQuestions)
            ->map(function (array $resolvedQuestion) use ($questionsByUuid) {
                $indexedQuestion = $resolvedQuestion['indexed_question'];
                $payload = $resolvedQuestion['payload'];
                $explanations = [];

                foreach ($payload['explanations'] as $marker => $markerExplanations) {
                    $correctAnswer = $indexedQuestion['answers'][$marker] ?? null;

                    foreach ($markerExplanations as $wrongAnswer => $text) {
                        $explanations[] = [
                            'marker' => $marker,
                            'wrong_answer' => (string) $wrongAnswer,
                            'correct_answer' => $correctAnswer,
                            'text' => $text,
                        ];
                    }
                }

                return [
                    'uuid' => $resolvedQuestion['uuid'],
                    'raw_text' => $indexedQuestion['question'],
                    'question_id' => $questionsByUuid[$resolvedQuestion['uuid']] ?? null,
                    'database_exists' => $questionsByUuid->has($resolvedQuestion['uuid']),
                    'hints' => $payload['hints'],
                    'answers' => collect($indexedQuestion['answers'] ?? [])
                        ->map(fn ($answer, $marker) => [
                            'marker' => $marker,
                            'label' => $answer,
                        ])
                        ->values(),
                    'explanations' => $explanations,
                ];
            })
            ->values();

        return [
            'type' => 'question_localizations',
            'locale' => $this->normalizeLocale((string) ($definition['locale'] ?? '')),
            'target' => [
                'seeder_class' => trim((string) Arr::get($definition, 'target.seeder_class', '')),
                'definition' => $baseIndex['definition_key'] ?? trim((string) Arr::get($definition, 'target.definition', '')),
                'definition_path' => $baseIndex['definition_path'] ?? $this->resolveTargetDefinitionPath($definition),
            ],
            'questions' => $questions,
            'localizedQuestionCount' => $questions->count(),
            'existingQuestionCount' => $questions->where('database_exists', true)->count(),
        ];
    }

    private function loadVirtualSeederDefinition(string $className): array
    {
        $descriptor = $this->descriptorForClass($className);

        if (! is_array($descriptor)) {
            throw new RuntimeException("Localization seeder not found: {$className}");
        }

        return $this->loadLocalizationDefinition((string) $descriptor['path']);
    }

    private function loadLocalizationDefinition(string $path): array
    {
        $definition = $this->definitionIndex->loadDefinitionFromFile($path);

        if (! is_array($definition['questions'] ?? null)) {
            throw new RuntimeException("Localization definition must contain a questions array: {$path}");
        }

        return $definition;
    }

    private function descriptorMap(): Collection
    {
        if (is_array($this->descriptorCache)) {
            return collect($this->descriptorCache);
        }

        $directory = $this->localizationsDirectory();

        if (! File::isDirectory($directory)) {
            $this->descriptorCache = [];

            return collect();
        }

        $map = collect(File::allFiles($directory))
            ->filter(fn (SplFileInfo $file) => strtolower($file->getExtension()) === 'json')
            ->mapWithKeys(function (SplFileInfo $file) {
                $definition = $this->loadLocalizationDefinition($file->getPathname());
                $className = $this->resolveVirtualSeederClassName($definition, $file->getPathname());

                return [$className => [
                    'class_name' => $className,
                    'path' => $file->getPathname(),
                    'locale' => $this->normalizeLocale((string) ($definition['locale'] ?? '')),
                    'target_seeder_class' => trim((string) Arr::get($definition, 'target.seeder_class', '')),
                    'target_definition' => trim((string) Arr::get($definition, 'target.definition', '')),
                    'target_definition_path' => $this->resolveTargetDefinitionPath($definition),
                ]];
            })
            ->all();

        $this->descriptorCache = $map;

        return collect($map);
    }

    private function resolveVirtualSeederClassName(array $definition, string $path): string
    {
        $configured = trim((string) Arr::get($definition, 'seeder.class', ''));

        if ($configured !== '') {
            return $configured;
        }

        $directory = rtrim(str_replace('\\', '/', $this->localizationsDirectory()), '/');
        $normalizedPath = str_replace('\\', '/', $path);
        $relativePath = Str::after($normalizedPath, $directory . '/');
        $segments = array_values(array_filter(explode('/', $relativePath), 'strlen'));
        $fileName = array_pop($segments) ?: 'generated';
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $className = Str::studly($baseName);

        if (! Str::endsWith($className, 'Seeder')) {
            $className .= 'Seeder';
        }

        $namespaceSegments = array_map(
            fn (string $segment) => Str::studly(pathinfo($segment, PATHINFO_FILENAME)),
            $segments
        );

        $namespaceSegments[] = $className;

        return 'Database\\Seeders\\V3\\Localizations\\' . implode('\\', $namespaceSegments);
    }

    private function localizationsDirectory(): string
    {
        return database_path('seeders/V3/localizations');
    }

    private function matchesTarget(array $localizationDefinition, ?string $definitionPath, string $baseSeederClass): bool
    {
        $matched = false;
        $targetSeederClass = trim((string) Arr::get($localizationDefinition, 'target.seeder_class', ''));

        if ($targetSeederClass !== '') {
            if ($targetSeederClass !== $baseSeederClass) {
                return false;
            }

            $matched = true;
        }

        $targetDefinition = trim((string) Arr::get($localizationDefinition, 'target.definition', ''));
        $baseDefinitionKey = $this->definitionIndex->definitionKeyFromPath($definitionPath);

        if ($targetDefinition !== '') {
            if ($baseDefinitionKey === null || $targetDefinition !== $baseDefinitionKey) {
                return false;
            }

            $matched = true;
        }

        $targetDefinitionPath = $this->resolveTargetDefinitionPath($localizationDefinition);

        if ($targetDefinitionPath !== null) {
            if (! $this->pathsEqual($targetDefinitionPath, $definitionPath)) {
                return false;
            }

            $matched = true;
        }

        return $matched;
    }

    private function targetDefinitionIndex(array $localizationDefinition): array
    {
        $definitionPath = $this->resolveTargetDefinitionPath($localizationDefinition);

        if ($definitionPath === null) {
            throw new RuntimeException('Localization definition must define target.definition or target.definition_path.');
        }

        $definition = $this->definitionIndex->loadDefinitionFromFile($definitionPath);
        $fallbackSeederClass = trim((string) Arr::get($localizationDefinition, 'target.seeder_class', ''));

        return $this->definitionIndex->indexQuestions($definition, $definitionPath, $fallbackSeederClass);
    }

    private function resolveTargetDefinitionPath(array $localizationDefinition): ?string
    {
        $configuredPath = trim((string) Arr::get($localizationDefinition, 'target.definition_path', ''));

        if ($configuredPath !== '') {
            return $this->normalizeTargetDefinitionPath($configuredPath);
        }

        $definitionKey = trim((string) Arr::get($localizationDefinition, 'target.definition', ''));

        if ($definitionKey === '') {
            return null;
        }

        if (! Str::endsWith(strtolower($definitionKey), '.json')) {
            $definitionKey .= '.json';
        }

        return database_path('seeders/V3/definitions/' . str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $definitionKey));
    }

    private function normalizeTargetDefinitionPath(string $path): string
    {
        if ($this->isAbsolutePath($path)) {
            return $path;
        }

        $normalized = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR);
        $unixPath = str_replace('\\', '/', $normalized);

        if (Str::startsWith($unixPath, 'database/')) {
            return base_path($normalized);
        }

        if (Str::startsWith($unixPath, 'seeders/')) {
            return database_path(Str::after($normalized, 'seeders' . DIRECTORY_SEPARATOR));
        }

        return database_path('seeders/V3/definitions/' . $normalized);
    }

    private function resolveLocalizationQuestions(array $localizationDefinition, array $baseIndex): array
    {
        $locale = $this->normalizeLocale((string) ($localizationDefinition['locale'] ?? ''));

        if ($locale === '') {
            throw new RuntimeException('Localization definition must define a locale.');
        }

        $resolved = [];

        foreach ($localizationDefinition['questions'] as $questionDefinition) {
            if (! is_array($questionDefinition)) {
                continue;
            }

            $indexedQuestion = $this->definitionIndex->resolveIndexedQuestion($baseIndex, $questionDefinition);

            if (! is_array($indexedQuestion)) {
                continue;
            }

            $payload = [
                'locale' => $locale,
                'hint_provider' => $this->resolveHintProvider($localizationDefinition, $questionDefinition),
                'hints' => $this->normalizeHints($questionDefinition['hints'] ?? []),
                'explanations' => $this->normalizeExplanations(
                    $questionDefinition['explanations'] ?? [],
                    $indexedQuestion
                ),
            ];

            if ($payload['hints'] === [] && $payload['explanations'] === []) {
                continue;
            }

            $resolved[] = [
                'uuid' => $indexedQuestion['uuid'],
                'indexed_question' => $indexedQuestion,
                'payload' => $payload,
            ];
        }

        return $resolved;
    }

    private function resolveHintProvider(array $definition, array $questionDefinition): string
    {
        $provider = trim((string) ($questionDefinition['hint_provider'] ?? $definition['hint_provider'] ?? 'chatgpt'));

        return $provider !== '' ? $provider : 'chatgpt';
    }

    private function normalizeHints(mixed $hints): array
    {
        $normalized = [];

        if (is_string($hints)) {
            $clean = trim($hints);

            return $clean !== '' ? [$clean] : [];
        }

        if (! is_array($hints)) {
            return [];
        }

        foreach ($hints as $hint) {
            $this->appendHintFragments($normalized, $hint);
        }

        return $normalized;
    }

    private function appendHintFragments(array &$normalized, mixed $value): void
    {
        if (is_string($value)) {
            $clean = trim($value);

            if ($clean !== '') {
                $normalized[] = $clean;
            }

            return;
        }

        if (! is_array($value)) {
            return;
        }

        foreach ($value as $item) {
            $this->appendHintFragments($normalized, $item);
        }
    }

    private function normalizeExplanations(mixed $payload, array $indexedQuestion): array
    {
        if (! is_array($payload) || $payload === []) {
            return [];
        }

        $normalized = [];
        $markers = $indexedQuestion['markers'] ?? [];
        $optionMarkers = $indexedQuestion['option_markers'] ?? [];
        $fallbackMarker = $markers[0] ?? null;

        if ($this->isFlatExplanationMap($payload)) {
            foreach ($payload as $option => $text) {
                $clean = trim((string) $text);

                if ($clean === '') {
                    continue;
                }

                $marker = $optionMarkers[(string) $option] ?? $fallbackMarker;

                if ($marker === null) {
                    continue;
                }

                $normalized[$marker][(string) $option] = $clean;
            }

            return $normalized;
        }

        foreach ($payload as $marker => $options) {
            if (! is_array($options)) {
                continue;
            }

            $markerName = in_array((string) $marker, $markers, true)
                ? (string) $marker
                : ($fallbackMarker ?? (string) $marker);

            if ($markerName === '') {
                continue;
            }

            foreach ($options as $option => $text) {
                $clean = trim((string) $text);

                if ($clean === '') {
                    continue;
                }

                $normalized[$markerName][(string) $option] = $clean;
            }
        }

        return $normalized;
    }

    private function isFlatExplanationMap(array $payload): bool
    {
        foreach ($payload as $value) {
            if (is_array($value)) {
                return false;
            }
        }

        return true;
    }

    private function formatHints(array $hints): ?string
    {
        if ($hints === []) {
            return null;
        }

        $parts = [];

        foreach ($hints as $hint) {
            $clean = trim((string) $hint);

            if ($clean !== '') {
                $parts[] = $clean;
            }
        }

        if ($parts === []) {
            return null;
        }

        return implode("\n", $parts);
    }

    private function explanationLanguagesForLocale(string $locale): array
    {
        $normalized = $this->normalizeLocale($locale);

        if ($normalized === 'uk') {
            return ['ua', 'uk'];
        }

        return [$normalized];
    }

    private function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        if ($normalized === '' || $normalized === 'ua') {
            return 'uk';
        }

        return $normalized;
    }

    private function pathsEqual(?string $first, ?string $second): bool
    {
        $firstPath = $this->normalizeComparablePath($first);
        $secondPath = $this->normalizeComparablePath($second);

        return $firstPath !== null && $secondPath !== null && $firstPath === $secondPath;
    }

    private function normalizeComparablePath(?string $path): ?string
    {
        $normalized = trim((string) $path);

        if ($normalized === '') {
            return null;
        }

        $realPath = realpath($normalized);

        if ($realPath !== false) {
            return str_replace('\\', '/', $realPath);
        }

        return str_replace('\\', '/', $normalized);
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^(?:[A-Za-z]:[\/\\\\]|\/)/', $path) === 1;
    }
}
