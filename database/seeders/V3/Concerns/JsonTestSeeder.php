<?php

namespace Database\Seeders\V3\Concerns;

use App\Models\Category;
use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Models\SavedGrammarTest;
use App\Models\Source;
use App\Models\Tag;
use Database\Seeders\QuestionSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

abstract class JsonTestSeeder extends QuestionSeeder
{
    protected array $categoryIdCache = [];

    protected array $sourceIdCache = [];

    protected array $tagIdCache = [];

    final public function run(): void
    {
        $definition = $this->loadDefinitionFromFile($this->definitionPath());
        $this->assertDefinition($definition);

        DB::transaction(function () use ($definition): void {
            $normalized = $this->normalizeDefinition($definition);

            $this->seedQuestionData($normalized['items'], []);
            $this->persistMetaPayloads(
                $normalized['meta'],
                $normalized['hint_locale'],
                $normalized['explanation_languages']
            );
            $this->syncSavedTest($normalized['saved_test']);
        });
    }

    abstract protected function definitionPath(): string;

    protected function expectedFormat(): string
    {
        return 'gramlyze.test_seeder.v3';
    }

    protected function loadDefinitionFromFile(string $path): array
    {
        if (! File::exists($path)) {
            throw new RuntimeException("V3 test JSON not found: {$path}");
        }

        $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException("Invalid V3 test JSON: {$path}");
        }

