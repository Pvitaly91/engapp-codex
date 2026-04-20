<?php

namespace App\Support\Database;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\SavedGrammarTest;
use App\Models\Source;
use App\Support\PromptGeneratorFilterNormalizer;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

abstract class JsonTestSeeder extends QuestionSeeder
{
    final public function run(): void
    {
        $definition = $this->loadDefinitionFromFile($this->definitionPath());
        $definition = app(JsonTestLocalizationManager::class)->mergeDefinitionLocalizations(
            $definition,
            $this->definitionPath(),
            static::class
        );

        $this->seedDefinition($definition, $this->resolveSeederClassName($definition));
    }

    abstract protected function definitionPath(): string;

    public function resolvedDefinitionPath(): string
    {
        return $this->definitionPath();
    }

    protected function seederDirectory(): string
    {
        $reflection = new \ReflectionClass($this);
        $fileName = $reflection->getFileName();

        if (! is_string($fileName) || $fileName === '') {
            throw new RuntimeException(sprintf(
                'Unable to resolve file path for seeder [%s].',
                static::class
            ));
        }

        return dirname($fileName);
    }

    protected function seederAssetPath(string $relativePath = ''): string
    {
        $directory = rtrim($this->seederDirectory(), DIRECTORY_SEPARATOR);
        $relativePath = ltrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);

        if ($relativePath === '') {
            return $directory;
        }

