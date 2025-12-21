<?php

namespace App\Http\Controllers;

use App\Models\TextBlock;
use App\Services\MarkerTheoryMatcherService;
use App\Services\QuestionVariantService;
use App\Services\SavedTestResolver;

class TestJsV2Controller extends Controller
{
    private const BUILDER_MIN_MARKERS = 2;

    public function __construct(
        private QuestionVariantService $variantService,
        private SavedTestResolver $savedTestResolver,
        private MarkerTheoryMatcherService $markerTheoryMatcher,
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
     * Show the V2 builder mode of the JS select test page with new UI design.
     */
    public function showSavedTestJsBuilderV2($slug)
    {
        return $this->renderSavedTestJsV2View(
            $slug,
            'saved-test-js-select-v2',
            'saved-test-js-builder-v2',
            fn ($q) => (($q['markers_count'] ?? 0) >= self::BUILDER_MIN_MARKERS) || ! empty($q['options_by_marker'])
        );
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

    private function renderSavedTestJsV2View(string $slug, string $view, ?string $mode = null, ?callable $filter = null)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $mode ??= $view;
        $stateKey = $this->jsStateSessionKey($test, $mode);
        $savedState = session($stateKey);
        $questions = $this->buildQuestionDataset($resolved, empty($savedState));
        if ($filter) {
            $questions = array_values(array_filter($questions, $filter));
        }

        return view("tests.$view", [
            'test' => $test,
            'questionData' => $questions,
            'jsStateMode' => $mode,
            'savedState' => $savedState,
            'usesUuidLinks' => $resolved->usesUuidLinks,
            'isAdmin' => $this->isAdminUser(),
        ]);
    }

    private function buildQuestionDataset($resolved, bool $freshVariants = false)
    {
        $test = $resolved->model;
        $relations = ['category', 'answers.option', 'options', 'verbHints.option'];
        $supportsVariants = $this->variantService->supportsVariants();
        if ($supportsVariants) {
            $relations[] = 'variants';
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

        $controller = $this;

        return $questions->map(function ($q) use ($controller) {
            $answers = $q->answers
                ->sortBy('marker')
                ->values()
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

            $verbHints = $q->verbHints
                ->mapWithKeys(fn ($vh) => [$vh->marker => $vh->option->option ?? ''])
                ->toArray();

            $optionsByMarker = $controller->normalizeOptionsByMarker($q->options_by_marker, $markers);
            $firstMarker = $markers[0] ?? null;

            // Load theory text block if question has a theory_text_block_uuid
            $theoryBlock = null;
            if (! empty($q->theory_text_block_uuid)) {
                $textBlock = TextBlock::where('uuid', $q->theory_text_block_uuid)->first();
                if ($textBlock) {
                    $theoryBlock = [
                        'uuid' => $textBlock->uuid,
                        'type' => $textBlock->type,
                        'body' => $textBlock->body,
                        'level' => $textBlock->level,
                    ];
                }
            }

            // Load marker tags for each answer marker
            $markerTags = $this->markerTheoryMatcher->getAllMarkerTags($q->id);

            return [
                'id' => $q->id,
                'uuid' => $q->uuid,
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
                'marker_tags' => $markerTags,
            ];
        })->values()->all();
    }

    private function jsStateSessionKey($test, string $mode): string
    {
        return sprintf('saved_test_js_state:%s:%s', $test->slug, $mode);
    }

    private function isAdminUser(): bool
    {
        return (bool) (auth()->user()?->is_admin ?? session('admin_authenticated', false));
    }

    private function normalizeOptionsByMarker($rawOptions, array $markers): ?array
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

    private function filterOptionArray(array $options): array
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
}
