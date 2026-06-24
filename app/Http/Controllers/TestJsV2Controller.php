<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\TextBlock;
use App\Services\MarkerTheoryMatcherService;
use App\Services\PolyglotCourseManifestService;
use App\Services\PolyglotLessonDebugPayloadBuilder;
use App\Services\QuestionReportFileStore;
use App\Services\QuestionReportIssueCatalog;
use App\Services\QuestionTechnicalInfoService;
use App\Services\QuestionVariantService;
use App\Services\SavedTestResolver;
use App\Support\AdminDebugAccess;
use App\Support\ComposeModeEligibility;
use App\Support\SavedTestJsState;
use App\Support\SentenceBuilderBranding;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestJsV2Controller extends Controller
{
    public function __construct(
        private QuestionVariantService $variantService,
        private SavedTestResolver $savedTestResolver,
        private MarkerTheoryMatcherService $markerTheoryMatcher,
        private QuestionTechnicalInfoService $questionTechnicalInfoService,
        private PolyglotCourseManifestService $polyglotCourseManifestService,
        private PolyglotLessonDebugPayloadBuilder $polyglotLessonDebugPayloadBuilder,
    ) {}

    /**
     * Show the V2 version of the JS test page with new UI design.
     */
    public function showSavedTestJsV2($slug)
    {
        return $this->renderSavedTestJsV2View($slug, 'saved-test-js-v2');
    }

    /**
     * Show the V2 version of the JS step test page with new UI design.
     */
    public function showSavedTestJsStepV2($slug)
    {
        return $this->renderSavedTestJsV2View($slug, 'saved-test-js-step-v2');
    }

    /**
     * Show the V2 version of the JS step input test page with new UI design.
     */
    public function showSavedTestJsStepInputV2($slug)
    {
        return $this->renderSavedTestJsV2View($slug, 'saved-test-js-step-input-v2');
    }

    /**
     * Show the V2 version of the JS step manual test page with new UI design.
     */
    public function showSavedTestJsStepManualV2($slug)
    {
        return $this->renderSavedTestJsV2View($slug, 'saved-test-js-step-manual-v2');
    }

    /**
     * Show the V2 version of the JS step select test page with new UI design.
     */
    public function showSavedTestJsStepSelectV2($slug)
    {
        return $this->renderSavedTestJsV2View($slug, 'saved-test-js-step-select-v2');
    }

    /**
     * Show the V2 version of the JS select test page (card mode) with new UI design.
     */
    public function showSavedTestJsSelectV2($slug)
    {
        return $this->renderSavedTestJsV2View($slug, 'saved-test-js-select-v2');
    }

    /**
     * Show the V2 version of the JS input test page (card mode) with new UI design.
     */
    public function showSavedTestJsInputV2($slug)
    {
        return $this->renderSavedTestJsV2View($slug, 'saved-test-js-input-v2');
    }

    /**
     * Show the V2 version of the JS manual test page (card mode) with new UI design.
     */
    public function showSavedTestJsManualV2($slug)
    {
        return $this->renderSavedTestJsV2View($slug, 'saved-test-js-manual-v2');
    }

    protected function renderSavedTestJsV2View(string $slug, string $mode, ?string $view = null)
    {
        return $this->renderSavedTestShell($slug, $mode, $view ?? "tests.$mode");
    }

    protected function renderSavedTestComposeShell(string $slug, string $mode, string $view, array $extra = [])
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;

        if ($redirect = $this->redirectForUnsupportedMixedPolyglotMode($test, $mode)) {
            return $redirect;
        }

        $isAdmin = $this->isAdminUser();
        $showTechnicalInfo = $this->shouldShowTechnicalInfo($isAdmin);

        abort_unless(ComposeModeEligibility::isAvailableForTest($test), 404);

        $questions = $this->buildComposeQuestionDataset($resolved, $showTechnicalInfo || $isAdmin);
        $courseContext = $this->buildPolyglotCourseContext($test);
        $polyglotAdminDebugPayload = $isAdmin
            ? $this->polyglotLessonDebugPayloadBuilder->build($test, $questions, $courseContext)
            : null;

        abort_if($questions === [], 404);

        return view($view, array_merge([
            'test' => $test,
            'questionData' => $questions,
            'jsStateMode' => $mode,
            'savedState' => null,
            'usesUuidLinks' => $resolved->usesUuidLinks,
            'isAdmin' => $isAdmin,
            'showTechnicalInfo' => $showTechnicalInfo,
            'courseContext' => $courseContext,
            'polyglotAdminDebugPayload' => $polyglotAdminDebugPayload,
        ], $this->questionReportViewData($questions, $isAdmin), $extra));
    }

    protected function renderSavedTestShell(string $slug, string $mode, string $view, array $extra = [])
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;

        if ($redirect = $this->redirectForUnsupportedMixedPolyglotMode($test, $mode)) {
            return $redirect;
        }

        $stateKey = $this->jsStateSessionKey($test, $mode);
        $savedState = $this->activeJsSavedState($stateKey);
        $isAdmin = $this->isAdminUser();
        $showTechnicalInfo = $this->shouldShowTechnicalInfo($isAdmin);
        $questions = $this->persistedQuestionData($test, $savedState);

        if (! is_array($questions) || ($showTechnicalInfo && ! $this->datasetContainsTechnicalInfo($questions, $test))) {
            $questions = $this->buildQuestionDataset($resolved, $savedState === null, $showTechnicalInfo);
        } else {
            $questions = $this->localizePersistedQuestionVerbHints($questions);
            session([$this->jsQuestionDataSessionKey($test) => $questions]);
        }

        return view($view, array_merge([
            'test' => $test,
            'questionData' => $questions,
            'jsStateMode' => $mode,
            'savedState' => $savedState,
            'usesUuidLinks' => $resolved->usesUuidLinks,
            'isAdmin' => $isAdmin,
            'showTechnicalInfo' => $showTechnicalInfo,
        ], $this->questionReportViewData($questions, $isAdmin), $extra));
    }

    protected function questionReportViewData(array $questions, bool $isAdmin): array
    {
        if (! $isAdmin) {
            return [
                'questionReportIssueCatalog' => [],
                'questionReportsByQuestion' => [],
                'questionReportUi' => [],
            ];
        }

        $questionIds = collect($questions)
            ->map(fn ($question) => data_get($question, 'id', data_get($question, 'tech_info.question_id')))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id): string => (string) $id)
            ->unique()
            ->values()
            ->all();

        $questionUuids = collect($questions)
            ->map(fn ($question) => data_get($question, 'uuid', data_get($question, 'tech_info.question_uuid')))
            ->filter(fn ($uuid) => filled($uuid))
            ->map(fn ($uuid): string => (string) $uuid)
            ->unique()
            ->values()
            ->all();

        return [
            'questionReportIssueCatalog' => app(QuestionReportIssueCatalog::class)->all(),
            'questionReportsByQuestion' => app(QuestionReportFileStore::class)
                ->openReportsByQuestionIdentifiers($questionIds, $questionUuids),
            'questionReportUi' => __('report_question'),
        ];
    }

    protected function buildQuestionDataset($resolved, bool $freshVariants = false, bool $includeTechnicalInfo = false)
    {
        $test = $resolved->model;
        $relations = ['category', 'answers.option', 'options', 'verbHints.option', 'theoryTextBlocks'];
        $supportsVariants = $this->variantService->supportsVariants();
        if ($supportsVariants) {
            $relations[] = 'variants';
        }
        if ($includeTechnicalInfo) {
            $relations[] = 'source';
            $relations[] = 'tags';
        }

        $questions = $this->savedTestResolver->loadQuestions($resolved, $relations);

        if ($supportsVariants) {
            if ($freshVariants) {
                $previousVariants = $this->variantService->getStoredVariants($test->slug);
                $this->variantService->clearForTest($test->slug);
                $questions = $this->variantService->applyRandomVariants($test, $questions, $previousVariants);
            } else {
                $questions = $questions->map(function ($question) use ($test) {
                    $this->variantService->applyStoredVariant($test->slug, $question);

                    return $question;
                });
            }
        }

        $testFilters = $this->normalizedTestFilters($test);
        $technicalInfoByQuestionId = $includeTechnicalInfo
            ? $this->questionTechnicalInfoService->mapForQuestions($questions, $testFilters)
            : [];

        $controller = $this;

        $preferredVerbHintLocales = $this->preferredVerbHintLocales();

        return $questions->map(function ($q) use ($controller, $technicalInfoByQuestionId, $preferredVerbHintLocales) {
            $answers = $controller->sortAnswersByMarker($q->answers)
                ->map(function ($a) {
                    return [
                        'marker' => $a->marker,
                        'value' => $a->option->option ?? $a->answer ?? '',
                    ];
                });

            $answerMap = $answers
                ->filter(fn ($ans) => ! empty($ans['marker']))
                ->mapWithKeys(fn ($ans) => [$ans['marker'] => $ans['value']])
                ->toArray();
            $markers = array_keys($answerMap);
            $answerList = array_map(fn ($marker) => $answerMap[$marker] ?? '', $markers);

            $options = $q->options->pluck('option')->toArray();
            foreach ($answerList as $ans) {
                if ($ans && ! in_array($ans, $options)) {
                    $options[] = $ans;
                }
            }

            $verbHints = $controller->localizedVerbHints($q->verbHints, $preferredVerbHintLocales);

            $optionsByMarker = $controller->normalizeOptionsByMarker($q->options_by_marker, $markers);
            $optionsByMarker = $controller->ensureMinimumOptionsByMarker(
                $optionsByMarker,
                $markers,
                $answerMap,
                $options
            );
            $firstMarker = $markers[0] ?? null;

            // Build the list of theory text blocks for this question.
            // Prefer the curated multi-block list (question_theory_text_blocks
            // pivot, eager-loaded above); fall back to the legacy single
            // theory_text_block_uuid column when no pivot rows exist.
            $theoryBlocks = [];
            $seenBlockUuids = [];

            if ($q->relationLoaded('theoryTextBlocks')) {
                foreach ($q->theoryTextBlocks as $block) {
                    if (! $block || $block->uuid === null) {
                        continue;
                    }
                    if (isset($seenBlockUuids[$block->uuid])) {
                        continue;
                    }
                    $seenBlockUuids[$block->uuid] = true;
                    $theoryBlocks[] = [
                        'uuid' => $block->uuid,
                        'type' => $block->type,
                        'body' => $block->body,
                        'level' => $block->level,
                    ];
                }
            }

            if ($theoryBlocks === [] && ! empty($q->theory_text_block_uuid)) {
                $textBlock = TextBlock::where('uuid', $q->theory_text_block_uuid)->first();
                if ($textBlock) {
                    $theoryBlocks[] = [
                        'uuid' => $textBlock->uuid,
                        'type' => $textBlock->type,
                        'body' => $textBlock->body,
                        'level' => $textBlock->level,
                    ];
                }
            }

            $theoryBlock = $theoryBlocks[0] ?? null;

            // Load marker tags for each answer marker
            $markerTags = $this->markerTheoryMatcher->getAllMarkerTags($q->id);

            return [
                'id' => $q->id,
                'uuid' => $q->uuid,
                'type' => $q->type,
                'question' => $q->question,
                'answer' => $answerList[0] ?? '',
                'answers' => $answerList,
                'answer_map' => $answerMap,
                'markers' => $markers,
                'markers_count' => count($markers),
                'options_by_marker' => $optionsByMarker,
                'verb_hint' => $firstMarker ? ($verbHints[$firstMarker] ?? '') : '',
                'verb_hints' => $verbHints,
                'options' => $options,
                'tense' => $q->category->name ?? '',
                'level' => $q->level ?? '',
                'theory_block' => $theoryBlock,
                'theory_blocks' => $theoryBlocks,
                'marker_tags' => $markerTags,
                'tech_info' => $technicalInfoByQuestionId[$q->id] ?? null,
            ];
        })->values()->all();
    }

    protected function preferredVerbHintLocales(): array
    {
        $candidates = [
            request()->query('locale'),
            request()->query('language'),
            session('locale'),
            app()->getLocale(),
            config('language-manager.fallback_locale'),
            config('app.locale'),
            '',
        ];

        $locales = [];

        foreach ($candidates as $candidate) {
            $locale = $this->normalizeVerbHintLocale($candidate);

            if ($locale === '') {
                continue;
            }

            if (! in_array($locale, $locales, true)) {
                $locales[] = $locale;
            }
        }

        if (! in_array('', $locales, true)) {
            $locales[] = '';
        }

        return $locales;
    }

    protected function localizedVerbHints($verbHints, array $preferredLocales): array
    {
        $priority = array_flip($preferredLocales);
        $selected = [];

        foreach ($verbHints as $verbHint) {
            $marker = strtoupper((string) $verbHint->marker);
            $text = trim((string) ($verbHint->option->option ?? ''));

            if ($marker === '' || $text === '') {
                continue;
            }

            $locale = $this->normalizeVerbHintLocale($verbHint->locale ?? '');
            $rank = $priority[$locale] ?? PHP_INT_MAX;
            $currentRank = $selected[$marker]['rank'] ?? PHP_INT_MAX;

            if ($rank < $currentRank) {
                $selected[$marker] = [
                    'rank' => $rank,
                    'text' => $text,
                ];
            }
        }

        return collect($selected)
            ->mapWithKeys(fn (array $item, string $marker) => [strtolower($marker) => $item['text']])
            ->all();
    }

    protected function localizePersistedQuestionVerbHints(array $questionData): array
    {
        $questionIds = collect($questionData)
            ->map(fn ($question) => data_get($question, 'id'))
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        $questionUuids = collect($questionData)
            ->map(fn ($question) => data_get($question, 'uuid'))
            ->filter(fn ($uuid) => filled($uuid))
            ->map(fn ($uuid): string => (string) $uuid)
            ->unique()
            ->values()
            ->all();

        if ($questionIds === [] && $questionUuids === []) {
            return $questionData;
        }

        $models = Question::query()
            ->with('verbHints.option')
            ->where(function ($query) use ($questionIds, $questionUuids): void {
                if ($questionIds !== []) {
                    $query->whereIn('id', $questionIds);
                }

                if ($questionUuids !== []) {
                    $method = $questionIds === [] ? 'whereIn' : 'orWhereIn';
                    $query->{$method}('uuid', $questionUuids);
                }
            })
            ->get();

        if ($models->isEmpty()) {
            return $questionData;
        }

        $byId = $models->keyBy(fn (Question $question): string => (string) $question->id);
        $byUuid = $models->keyBy(fn (Question $question): string => (string) $question->uuid);
        $preferredLocales = $this->preferredVerbHintLocales();

        foreach ($questionData as $index => $question) {
            if (! is_array($question)) {
                continue;
            }

            $model = $byId->get((string) data_get($question, 'id'))
                ?? $byUuid->get((string) data_get($question, 'uuid'));

            if (! $model) {
                continue;
            }

            $verbHints = $this->localizedVerbHints($model->verbHints, $preferredLocales);

            if ($verbHints === []) {
                continue;
            }

            $marker = $this->firstQuestionVerbHintMarker($question, $verbHints);

            $question['verb_hints'] = $verbHints;
            $question['verb_hint'] = $marker !== null
                ? ($verbHints[$marker] ?? reset($verbHints) ?: '')
                : '';

            $questionData[$index] = $question;
        }

        return $questionData;
    }

    protected function firstQuestionVerbHintMarker(array $question, array $verbHints): ?string
    {
        $markers = data_get($question, 'markers', []);

        if (is_array($markers)) {
            foreach ($markers as $marker) {
                $marker = strtolower(trim((string) $marker));

                if ($marker !== '') {
                    return $marker;
                }
            }
        }

        $answerMap = data_get($question, 'answer_map', []);

        if (is_array($answerMap)) {
            foreach (array_keys($answerMap) as $marker) {
                $marker = strtolower(trim((string) $marker));

                if ($marker !== '') {
                    return $marker;
                }
            }
        }

        $marker = array_key_first($verbHints);

        return $marker === null ? null : strtolower((string) $marker);
    }

    protected function normalizeVerbHintLocale(mixed $locale): string
    {
        $normalized = strtolower(trim((string) $locale));

        return $normalized === 'ua' ? 'uk' : $normalized;
    }

    protected function jsStateSessionKey($test, string $view): string
    {
        $key = sprintf('saved_test_js_state:%s:%s', $test->slug, $view);
        $launchToken = $this->sanitizeJsLaunchToken(request()->query('launch'));

        return $launchToken ? $key.':'.$launchToken : $key;
    }

    protected function jsQuestionDataSessionKey($test): string
    {
        $key = sprintf('saved_test_js_questions:%s', $test->slug);
        $launchToken = $this->sanitizeJsLaunchToken(request()->query('launch'));

        return $launchToken ? $key.':'.$launchToken : $key;
    }

    protected function activeJsSavedState(string $stateKey): ?array
    {
        $savedState = session($stateKey);

        if (! is_array($savedState)) {
            return null;
        }

        if (! SavedTestJsState::isStarted($savedState)) {
            session()->forget($stateKey);

            return null;
        }

        return $savedState;
    }

    protected function persistedQuestionData($test, ?array $savedState): ?array
    {
        $sessionKey = $this->jsQuestionDataSessionKey($test);
        $questionData = session($sessionKey);

        if (is_array($questionData)) {
            if ($this->cachedQuestionDataNeedsTheoryRefresh($questionData)) {
                session()->forget($sessionKey);

                return null;
            }

            return $questionData;
        }

        $questionData = SavedTestJsState::questionData($savedState);

        if (is_array($questionData)) {
            if ($this->cachedQuestionDataNeedsTheoryRefresh($questionData)) {
                return null;
            }

            session([$sessionKey => $questionData]);
        }

        return is_array($questionData) ? $questionData : null;
    }

    protected function cachedQuestionDataNeedsTheoryRefresh(array $questionData): bool
    {
        $questionUuids = collect($questionData)
            ->filter(fn ($question): bool => is_array($question))
            ->filter(function (array $question): bool {
                return filled($question['uuid'] ?? null)
                    && empty($question['theory_blocks'] ?? [])
                    && empty($question['theory_block'] ?? null);
            })
            ->pluck('uuid')
            ->map(fn ($uuid): string => (string) $uuid)
            ->unique()
            ->values();

        if ($questionUuids->isEmpty()) {
            return false;
        }

        return Question::query()
            ->whereIn('uuid', $questionUuids->all())
            ->where(function ($query): void {
                $query->whereNotNull('theory_text_block_uuid')
                    ->orWhereExists(function ($subQuery): void {
                        $subQuery->select(DB::raw(1))
                            ->from('question_theory_text_blocks')
                            ->whereColumn('question_theory_text_blocks.question_uuid', 'questions.uuid');
                    });
            })
            ->exists();
    }

    protected function buildComposeQuestionDataset($resolved, bool $includeTechnicalInfo = false): array
    {
        $filters = ComposeModeEligibility::normalizedFilters($resolved->model);
        $relations = ['answers.option', 'options', 'hints', 'chatgptExplanations', 'theoryTextBlocks'];
        if ($includeTechnicalInfo) {
            $relations[] = 'category';
            $relations[] = 'source';
            $relations[] = 'tags';
        }

        $questions = $this->savedTestResolver->loadQuestions($resolved, $relations)
            ->filter(fn (Question $question) => ComposeModeEligibility::supportsQuestion($question, $filters))
            ->values();

        if ($questions->isEmpty()) {
            return [];
        }

        $technicalInfoByQuestionId = $includeTechnicalInfo
            ? $this->questionTechnicalInfoService->mapForQuestions($questions, $filters)
            : [];

        $payload = $questions
            ->map(fn (Question $question) => $this->buildComposeQuestionPayload(
                $question,
                $technicalInfoByQuestionId[$question->id] ?? null
            ))
            ->filter()
            ->values()
            ->all();

        return $this->shouldShuffleComposeQuestions($filters)
            ? $this->shuffleComposeQuestionPayload($payload)
            : $payload;
    }

    protected function shouldShuffleComposeQuestions(array $filters): bool
    {
        return (bool) config('tests.compose_shuffle_enabled', true)
            && filled(data_get($filters, 'course_slug'));
    }

    protected function shuffleComposeQuestionPayload(array $payload): array
    {
        if (count($payload) < 2) {
            return $payload;
        }

        $seed = config('tests.compose_shuffle_seed');

        if (is_string($seed) && trim($seed) !== '') {
            return collect($payload)
                ->sortBy(function (array $question, int $index) use ($seed) {
                    $stableId = trim((string) ($question['uuid'] ?? $question['id'] ?? $index));

                    return sha1(trim($seed).'|'.$stableId.'|'.$index);
                })
                ->values()
                ->all();
        }

        return collect($payload)->shuffle()->values()->all();
    }

    protected function isAdminUser(): bool
    {
        return AdminDebugAccess::allowed(request());
    }

    protected function shouldShowTechnicalInfo(?bool $isAdmin = null): bool
    {
        $isAdmin ??= $this->isAdminUser();

        return $isAdmin && (bool) config('tests.tech_info_enabled', true);
    }

    protected function datasetContainsTechnicalInfo(array $questions, mixed $test = null): bool
    {
        $requiresTheoryPage = $test !== null && $this->isTheoryCategoryPageTest($test);
        $containsTechnicalInfo = false;

        foreach ($questions as $question) {
            if (is_array($question) && array_key_exists('tech_info', $question)) {
                $containsTechnicalInfo = true;

                if ($requiresTheoryPage && ! is_array(data_get($question, 'tech_info.theory_page'))) {
                    return false;
                }
            }
        }

        return $containsTechnicalInfo;
    }

    protected function isTheoryCategoryPageTest(mixed $test): bool
    {
        $filters = $this->normalizedTestFilters($test);

        return (bool) data_get(
            $filters,
            'theory_category_page_test',
            data_get($filters, '__meta.aggregated_theory_category_test', false)
        );
    }

    protected function normalizeOptionsByMarker($rawOptions, array $markers): ?array
    {
        if (! is_array($rawOptions)) {
            return null;
        }

        $normalized = [];
        $hasValues = false;

        foreach ($markers as $index => $marker) {
            $options = $rawOptions[$marker] ?? $rawOptions[$index] ?? [];
            $options = is_array($options) ? $this->filterOptionArray($options) : [];
            if (! empty($options)) {
                $hasValues = true;
            }
            $normalized[] = $options;
        }

        return $hasValues ? $normalized : null;
    }

    protected function ensureMinimumOptionsByMarker(
        ?array $optionsByMarker,
        array $markers,
        array $answerMap,
        array $fallbackOptions,
        int $minimum = 3
    ): ?array {
        if ($markers === []) {
            return $optionsByMarker;
        }

        $normalized = $optionsByMarker;
        if (! is_array($normalized)) {
            $normalized = array_fill(0, count($markers), []);
        }

        foreach ($markers as $index => $marker) {
            $answer = trim((string) ($answerMap[$marker] ?? ''));
            $options = $this->filterOptionArray($normalized[$index] ?? []);

            if ($answer !== '' && ! $this->containsOption($options, $answer)) {
                array_unshift($options, $answer);
            }

            $pool = $this->filterOptionArray(array_merge(
                $fallbackOptions,
                array_values($answerMap),
                $this->grammarDistractorPool($answer)
            ));

            foreach ($pool as $candidate) {
                if (count($options) >= $minimum) {
                    break;
                }
                if (! $this->containsOption($options, $candidate)) {
                    $options[] = $candidate;
                }
            }

            $normalized[$index] = $options;
        }

        return $normalized;
    }

    protected function containsOption(array $options, string $candidate): bool
    {
        $normalizedCandidate = mb_strtolower(trim($candidate));

        foreach ($options as $option) {
            if (mb_strtolower(trim((string) $option)) === $normalizedCandidate) {
                return true;
            }
        }

        return false;
    }

    protected function grammarDistractorPool(string $answer): array
    {
        $answer = trim($answer);
        $pool = [
            'do', 'does', 'did',
            'am', 'is', 'are', 'was', 'were',
            'have', 'has', 'had',
            'will', 'would', 'can', 'could', 'should', 'must',
            'not', 'never', 'always', 'usually', 'often',
            'work', 'works', 'worked', 'working',
            'go', 'goes', 'went', 'gone', 'going',
            'study', 'studies', 'studied', 'studying',
            'play', 'plays', 'played', 'playing',
            'read', 'reads', 'reading',
            'take', 'takes', 'took', 'taken', 'taking',
        ];

        if ($answer !== '') {
            $pool[] = $answer.'s';
            $pool[] = $answer.'ed';
            $pool[] = $answer.'ing';

            if (str_ends_with($answer, 's')) {
                $pool[] = substr($answer, 0, -1);
            }
            if (str_ends_with($answer, 'es')) {
                $pool[] = substr($answer, 0, -2);
            }
            if (str_ends_with($answer, 'ing')) {
                $pool[] = substr($answer, 0, -3);
            }
            if (str_ends_with($answer, 'ed')) {
                $pool[] = substr($answer, 0, -2);
            }
        }

        return $pool;
    }

    protected function filterOptionArray(array $options): array
    {
        $clean = [];
        foreach ($options as $option) {
            $value = is_string($option) ? trim($option) : trim((string) $option);
            if ($value === '') {
                continue;
            }
            if (! in_array($value, $clean, true)) {
                $clean[] = $value;
            }
        }

        return $clean;
    }

    protected function sortAnswersByMarker(EloquentCollection $answers): EloquentCollection
    {
        return $answers
            ->sortBy(fn ($answer) => $this->markerSortValue($answer->marker))
            ->values();
    }

    protected function markerSortValue(?string $marker): string
    {
        $normalized = strtolower(trim((string) $marker));

        if (preg_match('/^([a-z_]+)(\d+)$/', $normalized, $matches) === 1) {
            return sprintf('%s%08d', $matches[1], (int) $matches[2]);
        }

        return $normalized;
    }

    protected function normalizedTestFilters($test): array
    {
        return ComposeModeEligibility::normalizedFilters($test);
    }

    protected function isMixedPolyglotTheoryTest(mixed $test): bool
    {
        return (bool) data_get(
            $this->normalizedTestFilters($test),
            '__meta.theory_page_mixed_polyglot_test',
            false
        );
    }

    protected function redirectForUnsupportedMixedPolyglotMode(mixed $test, string $mode): ?RedirectResponse
    {
        if (! $this->isMixedPolyglotTheoryTest($test)) {
            return null;
        }

        if (in_array($mode, ['saved-test-js-v2', 'saved-test-js-step-v2'], true)) {
            return null;
        }

        $routeName = Str::contains($mode, 'step') ? 'test.step' : 'test.show';
        $url = localized_route($routeName, $test->slug);
        $query = request()->only(['filters', 'name', 'launch']);

        if ($query !== []) {
            $url .= '?'.http_build_query($query);
        }

        return redirect()->to($url);
    }

    protected function buildPolyglotCourseContext(mixed $test): ?array
    {
        $filters = $this->normalizedTestFilters($test);
        $courseSlug = trim((string) ($filters['course_slug'] ?? ''));
        $testSlug = trim((string) ($test->slug ?? ''));

        if ($courseSlug === '' || $testSlug === '') {
            return null;
        }

        $manifest = $this->polyglotCourseManifestService->build($courseSlug);
        $lesson = $this->polyglotCourseManifestService->findLesson($manifest, $testSlug);

        if (! $lesson) {
            return null;
        }

        $previousLesson = $this->polyglotCourseManifestService->previousLesson($manifest, $testSlug);
        $nextLesson = $this->polyglotCourseManifestService->nextLesson($manifest, $testSlug);
        $firstLesson = $this->polyglotCourseManifestService->firstLesson($manifest);

        return [
            'course_slug' => $courseSlug,
            'public_course_slug' => SentenceBuilderBranding::canonicalCourseSlug($courseSlug),
            'course_name' => $manifest['course']['name'] ?? null,
            'course_description' => $manifest['course']['description'] ?? null,
            'course_url' => localized_route('courses.show', SentenceBuilderBranding::canonicalCourseSlug($courseSlug)),
            'lesson_slug' => $lesson['slug'],
            'public_lesson_slug' => $lesson['public_slug'] ?? SentenceBuilderBranding::canonicalLessonSlug($lesson['slug']),
            'lesson_order' => $lesson['lesson_order'],
            'topic' => $lesson['topic'],
            'level' => $lesson['level'],
            'first_lesson_slug' => $firstLesson['slug'] ?? null,
            'first_lesson_url' => $firstLesson['compose_url'] ?? null,
            'previous_lesson_slug' => $previousLesson['slug'] ?? ($lesson['previous_lesson_slug'] ?? null),
            'previous_lesson_url' => $previousLesson['compose_url'] ?? null,
            'next_lesson_slug' => $nextLesson['slug'] ?? ($lesson['next_lesson_slug'] ?? null),
            'next_lesson_url' => $nextLesson['compose_url'] ?? null,
            'is_final_lesson' => ($nextLesson['slug'] ?? ($lesson['next_lesson_slug'] ?? null)) === null,
            'total_lessons' => $this->polyglotCourseManifestService->totalLessons($manifest),
            'completion' => $lesson['completion'] ?? [],
            'mode' => $lesson['mode'] ?? null,
            'lessons' => $manifest['lessons'] ?? [],
        ];
    }

    protected function composePunctuationForSource(?string $sourceText): string
    {
        return Str::endsWith(trim((string) $sourceText), '?') ? '?' : '.';
    }

    protected function buildComposeSentence(array $tokens, string $punctuation): string
    {
        $sentence = trim(implode(' ', array_map(fn ($token) => trim((string) $token), $tokens)));

        if ($sentence === '') {
            return '';
        }

        return preg_replace('/\s+([?.!,;:])/u', '$1', $sentence.$punctuation) ?? ($sentence.$punctuation);
    }

    protected function composeHintText(Question $question): ?string
    {
        if (! $question->relationLoaded('hints')) {
            return null;
        }

        $hint = $question->hints
            ->sortByDesc(fn ($item) => ($item->locale === 'uk' ? 1 : 0))
            ->first(fn ($item) => filled($item->hint));

        return $hint ? trim((string) $hint->hint) : null;
    }

    protected function composeExplanationMap(Question $question): array
    {
        if (! $question->relationLoaded('chatgptExplanations')) {
            return [];
        }

        return $question->chatgptExplanations
            ->filter(function ($item) {
                $language = strtolower(trim((string) ($item->language ?? '')));

                return in_array($language, ['', 'ua', 'uk'], true)
                    && filled($item->wrong_answer)
                    && filled($item->explanation);
            })
            ->mapWithKeys(fn ($item) => [
                trim((string) $item->wrong_answer) => trim((string) $item->explanation),
            ])
            ->toArray();
    }

    protected function sanitizeJsLaunchToken(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $token = trim($value);

        if ($token === '' || strlen($token) > 80) {
            return null;
        }

        return preg_match('/^[A-Za-z0-9_-]+$/', $token) === 1 ? $token : null;
    }

    protected function buildComposeQuestionPayload(Question $question, mixed $technicalInfo = null): ?array
    {
        $orderedAnswers = $this->sortAnswersByMarker($question->answers);
        $correctTokens = $orderedAnswers
            ->map(fn ($answer) => trim((string) ($answer->option->option ?? $answer->answer ?? '')))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->all();

        if ($correctTokens === []) {
            return null;
        }

        $punctuation = $this->composePunctuationForSource($question->question);
        $tokenBank = $this->buildComposeTokenBank(
            $correctTokens,
            $question->options->pluck('option')->all()
        );
        $correctTokenIds = collect($tokenBank)
            ->where('isCorrect', true)
            ->sortBy('correctIndex')
            ->pluck('id')
            ->values()
            ->all();

        $theoryBlocks = $this->resolveTheoryBlocksForQuestion($question);

        return [
            'id' => $question->id,
            'uuid' => $question->uuid,
            'type' => $question->type,
            'level' => $question->level,
            'sourceTextUk' => $question->question,
            'correctTokens' => $correctTokens,
            'correctTokenValues' => $correctTokens,
            'correctTokenIds' => $correctTokenIds,
            'tokenBank' => $tokenBank,
            'tokensPool' => collect($tokenBank)->pluck('value')->unique()->values()->all(),
            'correctText' => $this->buildComposeSentence($correctTokens, $punctuation),
            'hintUk' => $this->composeHintText($question),
            'explanations' => $this->composeExplanationMap($question),
            'punctuation' => $punctuation,
            'theory_block' => $theoryBlocks[0] ?? null,
            'theory_blocks' => $theoryBlocks,
            'tech_info' => $technicalInfo,
        ];
    }

    /**
     * Build the curated list of theory text blocks attached to a question,
     * preferring the multi-block pivot and falling back to the legacy
     * single theory_text_block_uuid column. Used by both card-mode and
     * compose-mode payloads.
     */
    protected function resolveTheoryBlocksForQuestion(Question $question): array
    {
        $blocks = [];
        $seen = [];

        if ($question->relationLoaded('theoryTextBlocks')) {
            foreach ($question->theoryTextBlocks as $block) {
                if (! $block || $block->uuid === null || isset($seen[$block->uuid])) {
                    continue;
                }
                $seen[$block->uuid] = true;
                $blocks[] = [
                    'uuid' => $block->uuid,
                    'type' => $block->type,
                    'body' => $block->body,
                    'level' => $block->level,
                ];
            }
        }

        if ($blocks === [] && ! empty($question->theory_text_block_uuid)) {
            $textBlock = TextBlock::where('uuid', $question->theory_text_block_uuid)->first();
            if ($textBlock) {
                $blocks[] = [
                    'uuid' => $textBlock->uuid,
                    'type' => $textBlock->type,
                    'body' => $textBlock->body,
                    'level' => $textBlock->level,
                ];
            }
        }

        return $blocks;
    }

    protected function buildComposeTokenBank(array $correctTokens, array $optionValues): array
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

            $distractorIndex += 1;
        }

        return $instances;
    }
}