        return $directory . DIRECTORY_SEPARATOR . $relativePath;
    }

    protected function loadDefinitionFromFile(string $path): array
    {
        if (! File::exists($path)) {
            throw new RuntimeException("V3 test definition not found: {$path}");
        }

        $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid V3 test definition: {$path}");
        }

        return $decoded;
    }

    protected function seedDefinition(array $definition, ?string $seederClass = null): void
    {
        $questions = Arr::get($definition, 'questions', []);

        if (! is_array($questions) || $questions === []) {
            throw new RuntimeException('V3 test definition must contain a non-empty questions array.');
        }

        $seederClass = filled($seederClass) ? $seederClass : static::class;
        $uuidNamespace = $this->resolveUuidNamespace($definition, $seederClass);
        $defaultLocale = $this->normalizeLocale((string) Arr::get($definition, 'defaults.default_locale', 'uk'));
        $defaultFlag = (int) Arr::get($definition, 'defaults.flag', 0);
        $defaultType = Arr::get($definition, 'defaults.type', 0);
        $levelDifficulty = Arr::get($definition, 'defaults.level_difficulty', []);
        $defaultTagKeys = $this->normalizeStringList(Arr::get($definition, 'default_tag_keys', []));

        $categoryId = $this->resolveCategoryId(Arr::get($definition, 'category'));
        $sourceIds = $this->resolveSourceIds(Arr::get($definition, 'sources', []));
        $tagIds = $this->resolveTagIdsByKey(Arr::get($definition, 'tags', []));

        $items = [];
        $localizedPayloads = [];

        foreach ($questions as $index => $questionDefinition) {
            if (! is_array($questionDefinition)) {
                throw new RuntimeException("Question definition at index {$index} must be an object.");
            }

            $questionText = trim((string) ($questionDefinition['question'] ?? ''));

            if ($questionText === '') {
                throw new RuntimeException("Question text is required at index {$index}.");
            }

            $markers = $this->normalizeMarkers($questionDefinition, $defaultLocale);

            if ($markers === []) {
                throw new RuntimeException("Question \"{$questionText}\" must define at least one marker.");
            }

            $answers = [];
            $answersMap = [];
            $optionsByMarker = [];
            $gapTags = $this->normalizeGapTags($questionDefinition['gap_tags'] ?? []);

            foreach ($markers as $marker => $markerData) {
                $answers[] = [
                    'marker' => $marker,
                    'answer' => $markerData['answer'],
                    'verb_hint' => $markerData['verb_hint'],
                ];

                $answersMap[$marker] = $markerData['answer'];
                $optionsByMarker[$marker] = $markerData['options'];

                if ($markerData['gap_tags'] !== []) {
                    $gapTags[$marker] = array_values(array_unique(array_merge(
                        $gapTags[$marker] ?? [],
                        $markerData['gap_tags']
                    )));
                }
            }

            $uuid = $this->resolveQuestionUuid($questionDefinition, $uuidNamespace, $index, $questionText);
            $tagKeys = $this->resolveQuestionTagKeys($questionDefinition, $defaultTagKeys);
            $variants = $this->resolveVariants($questionDefinition, $questionText);
            ['question' => $questionText, 'variants' => $variants] = $this->normalizeQuestionPresentation(
                $questionText,
                $variants,
                $markers
            );

            $items[] = [
                'uuid' => $uuid,
                'question' => $questionText,
                'category_id' => $questionDefinition['category_id'] ?? $categoryId,
                'difficulty' => $this->resolveDifficulty($questionDefinition, $levelDifficulty),
                'source_id' => $this->resolveSourceIdForQuestion($questionDefinition, $sourceIds),
                'flag' => (int) ($questionDefinition['flag'] ?? $defaultFlag),
                'type' => $questionDefinition['type'] ?? $defaultType,
                'level' => $questionDefinition['level'] ?? null,
                'tag_ids' => $this->resolveQuestionTagIds($tagKeys, $tagIds),
                'answers' => $answers,
                'options' => $this->resolveQuestionOptions($questionDefinition, $optionsByMarker, $answersMap),
                'options_by_marker' => $this->resolveOptionsByMarker($questionDefinition, $optionsByMarker),
                'variants' => $variants,
                'theory_text_block_uuid' => $questionDefinition['theory_text_block_uuid'] ?? null,
                'gap_tags' => $gapTags,
                'seeder' => $seederClass,
            ];

            $optionMarkers = array_replace(
                $this->buildOptionMarkerMapFromMarkers($markers),
                $this->normalizeOptionMarkerMap($questionDefinition['option_markers'] ?? [])
            );

            $localizedPayloads[$uuid] = $this->normalizeLocalizations(
                $questionDefinition,
                $defaultLocale,
                array_keys($markers),
                $optionMarkers,
                $answersMap
            );
        }

        $this->seedQuestionData($items, []);
        $this->persistLocalizations($localizedPayloads);
        $this->syncSavedTestFromDefinition(
            Arr::get($definition, 'saved_test'),
            $seederClass,
            array_column($items, 'uuid')
        );
    }

    protected function resolveSeederClassName(array $definition): string
    {
        $configured = trim((string) Arr::get($definition, 'seeder.class', ''));

        return $configured !== '' ? $configured : static::class;
    }

    protected function resolveUuidNamespace(array $definition, string $seederClass): string
    {
        $configured = trim((string) Arr::get($definition, 'seeder.uuid_namespace', ''));

        if ($configured !== '') {
            return $configured;
        }

        return Str::afterLast($seederClass, '\\');
    }

    protected function resolveQuestionUuid(
        array $questionDefinition,
        string $uuidNamespace,
        int $index,
        string $questionText
    ): string {
        $explicitUuid = trim((string) ($questionDefinition['uuid'] ?? ''));

        if ($explicitUuid !== '') {
            return $this->persistentQuestionUuid($explicitUuid);
        }

        $segments = $questionDefinition['uuid_segments'] ?? null;

        if (is_array($segments) && $segments !== []) {
            return $this->persistentQuestionUuid(
                $this->generateScopedQuestionUuid($uuidNamespace, ...$segments)
            );
        }

        $idSegment = $questionDefinition['id'] ?? ($index + 1);

        return $this->persistentQuestionUuid(
            $this->generateScopedQuestionUuid($uuidNamespace, $idSegment, $questionText)
        );
    }

    protected function generateScopedQuestionUuid(string $scope, int|string ...$segments): string
    {
        $base = Str::slug(Str::afterLast($scope, '\\'));

        $normalizedSegments = [];
        foreach ($segments as $segment) {
            $segment = Str::slug((string) $segment);

            if ($segment === '') {
                continue;
            }

            $normalizedSegments[] = $segment;
        }

        $suffix = $normalizedSegments !== [] ? '-' . implode('-', $normalizedSegments) : '';
        $maxLength = self::UUID_LENGTH - strlen($suffix);

        if ($maxLength <= 0) {
            return substr(ltrim($suffix, '-'), 0, self::UUID_LENGTH);
        }

        $base = substr($base, 0, $maxLength);

        if ($base === '') {
            return substr(ltrim($suffix, '-'), 0, self::UUID_LENGTH);
        }

        return $base . $suffix;
    }

    protected function resolveCategoryId(mixed $payload): int
    {
        if (is_string($payload)) {
            $name = trim($payload);

            if ($name !== '') {
                return Category::firstOrCreate(['name' => $name])->id;
            }
        }

        if (is_array($payload)) {
            $name = trim((string) ($payload['name'] ?? ''));
            $id = isset($payload['id']) ? (int) $payload['id'] : null;

            if ($id && $name !== '') {
                return Category::firstOrCreate(['id' => $id], ['name' => $name])->id;
            }

            if ($name !== '') {
                return Category::firstOrCreate(['name' => $name])->id;
            }

            if ($id) {
                return Category::firstOrCreate(['id' => $id], ['name' => 'Category ' . $id])->id;
            }
        }

        throw new RuntimeException('V3 test definition must define a category.');
    }

    protected function resolveSourceIds(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $resolved = [];

        foreach ($payload as $key => $sourceDefinition) {
            $resolved[$key] = $this->resolveSourceId($sourceDefinition);
        }

        return $resolved;
    }

    protected function resolveSourceId(mixed $payload): ?int
    {
        if (is_string($payload)) {
            $name = trim($payload);

            return $name !== '' ? Source::firstOrCreate(['name' => $name])->id : null;
        }

        if (! is_array($payload)) {
            return null;
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $id = isset($payload['id']) ? (int) $payload['id'] : null;

        if ($id && $name !== '') {
            return Source::firstOrCreate(['id' => $id], ['name' => $name])->id;
        }

        if ($name !== '') {
            return Source::firstOrCreate(['name' => $name])->id;
        }

        return $id ?: null;
    }

    protected function resolveTagIdsByKey(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $resolved = [];

        foreach ($payload as $key => $tagDefinition) {
            $resolved[$key] = $this->resolveTagId($tagDefinition);
        }

        return array_filter($resolved);
    }

    protected function resolveTagId(mixed $payload): ?int
    {
        if (is_string($payload)) {
            $name = trim($payload);

            return $name !== '' ? Tag::firstOrCreate(['name' => $name])->id : null;
        }

        if (! is_array($payload)) {
            return null;
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $category = trim((string) ($payload['category'] ?? ''));

        if ($name === '') {
            return null;
        }

        return Tag::firstOrCreate(
            ['name' => $name],
            $category !== '' ? ['category' => $category] : []
        )->id;
    }

    protected function normalizeMarkers(array $questionDefinition, string $defaultLocale): array
    {
        $normalized = [];
        $markers = $questionDefinition['markers'] ?? null;

        if (is_array($markers) && $markers !== []) {
            foreach ($markers as $marker => $markerDefinition) {
                $markerName = trim((string) $marker);

                if ($markerName === '' || ! is_array($markerDefinition)) {
                    continue;
                }

                $answer = trim((string) ($markerDefinition['answer'] ?? ''));

                if ($answer === '') {
                    continue;
                }

                $options = $this->normalizeOptionList($markerDefinition['options'] ?? []);

                if (! in_array($answer, $options, true)) {
                    $options[] = $answer;
                }

                $normalized[$markerName] = [
                    'answer' => $answer,
                    'options' => $options,
                    'verb_hint' => $this->selectLocalizedText($markerDefinition['verb_hint'] ?? null, $defaultLocale),
                    'gap_tags' => $this->normalizeStringList($markerDefinition['gap_tags'] ?? []),
                ];
            }
        }

        if ($normalized !== []) {
            uksort($normalized, 'strnatcmp');

            return $normalized;
        }

        $answers = $questionDefinition['answers'] ?? null;

        if (! is_array($answers) || $answers === []) {
            if (array_key_exists('correct', $questionDefinition)) {
                $answers = ['a1' => $questionDefinition['correct']];
            } else {
                return [];
            }
        }

        $sharedOptions = $this->normalizeOptionList($questionDefinition['options'] ?? []);
        $optionsByMarker = $questionDefinition['options_by_marker'] ?? [];
        $verbHints = is_array($questionDefinition['verb_hints'] ?? null) ? $questionDefinition['verb_hints'] : [];
        $gapTags = is_array($questionDefinition['gap_tags'] ?? null) ? $questionDefinition['gap_tags'] : [];

        foreach ($answers as $marker => $answerDefinition) {
            $markerName = trim((string) $marker);
            $answer = '';
            $verbHintValue = $verbHints[$markerName] ?? null;
            $markerGapTags = $gapTags[$markerName] ?? [];

            if (is_array($answerDefinition)) {
                $markerName = trim((string) ($answerDefinition['marker'] ?? $markerName));
                $answer = trim((string) ($answerDefinition['answer'] ?? ''));
                $verbHintValue = $answerDefinition['verb_hint'] ?? $verbHintValue;
                $markerGapTags = $answerDefinition['gap_tags'] ?? $markerGapTags;
            } else {
                $answer = trim((string) $answerDefinition);
            }

            if ($markerName === '' || $answer === '') {
                continue;
            }

            $markerOptions = $this->normalizeOptionList($optionsByMarker[$markerName] ?? $sharedOptions);

            if (! in_array($answer, $markerOptions, true)) {
                $markerOptions[] = $answer;
            }

            $normalized[$markerName] = [
                'answer' => $answer,
                'options' => $markerOptions,
                'verb_hint' => $this->selectLocalizedText($verbHintValue, $defaultLocale),
                'gap_tags' => $this->normalizeStringList($markerGapTags),
            ];
        }

        uksort($normalized, 'strnatcmp');

        return $normalized;
    }

    protected function resolveQuestionOptions(array $questionDefinition, array $optionsByMarker, array $answersMap): array
    {
        $explicitOptions = $this->normalizeOptionList($questionDefinition['options'] ?? []);

        if ($explicitOptions !== []) {
            foreach ($answersMap as $answer) {
                if (! in_array($answer, $explicitOptions, true)) {
                    $explicitOptions[] = $answer;
                }
            }

            return $explicitOptions;
        }

        return $this->flattenOptions($optionsByMarker);
    }

    protected function resolveOptionsByMarker(array $questionDefinition, array $inferredOptionsByMarker): array
    {
        $explicit = $questionDefinition['options_by_marker'] ?? null;

        if (! is_array($explicit) || $explicit === []) {
            return $inferredOptionsByMarker;
        }

        $normalized = [];

        foreach ($explicit as $marker => $options) {
            $markerName = trim((string) $marker);

            if ($markerName === '') {
                continue;
            }

            $normalized[$markerName] = $this->normalizeOptionList($options);
        }

        return $normalized !== [] ? $normalized : $inferredOptionsByMarker;
    }

    protected function resolveVariants(array $questionDefinition, string $questionText): array
    {
        if (! array_key_exists('variants', $questionDefinition)) {
            return [$questionText];
        }

        return $this->normalizeStringList($questionDefinition['variants']);
    }

    protected function normalizeQuestionPresentation(string $questionText, array $variants, array $markers): array
    {
        return [
            'question' => $questionText,
            'variants' => $variants,
        ];
    }

    protected function stripDuplicatedVerbHintTail(string $text, array $markers): string
    {
        foreach ($markers as $marker => $markerDefinition) {
            $verbHint = trim((string) ($markerDefinition['verb_hint'] ?? ''));

            if ($verbHint === '') {
                continue;
            }

            $markerPattern = preg_quote('{'.$marker.'}', '/');
            $hintPattern = preg_quote($verbHint, '/');

            $text = preg_replace(
                "/{$markerPattern}\\h+{$hintPattern}(?=(?:[\\h\\v]|[\\.,!\\?;:\\)])|$)/u",
                '{'.$marker.'}',
                $text
            ) ?? $text;
        }

        return $text;
    }

    protected function resolveSourceIdForQuestion(array $questionDefinition, array $sourceIds): ?int
    {
        if (isset($questionDefinition['source_id'])) {
            return (int) $questionDefinition['source_id'];
        }

        $sourceKey = $questionDefinition['source'] ?? null;

        if (is_string($sourceKey) && array_key_exists($sourceKey, $sourceIds)) {
            return $sourceIds[$sourceKey];
        }

        return $sourceIds === [] ? null : reset($sourceIds);
    }

    protected function resolveDifficulty(array $questionDefinition, mixed $levelDifficulty): int
    {
        if (isset($questionDefinition['difficulty'])) {
            return (int) $questionDefinition['difficulty'];
        }

        $level = trim((string) ($questionDefinition['level'] ?? ''));

        if ($level !== '' && is_array($levelDifficulty) && array_key_exists($level, $levelDifficulty)) {
            return (int) $levelDifficulty[$level];
        }

        return 3;
    }

    protected function resolveQuestionTagKeys(array $questionDefinition, array $defaultTagKeys): array
    {
        $questionTagKeys = $this->normalizeStringList($questionDefinition['tag_keys'] ?? []);
        $replaceDefaults = (bool) ($questionDefinition['replace_default_tag_keys'] ?? false);

        $keys = $replaceDefaults
            ? $questionTagKeys
            : array_merge($defaultTagKeys, $questionTagKeys);

        return array_values(array_unique($keys));
    }

    protected function resolveQuestionTagIds(array $tagKeys, array $tagIds): array
    {
        $resolved = [];

        foreach ($tagKeys as $tagKey) {
            if (array_key_exists($tagKey, $tagIds)) {
                $resolved[] = $tagIds[$tagKey];
            }
        }

        return array_values(array_unique($resolved));
    }

    protected function normalizeLocalizations(
        array $questionDefinition,
        string $defaultLocale,
        array $markers,
        array $optionMarkers,
        array $answersMap
    ): array {
        $localizations = $questionDefinition['localizations'] ?? [];

        if (! is_array($localizations)) {
            $localizations = [];
        }

        $legacyDefault = [];

        if (array_key_exists('hints', $questionDefinition)) {
            $legacyDefault['hints'] = $questionDefinition['hints'];
        }

        if (array_key_exists('explanations', $questionDefinition)) {
            $legacyDefault['explanations'] = $questionDefinition['explanations'];
        }

        if (array_key_exists('hint_provider', $questionDefinition)) {
            $legacyDefault['hint_provider'] = $questionDefinition['hint_provider'];
        }

        if ($legacyDefault !== []) {
            $existing = $localizations[$defaultLocale] ?? [];
            $localizations[$defaultLocale] = is_array($existing)
                ? array_merge($existing, $legacyDefault)
                : $legacyDefault;
        }

        $normalized = [];

        foreach ($localizations as $locale => $payload) {
            if (! is_array($payload)) {
                continue;
            }

            $normalizedLocale = $this->normalizeLocale((string) $locale);
            $provider = trim((string) ($payload['hint_provider'] ?? $questionDefinition['hint_provider'] ?? 'chatgpt'));
            $provider = $provider !== '' ? $provider : 'chatgpt';

            $hints = $this->normalizeHints($payload['hints'] ?? [], $markers);
            $explanations = $this->normalizeLocalizedExplanations(
                $payload['explanations'] ?? [],
                $markers,
                $optionMarkers
            );

            if ($hints === [] && $explanations === []) {
                continue;
            }

            $normalized[$normalizedLocale] = [
                'provider' => $provider,
                'hints' => $hints,
                'explanations' => $explanations,
                'answers' => $answersMap,
            ];
        }

        return $normalized;
    }

    protected function normalizeHints(mixed $hints, array $markers): array
    {
        $normalized = [];

        if (is_string($hints)) {
            $clean = trim($hints);

            return $clean !== '' ? [$clean] : [];
        }

        if (! is_array($hints)) {
            return [];
        }

        if (array_is_list($hints)) {
            foreach ($hints as $hint) {
                $this->appendHintFragments($normalized, $hint);
            }

            return $normalized;
        }

        $processedKeys = [];

        foreach ($markers as $marker) {
            if (! array_key_exists($marker, $hints)) {
                continue;
            }

            $processedKeys[] = $marker;
            $this->appendHintFragments($normalized, $hints[$marker]);
        }

        foreach ($hints as $key => $value) {
            if (in_array($key, $processedKeys, true)) {
                continue;
            }

            $this->appendHintFragments($normalized, $value);
        }

        return $normalized;
    }

    protected function appendHintFragments(array &$normalized, mixed $value): void
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

    protected function normalizeLocalizedExplanations(mixed $payload, array $markers, array $optionMarkers): array
    {
        if (! is_array($payload) || $payload === []) {
            return [];
        }

        $normalized = [];
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

    protected function isFlatExplanationMap(array $payload): bool
    {
        foreach ($payload as $value) {
            if (is_array($value)) {
                return false;
            }
        }

        return true;
    }

    protected function buildOptionMarkerMapFromMarkers(array $markers): array
    {
        $map = [];

        foreach ($markers as $marker => $markerDefinition) {
            foreach ($markerDefinition['options'] as $option) {
                $option = trim((string) $option);

                if ($option !== '' && ! array_key_exists($option, $map)) {
                    $map[$option] = $marker;
                }
            }
        }

        return $map;
    }

    protected function normalizeOptionMarkerMap(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $normalized = [];

        foreach ($payload as $option => $marker) {
            $optionName = trim((string) $option);
            $markerName = trim((string) $marker);

            if ($optionName === '' || $markerName === '') {
                continue;
            }

            $normalized[$optionName] = $markerName;
        }

        return $normalized;
    }

    protected function normalizeGapTags(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $normalized = [];

        foreach ($payload as $marker => $tags) {
            $markerName = trim((string) $marker);

            if ($markerName === '') {
                continue;
            }

            $normalized[$markerName] = $this->normalizeStringList($tags);
        }

        return $normalized;
    }

    protected function persistLocalizations(array $localizedPayloads): void
    {
        if ($localizedPayloads === []) {
            return;
        }

        $questionsByUuid = Question::query()
            ->whereIn('uuid', array_keys($localizedPayloads))
            ->get()
            ->keyBy('uuid');

        foreach ($localizedPayloads as $uuid => $localizations) {
            /** @var Question|null $question */
            $question = $questionsByUuid->get($uuid);

            if (! $question || ! is_array($localizations)) {
                continue;
            }

            foreach ($localizations as $locale => $payload) {
                if (! is_array($payload)) {
                    continue;
                }

                $hintText = $this->formatHints($payload['hints'] ?? []);

                if ($hintText !== null) {
                    QuestionHint::updateOrCreate(
                        [
                            'question_id' => $question->id,
                            'provider' => $payload['provider'] ?? 'chatgpt',
                            'locale' => $this->normalizeLocale((string) $locale),
                        ],
                        ['hint' => $hintText]
                    );
                }

                $answers = is_array($payload['answers'] ?? null) ? $payload['answers'] : [];

                foreach ($payload['explanations'] ?? [] as $marker => $markerExplanations) {
                    if (! is_array($markerExplanations)) {
                        continue;
                    }

                    $correct = $answers[$marker] ?? reset($answers);

                    if (! is_string($correct)) {
                        $correct = (string) $correct;
                    }

                    if ($correct === '') {
                        continue;
                    }

                    foreach ($markerExplanations as $option => $text) {
                        $clean = trim((string) $text);

                        if ($clean === '') {
                            continue;
                        }

                        foreach ($this->explanationLanguagesForLocale((string) $locale) as $language) {
                            ChatGPTExplanation::updateOrCreate(
                                [
                                    'question' => $question->question,
                                    'wrong_answer' => (string) $option,
                                    'correct_answer' => $correct,
                                    'language' => $language,
                                ],
                                ['explanation' => $clean]
                            );
                        }
                    }
                }
            }
        }
    }

    protected function syncSavedTestFromDefinition(
        mixed $payload,
        string $seederClass,
        array $questionUuids,
    ): ?SavedGrammarTest {
        if (! is_array($payload) || $payload === []) {
            return null;
        }

        $uuid = trim((string) ($payload['uuid'] ?? ''));
        $slug = trim((string) ($payload['slug'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));

        if ($uuid === '' || $slug === '' || $name === '') {
            throw new RuntimeException('saved_test must define uuid, slug and name.');
        }

        if (strlen($uuid) > self::UUID_LENGTH) {
            throw new RuntimeException(sprintf(
                'saved_test.uuid must be at most %d characters.',
                self::UUID_LENGTH
            ));
        }

        $orderedQuestionUuids = $this->normalizeSavedTestQuestionUuids(
            $payload['question_uuids'] ?? [],
            $questionUuids
        );

        $missingQuestionUuids = array_values(array_diff($questionUuids, $orderedQuestionUuids));
        $unknownQuestionUuids = array_values(array_diff($orderedQuestionUuids, $questionUuids));

        if ($missingQuestionUuids !== [] || $unknownQuestionUuids !== []) {
            throw new RuntimeException('saved_test.question_uuids must contain the same UUID set as questions.');
        }

        $filters = is_array($payload['filters'] ?? null) ? $payload['filters'] : [];
        $normalizedPromptGenerator = PromptGeneratorFilterNormalizer::normalize(
            $filters['prompt_generator'] ?? null
        );

        if ($normalizedPromptGenerator !== null || array_key_exists('prompt_generator', $filters)) {
            $filters['prompt_generator'] = $normalizedPromptGenerator;
        }

        $seederClasses = $this->normalizeStringList($filters['seeder_classes'] ?? []);

        if (! in_array($seederClass, $seederClasses, true)) {
            $seederClasses[] = $seederClass;
        }

        $filters['seeder_classes'] = $seederClasses;
        $filters['num_questions'] = (int) ($filters['num_questions'] ?? count($orderedQuestionUuids));

        $savedTest = SavedGrammarTest::query()
            ->where('uuid', $uuid)
            ->first();

        $slugOwner = SavedGrammarTest::query()
            ->where('slug', $slug)
            ->first();

        if ($savedTest) {
            if ($slugOwner && (int) $slugOwner->getKey() !== (int) $savedTest->getKey()) {
                throw new RuntimeException(sprintf(
                    'saved_test.slug [%s] is already used by uuid [%s]; each seeder must use a unique saved_test.slug.',
                    $slug,
                    (string) ($slugOwner->uuid ?? '')
                ));
            }
        } elseif ($slugOwner) {
            if ((string) ($slugOwner->uuid ?? '') !== $uuid) {
                throw new RuntimeException(sprintf(
                    'saved_test.slug [%s] is already used by uuid [%s]; each seeder must use a unique saved_test.slug.',
                    $slug,
                    (string) ($slugOwner->uuid ?? '')
                ));
            }

            $savedTest = $slugOwner;
        }

        if (! $savedTest) {
            $savedTest = new SavedGrammarTest;
        }

        $savedTest->fill([
            'uuid' => $uuid,
            'slug' => $slug,
            'name' => $name,
            'description' => $this->nullableString($payload['description'] ?? null),
            'filters' => $filters,
        ]);
        $savedTest->save();

        $existingCount = Question::query()
            ->whereIn('uuid', $orderedQuestionUuids)
            ->count();

        if ($existingCount !== count($orderedQuestionUuids)) {
            throw new RuntimeException('saved_test.question_uuids references questions that were not seeded.');
        }

        $savedTest->questionLinks()->delete();

        $linkPayloads = [];
        foreach ($orderedQuestionUuids as $position => $questionUuid) {
            $linkPayloads[] = [
                'question_uuid' => $questionUuid,
                'position' => $position + 1,
            ];
        }

        if ($linkPayloads !== []) {
            $savedTest->questionLinks()->createMany($linkPayloads);
        }

        return $savedTest;
    }

    protected function normalizeSavedTestQuestionUuids(mixed $payload, array $fallback): array
    {
        if (! is_array($payload) || $payload === []) {
            return $fallback;
        }

        $normalized = [];

        foreach ($payload as $questionUuid) {
            $uuid = $this->persistentQuestionUuid((string) $questionUuid);

            if ($uuid === '' || in_array($uuid, $normalized, true)) {
                continue;
            }

            $normalized[] = $uuid;
        }

        return $normalized === [] ? $fallback : $normalized;
    }

    protected function persistentQuestionUuid(string $uuid): string
    {
        return app(QuestionUuidResolver::class)->toPersistent($uuid);
    }

    protected function explanationLanguagesForLocale(string $locale): array
    {
        $normalized = $this->normalizeLocale($locale);

        if ($normalized === 'uk') {
            return ['ua', 'uk'];
        }

        return [$normalized];
    }

    protected function normalizeLocale(string $locale): string
    {
        $normalized = strtolower(trim($locale));

        if ($normalized === '' || $normalized === 'ua') {
            return 'uk';
        }

        return $normalized;
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function selectLocalizedText(mixed $value, string $locale): ?string
    {
        if (is_string($value)) {
            $clean = trim($value);

            return $clean !== '' ? $clean : null;
        }

        if (! is_array($value) || $value === []) {
            return null;
        }

        $normalizedLocale = $this->normalizeLocale($locale);
        $candidates = array_filter([
            $normalizedLocale,
            $normalizedLocale === 'uk' ? 'ua' : null,
            'default',
        ]);

        foreach ($candidates as $candidate) {
            if (! array_key_exists($candidate, $value)) {
                continue;
            }

            $resolved = $this->selectLocalizedText($value[$candidate], $locale);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        foreach ($value as $candidate) {
            $resolved = $this->selectLocalizedText($candidate, $locale);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }

    protected function normalizeOptionList(mixed $options): array
    {
        if (! is_array($options)) {
            return [];
        }

        $normalized = [];

        foreach ($options as $option) {
            $value = trim((string) $option);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    protected function normalizeStringList(mixed $values): array
    {
        if (is_string($values)) {
            $clean = trim($values);

            return $clean !== '' ? [$clean] : [];
        }

        if (! is_array($values)) {
            return [];
        }

        $normalized = [];

        foreach ($values as $value) {
            if (is_array($value)) {
                foreach ($this->normalizeStringList($value) as $nestedValue) {
                    if (! in_array($nestedValue, $normalized, true)) {
                        $normalized[] = $nestedValue;
                    }
                }

                continue;
            }

            $clean = trim((string) $value);

            if ($clean === '' || in_array($clean, $normalized, true)) {
                continue;
            }

            $normalized[] = $clean;
        }

        return $normalized;
    }

    protected function flattenOptions(array $optionsByMarker): array
    {
        $flat = [];

        foreach ($optionsByMarker as $markerOptions) {
            foreach ($markerOptions as $option) {
                $value = trim((string) $option);

                if ($value === '' || in_array($value, $flat, true)) {
                    continue;
                }

                $flat[] = $value;
            }
        }

        return $flat;
    }
}
