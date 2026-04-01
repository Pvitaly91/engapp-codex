<?php

namespace App\Http\Controllers;

use App\Models\TextBlock;
use App\Services\MarkerTheoryMatcherService;
use App\Services\QuestionVariantService;
use App\Services\SavedTestResolver;

class TestJsV2Controller extends Controller
{
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

    protected function renderSavedTestShell(string $slug, string $mode, string $view, array $extra = [])
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $stateKey = $this->jsStateSessionKey($test, $mode);
        $savedState = session($stateKey);
        $questions = $this->buildQuestionDataset($resolved, empty($savedState));

        return view($view, array_merge([
            'test' => $test,
            'questionData' => $questions,
            'jsStateMode' => $mode,
            'savedState' => $savedState,
            'usesUuidLinks' => $resolved->usesUuidLinks,
            'isAdmin' => $this->isAdminUser(),
        ], $extra));
    }

    protected function buildQuestionDataset($resolved, bool $freshVariants = false)
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

    protected function jsStateSessionKey($test, string $view): string
    {
        $key = sprintf('saved_test_js_state:%s:%s', $test->slug, $view);
        $launchToken = $this->sanitizeJsLaunchToken(request()->query('launch'));

        return $launchToken ? $key . ':' . $launchToken : $key;
    }

    protected function isAdminUser(): bool
    {
        return (bool) (auth()->user()?->is_admin ?? session('admin_authenticated', false));
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
}
