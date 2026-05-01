<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageCategory;
use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Support\ComposeModeEligibility;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TheoryCourseTestPoolService
{
    /** @var array<int, array<string, mixed>> */
    private array $summaryCache = [];

    /** @var array<int, Collection> */
    private array $relatedTestsCache = [];

    public function __construct(
        private TheoryPagePromptLinkedTestsService $linkedTestsService,
        private SavedTestResolver $savedTestResolver,
        private GrammarTestFilterService $filterService,
    ) {}

    public function summaryForPage(Page $page): array
    {
        $pageId = (int) $page->getKey();

        if ($pageId > 0 && isset($this->summaryCache[$pageId])) {
            return $this->summaryCache[$pageId];
        }

        $tests = $this->relatedTestsForPage($page);
        $meta = $tests
            ->map(fn (mixed $test): array => $this->testMeta($test))
            ->values()
            ->all();

        $counts = $this->countsFromTestMeta($meta);
        $summary = [
            'tests' => $meta,
            'counts' => $counts,
            'has_tests' => $counts['tests_total'] > 0 && $counts['questions_total'] > 0,
        ];

        if ($pageId > 0) {
            $this->summaryCache[$pageId] = $summary;
        }

        return $summary;
    }

    public function buildForPage(Page $page, ?string $attemptSeed = null): array
    {
        $tests = $this->relatedTestsForPage($page);
        $testMeta = [];
        $questions = [];
        $seenQuestions = [];
        $standardQuestionCount = 0;
        $composeQuestionCount = 0;

        foreach ($tests as $test) {
            $meta = $this->testMeta($test);
            $loadedQuestions = $this->questionsForTest($test);
            $meta['questions_loaded'] = $loadedQuestions->count();
            $testMeta[] = $meta;

            foreach ($loadedQuestions as $question) {
                if (! $question instanceof Question) {
                    continue;
                }

                $key = $this->questionDeduplicationKey($question);

                if ($key !== null && isset($seenQuestions[$key])) {
                    continue;
                }

                if ($key !== null) {
                    $seenQuestions[$key] = true;
                }

                $payload = $this->questionPayload($question, $meta);

                if ($payload === null) {
                    continue;
                }

                if (($payload['renderer'] ?? null) === 'compose') {
                    $composeQuestionCount++;
                } else {
                    $standardQuestionCount++;
                }

                $questions[] = $payload;
            }
        }

        $questions = $this->deterministicShuffle(
            $questions,
            $attemptSeed ?: $this->lessonSeed($page)
        );

        $counts = $this->countsFromTestMeta($testMeta);
        $counts['questions_total'] = count($questions);
        $counts['compose_questions'] = $composeQuestionCount;
        $counts['standard_questions'] = $standardQuestionCount;

        return [
            'tests' => $testMeta,
            'questions' => $questions,
            'counts' => $counts,
            'has_tests' => count($questions) > 0,
            'completion' => $this->completionRules(count($questions)),
        ];
    }

    private function relatedTestsForPage(Page $page): Collection
    {
        $pageId = (int) $page->getKey();

        if ($pageId > 0 && isset($this->relatedTestsCache[$pageId])) {
            return $this->relatedTestsCache[$pageId];
        }

        $tests = $this->linkedTestsService
            ->findForPage($page)
            ->concat($this->linkedTestsService->buildForPage($page));

        $deduped = $tests
            ->filter()
            ->unique(fn (mixed $test): string => $this->testDeduplicationKey($test))
            ->values();

        if ($pageId > 0) {
            $this->relatedTestsCache[$pageId] = $deduped;
        }

        return $deduped;
    }

    private function questionsForTest(mixed $test): Collection
    {
        $relations = [
            'category',
            'answers.option',
            'options',
            'verbHints.option',
            'hints',
            'chatgptExplanations',
            'source',
            'tags',
        ];

        if ($test instanceof VirtualSavedTest) {
            $filters = $test->filters;
            $filters['randomize_filtered'] = false;
            $questions = $this->filterService->questionsFromFilters($filters);

            return $this->withQuestionRelations($questions, $relations);
        }

        if (! $test instanceof SavedGrammarTest) {
            return collect();
        }

        try {
            $resolved = $this->savedTestResolver->resolve((string) $test->slug);
            $questions = $this->savedTestResolver->loadQuestions($resolved, $relations);

            return $this->withQuestionRelations($questions, $relations);
        } catch (\Throwable) {
            return collect();
        }
    }

    /**
     * @param  array<int, string>  $relations
     */
    private function withQuestionRelations(Collection $questions, array $relations): Collection
    {
        if (method_exists($questions, 'loadMissing')) {
            $questions->loadMissing($relations);

            return $questions->values();
        }

        $questions->each(function (mixed $question) use ($relations): void {
            if ($question instanceof Question) {
                $question->loadMissing($relations);
            }
        });

        return $questions->values();
    }

    private function questionPayload(Question $question, array $testMeta): ?array
    {
        if ((string) $question->type === Question::TYPE_COMPOSE_TOKENS) {
            return $this->composeQuestionPayload($question, $testMeta);
        }

        return $this->standardQuestionPayload($question, $testMeta);
    }

    private function standardQuestionPayload(Question $question, array $testMeta): array
    {
        $answers = $this->sortAnswersByMarker($question->answers)
            ->map(function ($answer): array {
                return [
                    'marker' => $answer->marker,
                    'value' => $answer->option->option ?? $answer->answer ?? '',
                ];
            })
            ->filter(fn (array $answer): bool => trim((string) $answer['value']) !== '')
            ->values();

        $answerMap = $answers
            ->filter(fn (array $answer): bool => filled($answer['marker'] ?? null))
            ->mapWithKeys(fn (array $answer): array => [$answer['marker'] => $answer['value']])
            ->toArray();
        $markers = array_keys($answerMap);
        $answerList = array_map(fn (string $marker): string => (string) ($answerMap[$marker] ?? ''), $markers);

        $options = $question->options->pluck('option')->filter()->values()->all();
        foreach ($answerList as $answer) {
            if ($answer !== '' && ! in_array($answer, $options, true)) {
                $options[] = $answer;
            }
        }

        $verbHints = $question->verbHints
            ->mapWithKeys(fn ($hint) => [$hint->marker => $hint->option->option ?? ''])
            ->toArray();

        return [
            'renderer' => 'standard',
            'id' => $question->id,
            'uuid' => $question->uuid,
            'type' => (string) $question->type,
            'question' => $question->question,
            'answers' => $answerList,
            'answer_map' => $answerMap,
            'markers' => $markers,
            'markers_count' => count($markers),
            'options' => $options,
            'options_by_marker' => $this->normalizeOptionsByMarker($question->options_by_marker, $markers),
            'verb_hints' => $verbHints,
            'level' => $question->level,
            'source_test' => $this->compactTestMeta($testMeta),
        ];
    }

    private function composeQuestionPayload(Question $question, array $testMeta): ?array
    {
        $correctTokens = $this->sortAnswersByMarker($question->answers)
            ->map(fn ($answer) => trim((string) ($answer->option->option ?? $answer->answer ?? '')))
            ->filter(fn (string $value): bool => $value !== '')
            ->values()
            ->all();

        if ($correctTokens === []) {
            return null;
        }

        $punctuation = Str::endsWith(trim((string) $question->question), '?') ? '?' : '.';
        $tokenBank = $this->composeTokenBank($correctTokens, $question->options->pluck('option')->all());
        $correctTokenIds = collect($tokenBank)
            ->where('isCorrect', true)
            ->sortBy('correctIndex')
            ->pluck('id')
            ->values()
            ->all();

        return [
            'renderer' => 'compose',
            'id' => $question->id,
            'uuid' => $question->uuid,
            'type' => (string) $question->type,
            'level' => $question->level,
            'question' => $question->question,
            'sourceTextUk' => $question->question,
            'correctTokens' => $correctTokens,
            'correctTokenValues' => $correctTokens,
            'correctTokenIds' => $correctTokenIds,
            'tokenBank' => $tokenBank,
            'correctText' => $this->composeSentence($correctTokens, $punctuation),
            'hintUk' => $this->composeHintText($question),
            'explanations' => $this->composeExplanationMap($question),
            'punctuation' => $punctuation,
            'source_test' => $this->compactTestMeta($testMeta),
        ];
    }

    private function testMeta(mixed $test): array
    {
        $filters = $this->testFilters($test);
        $isPolyglot = $this->isPolyglotTest($filters);
        $questionCount = $this->questionCountEstimate($test, $filters);

        return [
            'slug' => trim((string) ($test->slug ?? '')),
            'name' => trim((string) ($test->name ?? '')),
            'description' => trim((string) ($test->description ?? '')),
            'is_virtual' => $test instanceof VirtualSavedTest || (method_exists($test, 'isVirtual') && $test->isVirtual()),
            'source_type' => $isPolyglot ? 'polyglot' : 'standard',
            'is_polyglot' => $isPolyglot,
            'question_count' => $questionCount,
            'filters' => $filters,
        ];
    }

    private function compactTestMeta(array $meta): array
    {
        return [
            'slug' => $meta['slug'] ?? '',
            'name' => $meta['name'] ?? '',
            'source_type' => $meta['source_type'] ?? 'standard',
            'is_polyglot' => (bool) ($meta['is_polyglot'] ?? false),
            'is_virtual' => (bool) ($meta['is_virtual'] ?? false),
        ];
    }

    private function countsFromTestMeta(array $meta): array
    {
        $tests = collect($meta);
        $standardTests = $tests->where('is_polyglot', false)->count();
        $polyglotTests = $tests->where('is_polyglot', true)->count();

        return [
            'tests_total' => $tests->count(),
            'standard_tests' => $standardTests,
            'polyglot_tests' => $polyglotTests,
            'questions_total' => $tests->sum(fn (array $item): int => (int) ($item['question_count'] ?? 0)),
            'compose_questions' => 0,
            'standard_questions' => 0,
        ];
    }

    private function isPolyglotTest(array $filters): bool
    {
        $courseSlug = Str::lower(trim((string) ($filters['course_slug'] ?? '')));

        return ComposeModeEligibility::supportsFilters($filters)
            || Str::lower(trim((string) ($filters['lesson_type'] ?? ''))) === 'polyglot'
            || (string) ($filters['question_type'] ?? '') === Question::TYPE_COMPOSE_TOKENS
            || Str::startsWith($courseSlug, 'polyglot-');
    }

    private function questionCountEstimate(mixed $test, array $filters): int
    {
        if ($test instanceof VirtualSavedTest) {
            return max(0, (int) ($test->totalQuestionsAvailable ?: ($filters['num_questions'] ?? 0)));
        }

        $count = (int) ($test->question_links_count ?? 0);

        if ($count > 0) {
            return $count;
        }

        if (isset($test->questionLinks)) {
            $count = $test->questionLinks instanceof Collection
                ? $test->questionLinks->count()
                : 0;
        }

        return $count > 0 ? $count : max(0, (int) ($filters['num_questions'] ?? 0));
    }

    private function testFilters(mixed $test): array
    {
        if ($test instanceof VirtualSavedTest) {
            return $test->filters;
        }

        return ComposeModeEligibility::normalizedFilters($test);
    }

    private function testDeduplicationKey(mixed $test): string
    {
        $slug = trim((string) ($test->slug ?? ''));

        if ($slug !== '') {
            return 'slug:'.Str::lower($slug);
        }

        $filters = $this->testFilters($test);
        $seeders = collect($filters['seeder_classes'] ?? [])
            ->map(fn ($class): string => Str::lower(trim((string) $class)))
            ->filter()
            ->sort()
            ->implode('|');

        return $seeders !== '' ? 'seeders:'.$seeders : spl_object_hash((object) $test);
    }

    private function questionDeduplicationKey(Question $question): ?string
    {
        $uuid = trim((string) $question->uuid);

        if ($uuid !== '') {
            return 'uuid:'.Str::lower($uuid);
        }

        return $question->getKey() ? 'id:'.$question->getKey() : null;
    }

    private function deterministicShuffle(array $items, string $seed): array
    {
        if (count($items) < 2) {
            return $items;
        }

        return collect($items)
            ->sortBy(function (array $item, int $index) use ($seed): string {
                $stableId = trim((string) ($item['uuid'] ?? $item['id'] ?? $index));

                return sha1($seed.'|'.$stableId.'|'.$index);
            })
            ->values()
            ->all();
    }

    private function completionRules(int $questionCount): array
    {
        $minimumCorrect = (int) ceil($questionCount * 0.8);

        return [
            'minimum_rating_percent' => 80,
            'minimum_correct' => $minimumCorrect,
            'minimum_answered' => $questionCount,
            'requires_all_questions' => $questionCount < 5,
        ];
    }

    private function lessonSeed(Page $page): string
    {
        return implode('|', [
            'theory-course',
            $this->categorySlugPath($page),
            $page->slug,
        ]);
    }

    private function normalizeOptionsByMarker(mixed $rawOptions, array $markers): ?array
    {
        if (! is_array($rawOptions)) {
            return null;
        }

        $normalized = [];
        $hasValues = false;

        foreach ($markers as $index => $marker) {
            $options = $rawOptions[$marker] ?? $rawOptions[$index] ?? [];
            $options = is_array($options) ? $this->filterOptionArray($options) : [];

            if ($options !== []) {
                $hasValues = true;
            }

            $normalized[] = $options;
        }

        return $hasValues ? $normalized : null;
    }

    private function filterOptionArray(array $options): array
    {
        $clean = [];

        foreach ($options as $option) {
            $value = trim((string) $option);

            if ($value !== '' && ! in_array($value, $clean, true)) {
                $clean[] = $value;
            }
        }

        return $clean;
    }

    private function sortAnswersByMarker(Collection $answers): Collection
    {
        return $answers
            ->sortBy(fn ($answer): string => $this->markerSortValue($answer->marker))
            ->values();
    }

    private function markerSortValue(?string $marker): string
    {
        $normalized = strtolower(trim((string) $marker));

        if (preg_match('/^([a-z_]+)(\d+)$/', $normalized, $matches) === 1) {
            return sprintf('%s%08d', $matches[1], (int) $matches[2]);
        }

        return $normalized;
    }

    private function composeTokenBank(array $correctTokens, array $optionValues): array
    {
        $instances = [];

        foreach (array_values($correctTokens) as $index => $token) {
            $value = trim((string) $token);

            if ($value === '') {
                continue;
            }

            $instances[] = [
                'id' => 'c'.($index + 1),
                'value' => $value,
                'isCorrect' => true,
                'correctIndex' => $index,
            ];
        }

        $correctLookup = [];
        foreach ($instances as $instance) {
            $correctLookup[$instance['value']] = true;
        }

        $distractorIndex = 1;
        foreach ($this->filterOptionArray($optionValues) as $optionValue) {
            if (isset($correctLookup[$optionValue])) {
                continue;
            }

            $instances[] = [
                'id' => 'd'.$distractorIndex,
                'value' => $optionValue,
                'isCorrect' => false,
                'correctIndex' => null,
            ];
            $distractorIndex++;
        }

        return $instances;
    }

    private function composeSentence(array $tokens, string $punctuation): string
    {
        $sentence = trim(implode(' ', array_map(fn ($token): string => trim((string) $token), $tokens)));

        if ($sentence === '') {
            return '';
        }

        return preg_replace('/\s+([?.!,;:])/u', '$1', $sentence.$punctuation) ?? ($sentence.$punctuation);
    }

    private function composeHintText(Question $question): ?string
    {
        if (! $question->relationLoaded('hints')) {
            return null;
        }

        $hint = $question->hints
            ->sortByDesc(fn ($item): int => ($item->locale === 'uk' ? 1 : 0))
            ->first(fn ($item): bool => filled($item->hint));

        return $hint ? trim((string) $hint->hint) : null;
    }

    private function composeExplanationMap(Question $question): array
    {
        if (! $question->relationLoaded('chatgptExplanations')) {
            return [];
        }

        return $question->chatgptExplanations
            ->filter(function ($item): bool {
                $language = strtolower(trim((string) ($item->language ?? '')));

                return in_array($language, ['', 'ua', 'uk'], true)
                    && filled($item->wrong_answer)
                    && filled($item->explanation);
            })
            ->mapWithKeys(fn ($item): array => [
                trim((string) $item->wrong_answer) => trim((string) $item->explanation),
            ])
            ->toArray();
    }

    private function categorySlugPath(Page $page): string
    {
        $page->loadMissing('category.parent.parent.parent.parent.parent');
        $category = $page->category;
        $segments = [];
        $depth = 0;

        while ($category instanceof PageCategory && $depth < 10) {
            $slug = Str::lower(trim((string) $category->slug));

            if ($slug !== '') {
                array_unshift($segments, $slug);
            }

            $category = $category->parent;
            $depth++;
        }

        return implode('/', $segments);
    }
}
