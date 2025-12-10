<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QuestionVariantService;
use App\Services\SavedTestResolver;
use App\Models\Question;

class TestJsV2Controller extends Controller
{
    private const JS_VIEWS = [
        'saved-test-js-v2',
    ];

    public function __construct(
        private QuestionVariantService $variantService,
        private SavedTestResolver $savedTestResolver,
    )
    {
    }

    public function show($slug)
    {
        return $this->renderSavedTestJsView($slug, 'saved-test-js-v2');
    }

    public function fetchQuestions(Request $request, string $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $mode = $request->query('mode');

        if ($mode && ! in_array($mode, self::JS_VIEWS, true)) {
            return response()->json(['message' => 'Invalid mode'], 422);
        }

        if ($mode) {
            session()->forget($this->jsStateSessionKey($test, $mode));
        }

        $questions = $this->buildQuestionDataset($resolved, true);

        return response()->json(['questions' => $questions]);
    }

    public function storeState(Request $request, string $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $mode = $request->input('mode');

        if (! $mode || ! in_array($mode, self::JS_VIEWS, true)) {
            return response()->json(['message' => 'Invalid mode'], 422);
        }

        $key = $this->jsStateSessionKey($test, $mode);
        $state = $request->input('state');

        if ($state === null) {
            session()->forget($key);

            return response()->noContent();
        }

        if (! is_array($state)) {
            return response()->json(['message' => 'Invalid state'], 422);
        }

        session([$key => $state]);

        return response()->noContent();
    }

    private function renderSavedTestJsView(string $slug, string $view)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $stateKey = $this->jsStateSessionKey($test, $view);
        $savedState = session($stateKey);
        $questions = $this->buildQuestionDataset($resolved, empty($savedState));

        return view("tests.js-v2", [
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
                $questions = $questions->map(function (Question $question) use ($test) {
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
                ->mapWithKeys(fn($vh) => [$vh->marker => $vh->option->option ?? ''])
                ->toArray();

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
            ];
        })->values()->all();
    }

    private function jsStateSessionKey($test, string $view): string
    {
        return sprintf('saved_test_js_state_v2:%s:%s', $test->slug, $view);
    }
}
