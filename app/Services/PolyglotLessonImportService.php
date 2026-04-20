<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\Source;
use App\Support\Database\QuestionUuidResolver;
use App\Support\PolyglotLessonSchemaValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class PolyglotLessonImportService
{
    public function __construct(
        protected PolyglotLessonSchemaValidator $validator,
        protected QuestionSeedingService $questionSeedingService,
        protected QuestionMetaSyncService $questionMetaSyncService,
        protected QuestionUuidResolver $questionUuidResolver,
    ) {}

    public function preview(array|string $input): array
    {
        $prepared = $this->prepare($input);

        return $prepared['summary'];
    }

    public function previewFromFile(string $path): array
    {
        return $this->preview($path);
    }

    public function import(array|string $input, bool $force = false, ?string $ownerClass = null): array
    {
        $prepared = $this->prepare($input);
        $ownerClass ??= static::class;

        DB::transaction(function () use ($prepared, $force, $ownerClass): void {
            $normalized = $prepared['normalized'];
            $test = $normalized['test'];
            $category = Category::firstOrCreate([
                'name' => $this->deriveCategoryName($test['course_slug']),
            ]);

            $source = Source::firstOrCreate([
                'name' => $this->deriveSourceName($test['topic'], $test['level']),
            ]);

            $items = $this->buildQuestionItems($normalized, $category->id, $source->id, $ownerClass);
            $meta = $this->buildMetaPayloads($normalized);

            $this->questionSeedingService->seed($items, false);
            $this->questionMetaSyncService->sync($meta, $ownerClass, $test['interface_locale']);
            $this->syncSavedGrammarTest($normalized, $force);
        });

        return $prepared['summary'];
    }

    public function importFromFile(string $path, bool $force = false, ?string $ownerClass = null): array
    {
        return $this->import($path, $force, $ownerClass);
    }

    private function prepare(array|string $input): array
    {
        [$payload, $resolvedPath] = $this->resolvePayload($input);
        $normalized = $this->validator->validate($payload);
        $uuids = array_column($normalized['items'], 'uuid');

        return [
            'normalized' => $normalized,
            'resolved_path' => $resolvedPath,
            'summary' => [
                'slug' => $normalized['test']['slug'],
                'lesson_order' => $normalized['test']['lesson_order'],
                'items_count' => count($normalized['items']),
                'question_uuids' => $uuids,
                'first_uuids' => array_slice($uuids, 0, 3),
                'warnings' => $normalized['warnings'],
                'resolved_path' => $resolvedPath,
            ],
        ];
    }

    private function resolvePayload(array|string $input): array
    {
        if (is_array($input)) {
            return [$input, null];
        }

        $path = $this->resolvePath($input);
        $decoded = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException('Polyglot lesson JSON must decode into an object.');
        }

        return [$decoded, $path];
    }

    private function resolvePath(string $path): string
    {
        $trimmed = trim($path);

        if ($trimmed === '') {
            throw new RuntimeException('Lesson path cannot be empty.');
        }

        $candidates = [$trimmed];

        if (! File::exists($trimmed)) {
            $candidates[] = base_path($trimmed);
        }

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return realpath($candidate) ?: $candidate;
            }
        }

        throw new RuntimeException("Polyglot lesson JSON file not found: {$path}");
    }

    private function buildQuestionItems(array $normalized, int $categoryId, int $sourceId, string $ownerClass): array
    {
        return array_map(function (array $item) use ($normalized, $categoryId, $sourceId, $ownerClass) {
            $tokenPool = $this->dedupeTokens(array_merge($item['tokens_correct'], $item['distractors']));
            $persistentUuid = $this->questionUuidResolver->toPersistent($item['uuid']);

            return [
                'uuid' => $persistentUuid,
                'question' => $item['source_text_uk'],
                'category_id' => $categoryId,
                'difficulty' => 1,
                'source_id' => $sourceId,
                'flag' => 0,
                'level' => $normalized['test']['level'],
                'type' => Question::TYPE_COMPOSE_TOKENS,
                'answers' => $this->buildAnswerRows($item['tokens_correct']),
                'options' => $tokenPool,
                'variants' => [],
                'seeder' => $ownerClass,
            ];
        }, $normalized['items']);
    }

    private function buildMetaPayloads(array $normalized): array
    {
        $topicHint = $this->buildTopicHint($normalized['test']['topic']);

        return array_map(function (array $item) use ($topicHint) {
            $tokenPool = $this->dedupeTokens(array_merge($item['tokens_correct'], $item['distractors']));

            return [
                'uuid' => $this->questionUuidResolver->toPersistent($item['uuid']),
                'answers' => $this->buildAnswersMap($item['tokens_correct']),
                'option_markers' => $this->buildOptionMarkerMap($item['tokens_correct'], $tokenPool),
                'hints' => array_values(array_filter([
                    $item['hint_uk'],
                    $topicHint,
                ])),
                'explanations' => $item['distractor_explanations_uk'],
            ];
        }, $normalized['items']);
    }

    private function syncSavedGrammarTest(array $normalized, bool $force): SavedGrammarTest
    {
        $testPayload = $normalized['test'];
        $savedTest = SavedGrammarTest::query()
            ->where('slug', $testPayload['slug'])
            ->first();

        if ($savedTest && ! $force && ! $this->canSafelyOverwrite($savedTest, $testPayload)) {
            throw new RuntimeException(sprintf(
                'Saved grammar test [%s] already exists with incompatible filters. Use --force to overwrite it.',
                $testPayload['slug']
            ));
        }

        if (! $savedTest) {
            $savedTest = new SavedGrammarTest;
            $savedTest->uuid = (string) Str::uuid();
        }

        $savedTest->fill([
            'name' => $testPayload['name'],
            'slug' => $testPayload['slug'],
            'description' => $testPayload['description_uk'],
            'filters' => $this->buildSavedTestFilters($testPayload),
        ]);
        $savedTest->save();

        $questionUuids = array_map(
            fn (string $uuid) => $this->questionUuidResolver->toPersistent($uuid),
            array_column($normalized['items'], 'uuid')
        );
        $existingCount = Question::query()
            ->whereIn('uuid', $questionUuids)
            ->count();

        if ($existingCount !== count($questionUuids)) {
            throw new RuntimeException('Saved test question links reference questions that were not imported.');
        }

        $savedTest->questionLinks()->delete();

        foreach (array_values($questionUuids) as $position => $uuid) {
            $savedTest->questionLinks()->create([
                'question_uuid' => $uuid,
                'position' => $position,
            ]);
        }

        return $savedTest;
    }

    private function canSafelyOverwrite(SavedGrammarTest $savedTest, array $testPayload): bool
    {
        $filters = is_array($savedTest->filters) ? $savedTest->filters : [];

        return ($filters['mode'] ?? null) === 'compose_tokens'
            && (string) ($filters['question_type'] ?? '') === Question::TYPE_COMPOSE_TOKENS
            && ($filters['course_slug'] ?? null) === $testPayload['course_slug']
            && (int) ($filters['lesson_order'] ?? 0) === (int) $testPayload['lesson_order'];
    }

    private function buildSavedTestFilters(array $testPayload): array
    {
        return [
            'mode' => 'compose_tokens',
            'question_type' => (int) Question::TYPE_COMPOSE_TOKENS,
            'lesson_type' => 'polyglot',
            'payload_version' => 2,
            'import_format' => 'polyglot_lesson_json',
            'supports_duplicate_tokens' => true,
            'course_slug' => $testPayload['course_slug'],
            'lesson_order' => $testPayload['lesson_order'],
            'previous_lesson_slug' => $testPayload['previous_lesson_slug'],
            'next_lesson_slug' => $testPayload['next_lesson_slug'],
            'completion' => $testPayload['completion'],
            'interface_locale' => $testPayload['interface_locale'],
            'study_locale' => $testPayload['study_locale'],
            'target_locale' => $testPayload['target_locale'],
            'topic' => $testPayload['topic'],
            'level' => $testPayload['level'],
        ];
    }

    private function buildAnswerRows(array $tokens): array
    {
        return array_map(
            fn (string $token, int $index) => [
                'marker' => 'a'.($index + 1),
                'answer' => $token,
                'verb_hint' => null,
            ],
            array_values($tokens),
            array_keys($tokens)
        );
    }

    private function buildAnswersMap(array $tokens): array
    {
        $map = [];

        foreach (array_values($tokens) as $index => $token) {
            $map['a'.($index + 1)] = $token;
        }

        return $map;
    }

    private function buildOptionMarkerMap(array $tokensCorrect, array $tokenPool): array
    {
        $map = [];

        foreach (array_values($tokensCorrect) as $index => $token) {
            if (! array_key_exists($token, $map)) {
                $map[$token] = 'a'.($index + 1);
            }
        }

        foreach ($tokenPool as $token) {
            if (! array_key_exists($token, $map)) {
                $map[$token] = 'a1';
            }
        }

        return $map;
    }

    private function dedupeTokens(array $tokens): array
    {
        $unique = [];

        foreach ($tokens as $token) {
            $value = trim((string) $token);

            if ($value === '' || in_array($value, $unique, true)) {
                continue;
            }

            $unique[] = $value;
        }

        return $unique;
    }

    private function deriveCategoryName(string $courseSlug): string
    {
        return Str::of($courseSlug)
            ->replace('-', ' ')
            ->headline()
            ->toString();
    }

    private function deriveSourceName(string $topic, string $level): string
    {
        $topicLabel = Str::of($topic)
            ->replace('/', ' ')
            ->replace('-', ' ')
            ->replace('_', ' ')
            ->squish()
            ->headline()
            ->toString();

        return trim(sprintf('Custom: Polyglot %s Lesson %s', $topicLabel, strtoupper($level)));
    }

    private function buildTopicHint(string $topic): ?string
    {
        $normalized = strtolower(trim($topic));

        return match ($normalized) {
            'to be' => 'Тема: дієслово to be у Present Simple',
            default => trim(sprintf('Тема: %s у Present Simple', $topic)),
        };
    }
}