        return $decoded;
    }

    protected function assertDefinition(array $definition): void
    {
        foreach (['format', 'version', 'generated_at', 'generated_from', 'test', 'saved_test', 'taxonomy', 'exercises', 'items', 'meta'] as $key) {
            if (! array_key_exists($key, $definition)) {
                throw new RuntimeException("V3 test JSON is missing required key [{$key}].");
            }
        }

        if (($definition['format'] ?? null) !== $this->expectedFormat()) {
            throw new RuntimeException(sprintf(
                'Unsupported V3 test format [%s], expected [%s].',
                (string) ($definition['format'] ?? ''),
                $this->expectedFormat()
            ));
        }

        if (! is_array($definition['test'] ?? null)) {
            throw new RuntimeException('V3 test JSON key [test] must be an object.');
        }

        if (! is_array($definition['saved_test'] ?? null)) {
            throw new RuntimeException('V3 test JSON key [saved_test] must be an object.');
        }

        if (! is_array($definition['taxonomy'] ?? null)) {
            throw new RuntimeException('V3 test JSON key [taxonomy] must be an object.');
        }

        if (! is_array($definition['items'] ?? null) || ($definition['items'] ?? []) === []) {
            throw new RuntimeException('V3 test JSON must contain a non-empty [items] array.');
        }

        if (! is_array($definition['meta'] ?? null)) {
            throw new RuntimeException('V3 test JSON key [meta] must be an array.');
        }

        if (trim((string) Arr::get($definition, 'taxonomy.category.name', '')) === '') {
            throw new RuntimeException('V3 test JSON must define taxonomy.category.name.');
        }

        if (trim((string) Arr::get($definition, 'taxonomy.source.name', '')) === '') {
            throw new RuntimeException('V3 test JSON must define taxonomy.source.name.');
        }

        if (trim((string) Arr::get($definition, 'saved_test.uuid', '')) === '') {
            throw new RuntimeException('V3 test JSON must define saved_test.uuid.');
        }

        if (trim((string) Arr::get($definition, 'saved_test.slug', '')) === '') {
            throw new RuntimeException('V3 test JSON must define saved_test.slug.');
        }

        $declaredQuestionCount = Arr::get($definition, 'test.question_count');
        if ($declaredQuestionCount !== null && (int) $declaredQuestionCount !== count($definition['items'])) {
            throw new RuntimeException('test.question_count does not match items count.');
        }

        $declaredExerciseCount = Arr::get($definition, 'test.exercise_count');
        if ($declaredExerciseCount !== null && is_array($definition['exercises']) && (int) $declaredExerciseCount !== count($definition['exercises'])) {
            throw new RuntimeException('test.exercise_count does not match exercises count.');
        }
    }

    protected function normalizeDefinition(array $definition): array
    {
        $categoryId = $this->resolveCategoryIdByName((string) Arr::get($definition, 'taxonomy.category.name'));
        $sourceId = $this->resolveSourceIdByName((string) Arr::get($definition, 'taxonomy.source.name'));

        $tagIdsByName = $this->ensureTagIds($this->collectTagNames($definition));
        $metaByUuid = $this->indexMetaPayloads($definition['meta']);

        $items = [];
        $metaPayloads = [];
        $questionUuids = [];

        foreach ($definition['items'] as $index => $itemPayload) {
            if (! is_array($itemPayload)) {
                throw new RuntimeException("Item at index {$index} must be an object.");
            }

            $uuid = trim((string) ($itemPayload['uuid'] ?? ''));
            $question = trim((string) ($itemPayload['question'] ?? ''));

            if ($uuid === '') {
                throw new RuntimeException("Item at index {$index} must define uuid.");
            }

            if ($question === '') {
                throw new RuntimeException("Item [{$uuid}] must define question.");
            }

            if (in_array($uuid, $questionUuids, true)) {
                throw new RuntimeException("Duplicate item uuid detected: {$uuid}");
            }

            $questionUuids[] = $uuid;

            $answers = $this->normalizeAnswers(
                $itemPayload['answers'] ?? [],
                Arr::get($metaByUuid, "{$uuid}.answers", [])
            );

            if ($answers === []) {
                throw new RuntimeException("Item [{$uuid}] must define at least one answer.");
            }

            $optionsByMarker = $this->normalizeOptionsByMarker($itemPayload['options_by_marker'] ?? []);
            $options = $this->normalizeOptionList($itemPayload['options'] ?? []);

            if ($options === []) {
                $options = $this->flattenOptions($optionsByMarker);
            }

            foreach ($answers as $answer) {
                if (! in_array($answer['answer'], $options, true) && $options !== []) {
                    $options[] = $answer['answer'];
                }
            }

            $items[] = [
                'uuid' => $uuid,
                'question' => $question,
                'difficulty' => (int) ($itemPayload['difficulty'] ?? 3),
                'level' => $this->nullableString($itemPayload['level'] ?? null),
                'type' => $this->nullableString($itemPayload['type'] ?? null),
                'flag' => (int) ($itemPayload['flag'] ?? 0),
                'category_id' => $this->resolveCategoryIdForItem($itemPayload, $categoryId),
                'source_id' => $this->resolveSourceIdForItem($itemPayload, $sourceId),
                'answers' => $answers,
                'options_by_marker' => $optionsByMarker,
                'options' => $options,
                'tag_ids' => $this->resolveTagIdsFromNames($itemPayload['tag_names'] ?? [], $tagIdsByName),
                'variants' => $this->normalizeStringList($itemPayload['variants'] ?? []),
                'seeder' => static::class,
            ];

            $metaPayloads[] = $this->normalizeMetaPayload(
                $uuid,
                $metaByUuid[$uuid] ?? [],
                $answers
            );
        }

        $savedTest = $this->normalizeSavedTest($definition['saved_test'], $questionUuids);

        return [
            'items' => $items,
            'meta' => $metaPayloads,
            'saved_test' => $savedTest,
            'hint_locale' => $this->normalizeLocale((string) Arr::get($definition, 'test.locale', 'en')),
            'explanation_languages' => $this->resolveExplanationLanguages(
                (string) Arr::get($definition, 'test.language', Arr::get($definition, 'test.locale', 'en'))
            ),
        ];
    }

    protected function indexMetaPayloads(array $metaPayloads): array
    {
        $indexed = [];

        foreach ($metaPayloads as $index => $metaPayload) {
            if (! is_array($metaPayload)) {
                throw new RuntimeException("Meta payload at index {$index} must be an object.");
            }

            $uuid = trim((string) ($metaPayload['uuid'] ?? ''));

            if ($uuid === '') {
                throw new RuntimeException("Meta payload at index {$index} must define uuid.");
            }

            if (array_key_exists($uuid, $indexed)) {
                throw new RuntimeException("Duplicate meta uuid detected: {$uuid}");
            }

            $indexed[$uuid] = $metaPayload;
        }

        return $indexed;
    }

    protected function normalizeSavedTest(array $payload, array $questionUuids): array
    {
        $uuid = trim((string) ($payload['uuid'] ?? ''));
        $slug = trim((string) ($payload['slug'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));

        if ($uuid === '' || $slug === '' || $name === '') {
            throw new RuntimeException('saved_test must define uuid, slug and name.');
        }

        $orderedQuestionUuids = $this->normalizeSavedTestQuestionUuids(
            $payload['question_uuids'] ?? [],
            $questionUuids
        );

        $missingQuestionUuids = array_values(array_diff($questionUuids, $orderedQuestionUuids));
        $unknownQuestionUuids = array_values(array_diff($orderedQuestionUuids, $questionUuids));

        if ($missingQuestionUuids !== [] || $unknownQuestionUuids !== []) {
            throw new RuntimeException('saved_test.question_uuids must contain the same UUID set as items.');
        }

        return [
            'uuid' => $uuid,
            'slug' => $slug,
            'name' => $name,
            'description' => $this->nullableString($payload['description'] ?? null),
            'filters' => is_array($payload['filters'] ?? null) ? $payload['filters'] : [],
            'question_uuids' => $orderedQuestionUuids,
        ];
    }

    protected function normalizeSavedTestQuestionUuids(mixed $payload, array $fallback): array
    {
        if (! is_array($payload) || $payload === []) {
            return $fallback;
        }

        $normalized = [];

        foreach ($payload as $questionUuid) {
            $uuid = trim((string) $questionUuid);

            if ($uuid === '' || in_array($uuid, $normalized, true)) {
                continue;
            }

            $normalized[] = $uuid;
        }

        return $normalized === [] ? $fallback : $normalized;
    }

    protected function syncSavedTest(array $payload): SavedGrammarTest
    {
        $savedTest = SavedGrammarTest::query()
            ->where('uuid', $payload['uuid'])
            ->first();

        $slugOwner = SavedGrammarTest::query()
            ->where('slug', $payload['slug'])
            ->first();

        if ($savedTest) {
            if ($slugOwner && (int) $slugOwner->getKey() !== (int) $savedTest->getKey()) {
                throw new RuntimeException(sprintf(
                    'saved_test.slug [%s] is already used by uuid [%s]; each seeder must use a unique saved_test.slug.',
                    $payload['slug'],
                    (string) ($slugOwner->uuid ?? '')
                ));
            }
        } elseif ($slugOwner) {
            if ((string) ($slugOwner->uuid ?? '') !== (string) $payload['uuid']) {
                throw new RuntimeException(sprintf(
                    'saved_test.slug [%s] is already used by uuid [%s]; each seeder must use a unique saved_test.slug.',
                    $payload['slug'],
                    (string) ($slugOwner->uuid ?? '')
                ));
            }

            $savedTest = $slugOwner;
        }

        if (! $savedTest) {
            $savedTest = new SavedGrammarTest;
        }

        $savedTest->fill([
            'uuid' => $payload['uuid'],
            'slug' => $payload['slug'],
            'name' => $payload['name'],
            'description' => $payload['description'],
            'filters' => $payload['filters'],
        ]);
        $savedTest->save();

        $questionUuids = $payload['question_uuids'];
        $existingCount = Question::query()
            ->whereIn('uuid', $questionUuids)
            ->count();

        if ($existingCount !== count($questionUuids)) {
            throw new RuntimeException('saved_test.question_uuids references questions that were not seeded.');
        }

        $savedTest->questionLinks()->delete();

        $linkPayloads = [];
        foreach ($questionUuids as $position => $questionUuid) {
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

    protected function persistMetaPayloads(array $metaPayloads, string $hintLocale, array $explanationLanguages): void
    {
        if ($metaPayloads === []) {
            return;
        }

        $questionsByUuid = Question::query()
            ->whereIn('uuid', array_column($metaPayloads, 'uuid'))
            ->get()
            ->keyBy('uuid');

        foreach ($metaPayloads as $payload) {
            /** @var Question|null $question */
            $question = $questionsByUuid->get($payload['uuid']);

            if (! $question) {
                throw new RuntimeException("Seeded question [{$payload['uuid']}] was not found.");
            }

            if (Schema::hasColumn('questions', 'seeder') && empty($question->seeder)) {
                $question->forceFill(['seeder' => static::class])->save();
            }

            $hintAttributes = [
                'question_id' => $question->id,
                'provider' => 'chatgpt',
                'locale' => $hintLocale,
            ];
            $hintText = $this->formatHints($payload['hints']);

            if ($hintText === null) {
                QuestionHint::query()->where($hintAttributes)->delete();
            } else {
                QuestionHint::updateOrCreate($hintAttributes, ['hint' => $hintText]);
            }

            if (Schema::hasTable('question_marker_tag')) {
                $this->syncMarkerTags($question->id, $payload['gap_tags']);
            }

            foreach ($this->normalizeExplanationPayloads(
                $payload['explanations'],
                $payload['option_markers'],
                $payload['answers']
            ) as $explanation) {
                foreach ($explanationLanguages as $language) {
                    ChatGPTExplanation::updateOrCreate(
                        [
                            'question' => $question->question,
                            'wrong_answer' => $explanation['wrong_answer'],
                            'correct_answer' => $explanation['correct_answer'],
                            'language' => $language,
                        ],
                        ['explanation' => $explanation['explanation']]
                    );
                }
            }
        }
    }

    protected function normalizeMetaPayload(string $uuid, array $payload, array $fallbackAnswers): array
    {
        return [
            'uuid' => $uuid,
            'answers' => $this->normalizeAnswerMap($payload['answers'] ?? [], $fallbackAnswers),
            'hints' => $this->normalizeHints($payload['hints'] ?? []),
            'gap_tags' => $this->normalizeGapTags($payload['gap_tags'] ?? []),
            'explanations' => is_array($payload['explanations'] ?? null) ? $payload['explanations'] : [],
            'option_markers' => $this->normalizeOptionMarkerMap($payload['option_markers'] ?? []),
        ];
    }

    protected function normalizeAnswers(mixed $payload, array $fallbackMap = []): array
    {
        $normalized = [];

        if (is_array($payload)) {
            foreach ($payload as $marker => $answerPayload) {
                $resolved = $this->normalizeSingleAnswer($marker, $answerPayload);

                if ($resolved === null) {
                    continue;
                }

                $normalized[$resolved['marker']] = $resolved;
            }
        }

        if ($normalized === [] && $fallbackMap !== []) {
            foreach ($fallbackMap as $marker => $answer) {
                $marker = trim((string) $marker);
                $answer = trim((string) $answer);

                if ($marker === '' || $answer === '') {
                    continue;
                }

                $normalized[$marker] = [
                    'marker' => $marker,
                    'answer' => $answer,
                    'verb_hint' => null,
                ];
            }
        }

        ksort($normalized, SORT_NATURAL);

        return array_values($normalized);
    }

    protected function normalizeSingleAnswer(mixed $key, mixed $payload): ?array
    {
        if (is_array($payload)) {
            $marker = trim((string) ($payload['marker'] ?? $key));
            $answer = trim((string) ($payload['answer'] ?? ''));
            $verbHint = $this->nullableString($payload['verb_hint'] ?? null);
        } else {
            $marker = trim((string) $key);
            $answer = trim((string) $payload);
            $verbHint = null;
        }

        if ($marker === '' || $answer === '') {
            return null;
        }

        return [
            'marker' => $marker,
            'answer' => $answer,
            'verb_hint' => $verbHint,
        ];
    }

    protected function normalizeAnswerMap(mixed $payload, array $fallbackAnswers): array
    {
        $normalized = [];

        if (is_array($payload)) {
            foreach ($payload as $marker => $answer) {
                $marker = trim((string) $marker);
                $answer = trim((string) $answer);

                if ($marker === '' || $answer === '') {
                    continue;
                }

                $normalized[$marker] = $answer;
            }
        }

        if ($normalized !== []) {
            ksort($normalized, SORT_NATURAL);

            return $normalized;
        }

        foreach ($fallbackAnswers as $answer) {
            $marker = trim((string) ($answer['marker'] ?? ''));
            $value = trim((string) ($answer['answer'] ?? ''));

            if ($marker === '' || $value === '') {
                continue;
            }

            $normalized[$marker] = $value;
        }

        ksort($normalized, SORT_NATURAL);

        return $normalized;
    }

    protected function normalizeOptionsByMarker(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $normalized = [];

        foreach ($payload as $marker => $options) {
            $marker = trim((string) $marker);

            if ($marker === '') {
                continue;
            }

            $normalized[$marker] = $this->normalizeOptionList($options);
        }

        ksort($normalized, SORT_NATURAL);

        return $normalized;
    }

    protected function normalizeOptionMarkerMap(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $normalized = [];

        foreach ($payload as $option => $marker) {
            $option = trim((string) $option);
            $marker = trim((string) $marker);

            if ($option === '' || $marker === '') {
                continue;
            }

            $normalized[$option] = $marker;
        }

        return $normalized;
    }

    protected function normalizeGapTags(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $normalized = [];

        foreach ($payload as $marker => $tagNames) {
            $marker = trim((string) $marker);

            if ($marker === '') {
                continue;
            }

            $normalized[$marker] = $this->normalizeStringList($tagNames);
        }

        ksort($normalized, SORT_NATURAL);

        return $normalized;
    }

    protected function normalizeExplanationPayloads(array $payload, array $optionMarkers, array $answers): array
    {
        if ($payload === []) {
            return [];
        }

        $normalized = [];
        $fallbackMarker = array_key_first($answers);

        $isNested = false;
        foreach ($payload as $value) {
            if (is_array($value)) {
                $isNested = true;
                break;
            }
        }

        if (! $isNested) {
            foreach ($payload as $wrongAnswer => $text) {
                $wrongAnswer = trim((string) $wrongAnswer);
                $text = trim((string) $text);

                if ($wrongAnswer === '' || $text === '') {
                    continue;
                }

                $marker = $optionMarkers[$wrongAnswer] ?? $fallbackMarker;
                $correctAnswer = $marker !== null ? ($answers[$marker] ?? null) : null;

                if (! is_string($correctAnswer) || $correctAnswer === '') {
                    continue;
                }

                $normalized[] = [
                    'wrong_answer' => $wrongAnswer,
                    'correct_answer' => $correctAnswer,
                    'explanation' => $text,
                ];
            }

            return $normalized;
        }

        foreach ($payload as $marker => $markerPayload) {
            $marker = trim((string) $marker);

            if ($marker === '' || ! is_array($markerPayload)) {
                continue;
            }

            $correctAnswer = $answers[$marker] ?? null;

            if (! is_string($correctAnswer) || $correctAnswer === '') {
                continue;
            }

            foreach ($markerPayload as $wrongAnswer => $text) {
                $wrongAnswer = trim((string) $wrongAnswer);
                $text = trim((string) $text);

                if ($wrongAnswer === '' || $text === '') {
                    continue;
                }

                $normalized[] = [
                    'wrong_answer' => $wrongAnswer,
                    'correct_answer' => $correctAnswer,
                    'explanation' => $text,
                ];
            }
        }

        return $normalized;
    }

    protected function normalizeHints(mixed $payload): array
    {
        $normalized = [];
        $this->appendHintFragments($normalized, $payload);

        return $normalized;
    }

    protected function appendHintFragments(array &$normalized, mixed $payload): void
    {
        if (is_string($payload)) {
            $clean = trim($payload);

            if ($clean !== '') {
                $normalized[] = $clean;
            }

            return;
        }

        if (! is_array($payload)) {
            return;
        }

        foreach ($payload as $value) {
            $this->appendHintFragments($normalized, $value);
        }
    }

    protected function collectTagNames(array $definition): array
    {
        $tagNames = [];

        $sources = [
            Arr::get($definition, 'taxonomy.tag_names', []),
            Arr::get($definition, 'test.tags', []),
        ];

        foreach ($definition['items'] as $itemPayload) {
            if (is_array($itemPayload)) {
                $sources[] = $itemPayload['tag_names'] ?? [];
            }
        }

        foreach ($definition['meta'] as $metaPayload) {
            if (! is_array($metaPayload) || ! is_array($metaPayload['gap_tags'] ?? null)) {
                continue;
            }

            foreach ($metaPayload['gap_tags'] as $markerTags) {
                $sources[] = $markerTags;
            }
        }

        foreach ($sources as $source) {
            foreach ($this->normalizeStringList($source) as $name) {
                $key = $this->normalizeName($name);

                if ($key === '' || array_key_exists($key, $tagNames)) {
                    continue;
                }

                $tagNames[$key] = $name;
            }
        }

        return $tagNames;
    }

    protected function ensureTagIds(array $tagNamesByKey): array
    {
        $resolved = [];

        foreach ($tagNamesByKey as $key => $name) {
            $resolved[$key] = $this->resolveTagIdByName($name);
        }

        return $resolved;
    }

    protected function resolveTagIdsFromNames(mixed $payload, array $tagIdsByKey): array
    {
        $resolved = [];

        foreach ($this->normalizeStringList($payload) as $name) {
            $key = $this->normalizeName($name);

            if ($key === '') {
                continue;
            }

            if (! array_key_exists($key, $tagIdsByKey)) {
                $tagIdsByKey[$key] = $this->resolveTagIdByName($name);
            }

            $resolved[] = $tagIdsByKey[$key];
        }

        return array_values(array_unique($resolved));
    }

    protected function resolveCategoryIdForItem(array $payload, int $fallback): int
    {
        $name = trim((string) ($payload['category_name'] ?? ''));

        return $name !== '' ? $this->resolveCategoryIdByName($name) : $fallback;
    }

    protected function resolveSourceIdForItem(array $payload, ?int $fallback): ?int
    {
        $name = trim((string) ($payload['source_name'] ?? ''));

        return $name !== '' ? $this->resolveSourceIdByName($name) : $fallback;
    }

    protected function resolveCategoryIdByName(string $name): int
    {
        $normalized = $this->normalizeName($name);

        if ($normalized === '') {
            throw new RuntimeException('Category name cannot be empty.');
        }

        if (array_key_exists($normalized, $this->categoryIdCache)) {
            return $this->categoryIdCache[$normalized];
        }

        $canonicalName = preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);

        $category = Category::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->first();

        if (! $category) {
            $category = Category::create(['name' => $canonicalName]);
        } elseif ($category->name !== $canonicalName) {
            $category->forceFill(['name' => $canonicalName])->save();
        }

        return $this->categoryIdCache[$normalized] = (int) $category->id;
    }

    protected function resolveSourceIdByName(string $name): int
    {
        $normalized = $this->normalizeName($name);

        if ($normalized === '') {
            throw new RuntimeException('Source name cannot be empty.');
        }

        if (array_key_exists($normalized, $this->sourceIdCache)) {
            return $this->sourceIdCache[$normalized];
        }

        $canonicalName = preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);

        $source = Source::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->first();

        if (! $source) {
            $source = Source::create(['name' => $canonicalName]);
        } elseif ($source->name !== $canonicalName) {
            $source->forceFill(['name' => $canonicalName])->save();
        }

        return $this->sourceIdCache[$normalized] = (int) $source->id;
    }

    protected function resolveTagIdByName(string $name): int
    {
        $normalized = $this->normalizeName($name);

        if ($normalized === '') {
            throw new RuntimeException('Tag name cannot be empty.');
        }

        if (array_key_exists($normalized, $this->tagIdCache)) {
            return $this->tagIdCache[$normalized];
        }

        $canonicalName = preg_replace('/\s+/u', ' ', trim($name)) ?? trim($name);

        $tag = Tag::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->first();

        if (! $tag) {
            $tag = Tag::create(['name' => $canonicalName]);
        } elseif ($tag->name !== $canonicalName) {
            $tag->forceFill(['name' => $canonicalName])->save();
        }

        return $this->tagIdCache[$normalized] = (int) $tag->id;
    }

    protected function resolveExplanationLanguages(string $language): array
    {
        $normalized = strtolower(trim($language));

        if ($normalized === '' || $normalized === 'ua' || $normalized === 'uk') {
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

    protected function normalizeOptionList(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $normalized = [];

        foreach ($payload as $value) {
            $value = trim((string) $value);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    protected function normalizeStringList(mixed $payload): array
    {
        if (is_string($payload)) {
            $payload = [$payload];
        }

        if (! is_array($payload)) {
            return [];
        }

        $normalized = [];

        foreach ($payload as $value) {
            if (is_array($value)) {
                foreach ($this->normalizeStringList($value) as $nestedValue) {
                    if (! in_array($nestedValue, $normalized, true)) {
                        $normalized[] = $nestedValue;
                    }
                }

                continue;
            }

            $value = trim((string) $value);

            if ($value === '' || in_array($value, $normalized, true)) {
                continue;
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    protected function flattenOptions(array $optionsByMarker): array
    {
        $normalized = [];

        foreach ($optionsByMarker as $options) {
            foreach ($options as $option) {
                if (! in_array($option, $normalized, true)) {
                    $normalized[] = $option;
                }
            }
        }

        return $normalized;
    }

    protected function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function normalizeName(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? '';

        return mb_strtolower($value);
    }
}
