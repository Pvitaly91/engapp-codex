<?php

namespace App\Http\Controllers;

use App\Models\TextBlock;
use App\Services\QuestionVariantService;
use App\Services\SavedTestResolver;

class TestJsV2Controller extends Controller
{
    public function __construct(
        private QuestionVariantService $variantService,
        private SavedTestResolver $savedTestResolver,
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

    private function renderSavedTestJsV2View(string $slug, string $view)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $stateKey = $this->jsStateSessionKey($test, $view);
        $savedState = session($stateKey);
        $questions = $this->buildQuestionDataset($resolved, empty($savedState));

        return view("tests.$view", [
            'test' => $test,
            'questionData' => $questions,
            'jsStateMode' => $view,
            'savedState' => $savedState,
            'usesUuidLinks' => $resolved->usesUuidLinks,
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

        return $questions->map(function ($q) {
            $answers = $q->answers->map(function ($a) {
                return [
                    'marker' => $a->marker,
                    'value' => $a->option->option ?? $a->answer ?? '',
                ];
            });

            $answerList = $answers->pluck('value')->values()->toArray();
            $answerMap = $answers
                ->filter(fn ($ans) => ! empty($ans['marker']))
                ->mapWithKeys(fn ($ans) => [$ans['marker'] => $ans['value']])
                ->toArray();
            $options = $q->options->pluck('option')->toArray();
            foreach ($answerList as $ans) {
                if ($ans && ! in_array($ans, $options)) {
                    $options[] = $ans;
                }
            }

            $verbHints = $q->verbHints
                ->mapWithKeys(fn ($vh) => [$vh->marker => $vh->option->option ?? ''])
                ->toArray();

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

            return [
                'id' => $q->id,
                'question' => $q->question,
                'answer' => $answerList[0] ?? '',
                'answers' => $answerList,
                'answer_map' => $answerMap,
                'verb_hint' => $verbHints['a1'] ?? '',
                'verb_hints' => $verbHints,
                'options' => $options,
                'tense' => $q->category->name ?? '',
                'level' => $q->level ?? '',
                'theory_block' => $theoryBlock,
            ];
        })->values()->all();
    }

    private function jsStateSessionKey($test, string $view): string
    {
        return sprintf('saved_test_js_state:%s:%s', $test->slug, $view);
    }
}
