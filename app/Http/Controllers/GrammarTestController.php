<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Category;
use Illuminate\Support\Arr;
use App\Models\Source;
use Illuminate\Support\Str;
use App\Models\Test;
use App\Models\Tag;
use App\Models\ChatGPTExplanation;
use App\Services\QuestionVariantService;
use App\Http\Resources\TechnicalQuestionResource;

class GrammarTestController extends Controller
{
    private const JS_VIEWS = [
        'saved-test-js',
        'saved-test-js-step',
        'saved-test-js-manual',
        'saved-test-js-step-manual',
        'saved-test-js-step-input',
        'saved-test-js-input',
        'saved-test-js-step-select',
        'saved-test-js-select',
    ];

    public function __construct(private QuestionVariantService $variantService)
    {
    }

    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'filters' => 'required|string',
            'questions' => 'required|string',
        ]);

        $filters = json_decode(html_entity_decode($request->input('filters')), true);
        $questionIds = json_decode(html_entity_decode($request->input('questions')), true);

        // Генеруємо унікальний slug
        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $i = 1;
        while (\App\Models\Test::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $i++;
        }

        $test = \App\Models\Test::create([
            'name' => $request->name,
            'slug' => $slug,
            'filters' => $filters,
            'questions' => $questionIds,
        ]);

        return redirect()->route('saved-test.show', $slug);
    }

    public function showSavedTest($slug)
    {
        $test = \App\Models\Test::where('slug', $slug)->firstOrFail();
        $supportsVariants = $this->variantService->supportsVariants();
        $relations = ['category', 'answers.option', 'options', 'verbHints.option', 'tags'];
        if ($supportsVariants) {
            $relations[] = 'variants';
        }

        $questions = \App\Models\Question::with($relations)
            ->whereIn('id', $test->questions)
            ->orderBy('id')
            ->get();

        if ($supportsVariants) {
            $previousVariants = $this->variantService->getStoredVariants($test->slug);
            $this->variantService->clearForTest($test->slug);
            $questions = $this->variantService->applyRandomVariants($test, $questions, $previousVariants);
        }

        $manualInput = !empty($test->filters['manual_input']);
        $autocompleteInput = !empty($test->filters['autocomplete_input']);
        $builderInput = !empty($test->filters['builder_input']);
        // Показати тільки питання — без фільтрів, флагів, тощо
        return view('saved-test', [
            'test' => $test,
            'questions' => $questions,
            'manualInput' => $manualInput,
            'autocompleteInput' => $autocompleteInput,
            'builderInput' => $builderInput,
        ]);
    }

    public function showSavedTestTech(string $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $supportsVariants = $this->variantService->supportsVariants();

        $relations = ['answers.option', 'options', 'verbHints.option', 'hints'];
        if ($supportsVariants) {
            $relations[] = 'variants';
        }

        $questions = Question::with($relations)
            ->whereIn('id', $test->questions)
            ->orderBy('id')
            ->get();

        $textToQuestionIds = [];

        foreach ($questions as $question) {
            $texts = collect([
                $question->getOriginal('question'),
                $question->question,
                $question->renderQuestionText(),
            ]);

            if ($supportsVariants) {
                $texts = $texts->merge($question->variants->pluck('text'));
            }

            $texts
                ->filter(fn($text) => is_string($text) && trim($text) !== '')
                ->map(fn($text) => trim($text))
                ->unique()
                ->each(function (string $text) use ($question, &$textToQuestionIds) {
                    $textToQuestionIds[$text][] = $question->id;
                });
        }

        $explanationsByQuestionId = [];

        if (! empty($textToQuestionIds)) {
            $explanations = ChatGPTExplanation::query()
                ->whereIn('question', array_keys($textToQuestionIds))
                ->orderBy('language')
                ->orderBy('wrong_answer')
                ->orderBy('correct_answer')
                ->get();

            foreach ($explanations as $explanation) {
                $key = trim((string) $explanation->question);
                $questionIds = $textToQuestionIds[$key] ?? [];

                foreach ($questionIds as $questionId) {
                    $explanationsByQuestionId[$questionId][] = $explanation;
                }
            }
        }

        return view('engram.saved-test-tech', [
            'test' => $test,
            'questions' => $questions,
            'explanationsByQuestionId' => $explanationsByQuestionId,
        ]);
    }

    public function showSavedTestRandom($slug)
    {
        $test = \App\Models\Test::where('slug', $slug)->firstOrFail();
        $supportsVariants = $this->variantService->supportsVariants();
        $relations = ['category', 'answers.option', 'options', 'verbHints.option', 'tags'];
        if ($supportsVariants) {
            $relations[] = 'variants';
        }

        $questions = \App\Models\Question::with($relations)
            ->whereIn('id', $test->questions)
            ->get();

        if ($supportsVariants) {
            $previousVariants = $this->variantService->getStoredVariants($test->slug);
            $this->variantService->clearForTest($test->slug);
            $questions = $this->variantService->applyRandomVariants($test, $questions, $previousVariants);
        }

        return view('saved-test-random', [
            'test' => $test,
            'questions' => $questions,
        ]);
    }

    public function showSavedTestJs($slug)
    {
        return $this->renderSavedTestJsView($slug, 'saved-test-js');
    }

    public function showSavedTestJsStep($slug)
    {
        return $this->renderSavedTestJsView($slug, 'saved-test-js-step');
    }

    public function showSavedTestJsManual($slug)
    {
        return $this->renderSavedTestJsView($slug, 'saved-test-js-manual');
    }

    public function showSavedTestJsStepManual($slug)
    {
        return $this->renderSavedTestJsView($slug, 'saved-test-js-step-manual');
    }

    public function showSavedTestJsStepInput($slug)
    {
        return $this->renderSavedTestJsView($slug, 'saved-test-js-step-input');
    }

    public function showSavedTestJsInput($slug)
    {
        return $this->renderSavedTestJsView($slug, 'saved-test-js-input');
    }

    public function showSavedTestJsStepSelect($slug)
    {
        return $this->renderSavedTestJsView($slug, 'saved-test-js-step-select');
    }

    public function showSavedTestJsSelect($slug)
    {
        return $this->renderSavedTestJsView($slug, 'saved-test-js-select');
    }

    private function renderSavedTestJsView(string $slug, string $view)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $stateKey = $this->jsStateSessionKey($test, $view);
        $savedState = session($stateKey);
        $questions = $this->buildQuestionDataset($test, empty($savedState));

        return view("engram.$view", [
            'test' => $test,
            'questionData' => $questions,
            'jsStateMode' => $view,
            'savedState' => $savedState,
        ]);
    }

    public function fetchSavedTestJsQuestions(Request $request, string $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $mode = $request->query('mode');

        if ($mode && ! in_array($mode, self::JS_VIEWS, true)) {
            return response()->json(['message' => 'Invalid mode'], 422);
        }

        if ($mode) {
            session()->forget($this->jsStateSessionKey($test, $mode));
        }

        $questions = $this->buildQuestionDataset($test, true);

        return response()->json(['questions' => $questions]);
    }

    public function storeSavedTestJsState(Request $request, string $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
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

    private function buildQuestionDataset(Test $test, bool $freshVariants = false)
    {
        $relations = ['category', 'answers.option', 'options', 'verbHints.option'];
        $supportsVariants = $this->variantService->supportsVariants();
        if ($supportsVariants) {
            $relations[] = 'variants';
        }

        $questions = Question::with($relations)
            ->whereIn('id', $test->questions)
            ->orderBy('id')
            ->get();

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
                return $a->option->option ?? $a->answer ?? '';
            });

            $answerList = $answers->values()->toArray();
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
                'verb_hint' => $verbHints['a1'] ?? '',
                'verb_hints' => $verbHints,
                'options' => $options,
                'tense' => $q->category->name ?? '',
                'level' => $q->level ?? '',
            ];
        })->values()->all();
    }

    private function jsStateSessionKey(Test $test, string $view): string
    {
        return sprintf('saved_test_js_state:%s:%s', $test->slug, $view);
    }

    public function showSavedTestStep(Request $request, $slug)
    {
        $test = \App\Models\Test::where('slug', $slug)->firstOrFail();

        $key = 'step_' . $test->slug;

        $orderParam = $request->query('order');
        $allowedOrders = ['sequential', 'random'];
        if ($orderParam && in_array($orderParam, $allowedOrders)) {
            if ($orderParam !== session($key . '_order')) {
                session([$key . '_order' => $orderParam]);
                session()->forget([
                    $key . '_stats',
                    $key . '_queue',
                    $key . '_total',
                    $key . '_index',
                    $key . '_feedback',
                ]);
            }
        }

        $order = session($key . '_order', 'sequential');
        $stats = session($key . '_stats', ['correct' => 0, 'wrong' => 0, 'total' => 0]);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;
        $queue = session($key . '_queue');
        $index = session($key . '_index', 0);
        $totalCount = session($key . '_total', 0);
        if (! $queue) {
            $queue = \App\Models\Question::whereIn('id', $test->questions)
                ->orderBy('id')
                ->pluck('id')
                ->toArray();
            if ($order === 'random') {
                shuffle($queue);
            }
            $totalCount = count($queue);
            session([$key . '_queue' => $queue, $key . '_total' => $totalCount, $key . '_index' => 0]);
            $index = 0;
        }

        $nav = $request->query('nav');
        if ($nav === 'next' && $index < count($queue) - 1) {
            $index++;
            session([$key . '_index' => $index]);
        } elseif ($nav === 'prev' && $index > 0) {
            $index--;
            session([$key . '_index' => $index]);
        }

        if ($index >= count($queue)) {
            return view('saved-test-complete', [
                'test' => $test,
                'stats' => $stats,
                'percentage' => $percentage,
                'totalCount' => $totalCount,
            ]);
        }

        $currentId = $queue[$index];
        $relations = ['options', 'answers.option', 'verbHints.option', 'tags'];
        if ($this->variantService->supportsVariants()) {
            $relations[] = 'variants';
        }

        $question = \App\Models\Question::with($relations)
            ->findOrFail($currentId);

        $this->variantService->applyRandomVariant($test, $question);

        $questionNumber = array_search($currentId, $test->questions, true);
        $questionNumber = $questionNumber === false ? null : $questionNumber + 1;

        $feedback = session($key . '_feedback');
        session()->forget($key . '_feedback');

        return view('saved-test-step', [
            'test' => $test,
            'question' => $question,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => $totalCount,
            'feedback' => $feedback,
            'order' => $order,
            'hasPrev' => $index > 0,
            'hasNext' => $index < count($queue) - 1,
            'questionNumber' => $questionNumber,
        ]);
    }

    public function refreshDescription($slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $questions = Question::whereIn('id', $test->questions)->pluck('question');
        $gpt = app(\App\Services\ChatGPTService::class);
        $test->description = $gpt->generateTestDescription($questions->toArray());
        $test->save();

        return redirect()->back();
    }

    public function refreshDescriptionGemini($slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $questions = Question::whereIn('id', $test->questions)->pluck('question');
        $gemini = app(\App\Services\GeminiService::class);
        $test->description = $gemini->generateTestDescription($questions->toArray());
        $test->save();

        return redirect()->back();
    }

    public function checkSavedTestStep(Request $request, $slug)
    {
        $test = \App\Models\Test::where('slug', $slug)->firstOrFail();
        $key = 'step_' . $test->slug;
        $request->validate([
            'question_id' => 'required|integer',
            'answers' => 'required|array',
        ]);
        $question = \App\Models\Question::with('answers.option')->findOrFail($request->input('question_id'));
        $this->variantService->applyStoredVariant($slug, $question);
        $userAnswers = $request->input('answers', []);
        $correct = true;
        $explanations = [];
        $givenAnswers = [];
        $gpt = app(\App\Services\ChatGPTService::class);
        $sentenceHtml = e($question->question);
        foreach ($question->answers as $ans) {
            $given = $userAnswers[$ans->marker] ?? '';
            if (is_array($given)) {
                $given = implode(' ', $given);
            }
            $given = trim($given);
            $givenAnswers[$ans->marker] = $given;
            $correctValue = $ans->option->option ?? $ans->answer;
            $isCorrectAnswer = mb_strtolower($given) === mb_strtolower($correctValue);
            if (! $isCorrectAnswer) {
                $correct = false;
                $explanations[$ans->marker] = $gpt->explainWrongAnswer($question->question, $given, $correctValue);
            }
            $class = $isCorrectAnswer ? 'text-green-700 font-bold' : 'text-red-700 font-bold';
            $replacement = '<span class="' . $class . '">' . e($given) . '</span>';
            $sentenceHtml = str_replace('{' . $ans->marker . '}', $replacement, $sentenceHtml);
        }
        $stats = session($key . '_stats', ['correct' => 0, 'wrong' => 0, 'total' => 0]);
        $answered = session($key . '_answered', []);
        $prev = $answered[$question->id] ?? null;

        if ($prev === null) {
            $stats['total']++;
            if ($correct) {
                $stats['correct']++;
                $answered[$question->id] = 'correct';
            } else {
                $stats['wrong']++;
                $answered[$question->id] = 'wrong';
            }
        } elseif ($prev === 'wrong' && $correct) {
            $stats['wrong']--;
            $stats['correct']++;
            $answered[$question->id] = 'correct';
        } elseif ($prev === 'correct' && ! $correct) {
            $stats['correct']--;
            $stats['wrong']++;
            $answered[$question->id] = 'wrong';
        }
        session([
            $key . '_stats' => $stats,
            $key . '_answered' => $answered,
            $key . '_feedback' => [
                'isCorrect' => $correct,
                'explanations' => $explanations,
                'answers' => $givenAnswers,
                'answer_sentence' => $sentenceHtml,
            ],
        ]);
        if($correct){
            return redirect()->route('saved-test.step', [
                'slug' => $slug,
                'nav'  => 'next',
            ]);
        }else{
            return redirect()->route('saved-test.step', $slug);    
        }    
       
    }

    public function determineTense(Request $request, string $slug)
    {
        return $this->handleDetermineTense($request, $slug, \App\Services\ChatGPTService::class);
    }

    public function determineTenseGemini(Request $request, string $slug)
    {
        return $this->handleDetermineTense($request, $slug, \App\Services\GeminiService::class);
    }

    /**
     * @param  class-string  $serviceClass
     */
    private function handleDetermineTense(Request $request, string $slug, string $serviceClass)
    {
        $test = Test::where('slug', $slug)->firstOrFail();

        $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
        ]);

        $question = Question::with(['answers.option'])->findOrFail($request->integer('question_id'));

        // Переконуємось, що питання належить до цього тесту
        $testQuestionIds = Arr::wrap($test->questions); // якщо зкастовано до array — ок
        if (! in_array($question->id, $testQuestionIds, true)) {
            abort(404);
        }

        $this->variantService->applyStoredVariant($slug, $question);

        $questionText = $question->question;

        // Список тегів (категорія "Tenses")
        $tags = Tag::where('category', 'Tenses')->pluck('name')->all();

        // Визначаємо теги через потрібний сервіс
        $service   = app($serviceClass);
        $suggested = $service->determineTenseTags($questionText, $tags);

        return response()->json(['tags' => $suggested]);
    }

    public function determineLevel(Request $request, $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $request->validate([
            'question_id' => 'required|integer',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! in_array($question->id, $test->questions)) {
            abort(404);
        }

        $this->variantService->applyStoredVariant($slug, $question);

        $gpt = app(\App\Services\ChatGPTService::class);
        $level = $gpt->determineDifficulty($question->question);

        return response()->json(['level' => $level]);
    }
 
    public function determineLevelGemini(Request $request, $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $request->validate([
            'question_id' => 'required|integer',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! in_array($question->id, $test->questions)) {
            abort(404);
        }

        $this->variantService->applyStoredVariant($slug, $question);

        $gemini = app(\App\Services\GeminiService::class);
        $level = $gemini->determineDifficulty($question->question);

        return response()->json(['level' => $level]);
    }

    public function setLevel(Request $request, $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $request->validate([
            'question_id' => 'required|integer',
            'level' => 'required|in:A1,A2,B1,B2,C1,C2',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! in_array($question->id, $test->questions)) {
            abort(404);
        }

        $question->level = $request->input('level');
        $question->save();

        return response()->json(['status' => 'ok']);
    }

    public function addTag(Request $request, $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $request->validate([
            'question_id' => 'required|integer',
            'tag' => 'required|string',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! in_array($question->id, $test->questions)) {
            abort(404);
        }

        $tag = Tag::where('name', $request->input('tag'))->first();
        if (! $tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $question->tags()->syncWithoutDetaching([$tag->id]);
        $question->load('tags');

        return response()->json(['tags' => $question->tags->pluck('name')]);
    }

    public function removeTag(Request $request, $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $request->validate([
            'question_id' => 'required|integer',
            'tag' => 'required|string',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! in_array($question->id, $test->questions)) {
            abort(404);
        }

        $tag = Tag::where('name', $request->input('tag'))->first();
        if (! $tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $question->tags()->detach($tag->id);
        $question->load('tags');

        return response()->json(['tags' => $question->tags->pluck('name')]);
    }

    public function resetSavedTestStep($slug)
    {
        $test = \App\Models\Test::where('slug', $slug)->firstOrFail();
        $key = 'step_' . $test->slug;
        session()->forget([
            $key . '_stats',
            $key . '_answered',
            $key . '_queue',
            $key . '_total',
            $key . '_index',
            $key . '_feedback',
        ]);
        return redirect()->route('saved-test.step', $slug);
    }

    public function deleteQuestion($slug, Question $question)
    {
        $test = Test::where('slug', $slug)->firstOrFail();
        $test->questions = array_values(array_filter(
            $test->questions,
            fn ($id) => (int) $id !== $question->id
        ));
        $test->save();

        $key = 'step_' . $test->slug;
        $queue = session($key . '_queue', []);
        $index = session($key . '_index', 0);
        $removedIndex = array_search($question->id, $queue, true);
        $queue = array_values(array_filter($queue, fn ($id) => (int) $id !== $question->id));
        if ($removedIndex !== false && $removedIndex < $index) {
            $index = max($index - 1, 0);
        } elseif ($removedIndex !== false && $removedIndex === $index) {
            $index = min($index, max(count($queue) - 1, 0));
        }
        session([$key . '_queue' => $queue, $key . '_index' => $index]);
        if (session()->has($key . '_total')) {
            session([$key . '_total' => max(session($key . '_total') - 1, 0)]);
        }

        return redirect()->back();
    }

    public function addQuestion(Request $request, string $slug)
    {
        $test = Test::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
        ]);

        $questionId = (int) $data['question_id'];
        $questions = Arr::wrap($test->questions);

        if (in_array($questionId, $questions, true)) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Питання вже додано до цього тесту.',
                ], 422);
            }

            return redirect()->back()->withErrors([
                'question_id' => 'Питання вже додано до цього тесту.',
            ]);
        }

        $questions[] = $questionId;
        $test->questions = array_values($questions);
        $test->save();

        $key = 'step_' . $test->slug;
        $queue = Arr::wrap(session($key . '_queue', []));
        if (! in_array($questionId, $queue, true)) {
            $queue[] = $questionId;
            session([$key . '_queue' => $queue]);
        }

        if (session()->has($key . '_total')) {
            session([$key . '_total' => session($key . '_total', 0) + 1]);
        }

        $question = Question::findOrFail($questionId);

        if ($request->wantsJson()) {
            return TechnicalQuestionResource::make($question);
        }

        return redirect()->back();
    }

    public function show(Request $request)
    {
        $categories = Category::all();
        $minDifficulty = 1;
        $maxDifficulty = 10;
        $maxQuestions = 999;

        // Значення по замовчуванню, якщо GET
        $selectedCategories = [];
        $difficultyFrom = $minDifficulty;
        $difficultyTo = $maxDifficulty;
        $numQuestions = 10;
        $manualInput = false;
        $autocompleteInput = false;
        $checkOneInput = false;
        $builderInput = false;
        $includeAi = false;
        $onlyAi = false;
        $includeAiV2 = false;
        $onlyAiV2 = false;
        $questions = [];

        return view('grammar-test', compact(
            'categories', 'minDifficulty', 'maxDifficulty', 'maxQuestions',
            'selectedCategories', 'difficultyFrom', 'difficultyTo', 'numQuestions',
            'manualInput', 'autocompleteInput', 'checkOneInput', 'questions', 'builderInput',
            'includeAi', 'onlyAi', 'includeAiV2', 'onlyAiV2'
        ));
    }

    public function generate(Request $request)
    {
        $categories = \App\Models\Category::all();
        $minDifficulty = 1;
        $maxDifficulty = 10;
        $maxQuestions = 999999;
    
        $selectedCategories = $request->input('categories', []);
        $difficultyFrom = $request->input('difficulty_from', $minDifficulty);
        $difficultyTo = $request->input('difficulty_to', $maxDifficulty);
        $numQuestions = min($request->input('num_questions', 10), $maxQuestions);
    
        $manualInput = $request->boolean('manual_input');
        $autocompleteInput = $request->boolean('autocomplete_input');
        $checkOneInput = $request->boolean('check_one_input');
        $builderInput = $request->boolean('builder_input');
        $includeAi = $request->boolean('include_ai');
        $onlyAi = $request->boolean('only_ai');
        $includeAiV2 = $request->boolean('include_ai_v2');
        $onlyAiV2 = $request->boolean('only_ai_v2');
        $selectedTags = $request->input('tags', []);
        $selectedLevels = (array) $request->input('levels', []);
    
        // MULTI-SOURCE support
        $selectedSources = $request->input('sources', []); // array of source IDs

        // Групування: якщо вибрано sources — по source_id, інакше по категоріях
        $groupBy = !empty($selectedSources) ? 'source_id' : 'category_id';

        if ($groupBy === 'source_id') {
            $groups = $selectedSources;
        } else {
            $groups = !empty($selectedCategories) ? $selectedCategories : $categories->pluck('id')->toArray();
        }
        $groups = array_values($groups);
    
        // Розрахунок питань на групу
        $questionsPerGroup = floor($numQuestions / count($groups));
        $remaining = $numQuestions % count($groups);
    
        $questions = collect();
    
        foreach ($groups as $i => $group) {
            $take = $questionsPerGroup + ($remaining > 0 ? 1 : 0);
            if ($remaining > 0) $remaining--;
    
            $query = \App\Models\Question::with(['category', 'answers.option', 'options', 'verbHints.option', 'source'])
                ->whereBetween('difficulty', [$difficultyFrom, $difficultyTo]);
            if (!empty($selectedLevels)) {
                $query->whereIn('level', $selectedLevels);
            }

            if ($groupBy === 'source_id') {
                $query->where('source_id', $group);
            } else {
                $query->where('category_id', $group);
            }

            if (!empty($selectedSources) && $groupBy !== 'source_id') {
                $query->whereIn('source_id', $selectedSources);
            }
            if (!empty($selectedCategories) && $groupBy !== 'category_id') {
                $query->whereIn('category_id', $selectedCategories);
            }
            if (!empty($selectedTags)) {
                $query->whereHas('tags', fn ($q) => $q->whereIn('name', $selectedTags));
            }
    
            // AI-фільтри
            $onlyFlags = [];
            if ($onlyAi) {
                $onlyFlags[] = 1;
            }
            if ($onlyAiV2) {
                $onlyFlags[] = 2;
            }

            if (!empty($onlyFlags)) {
                if (count($onlyFlags) === 1) {
                    $query->where('flag', $onlyFlags[0]);
                } else {
                    $query->whereIn('flag', $onlyFlags);
                }
            } else {
                $allowedFlags = [0];
                if ($includeAi) {
                    $allowedFlags[] = 1;
                }
                if ($includeAiV2) {
                    $allowedFlags[] = 2;
                }

                $allowedFlags = array_values(array_unique($allowedFlags));

                if (count($allowedFlags) === 1) {
                    $query->where('flag', $allowedFlags[0]);
                } elseif (count($allowedFlags) < 3) {
                    $query->whereIn('flag', $allowedFlags);
                }
            }

            $questions = $questions->merge($query->orderBy('id')->limit($take)->get());
        }

        // Відсортувати питання за ID, щоб зберегти порядок із сидера
        $questions = $questions->sortBy('id')->values();
    
        // Автоматичне ім'я тесту
       // $sourcesForName = collect($questions)->pluck('source.name')->filter()->unique()->values();
       // if ($sourcesForName->count() > 0) {
       //     $autoTestName = $sourcesForName->join(', ');
      //  } else {
            $categoryNames = collect($questions)->pluck('category.name')->unique()->values();
            $autoTestName = ucwords($categoryNames->join(' - '));
      //  }
    
        // Для фільтра — всі унікальні source
        $sources = Source::orderBy('name')->get();
        // Show only tags that have at least one question assigned
        $allTags = \App\Models\Tag::whereHas('questions')->get();
        $order = array_flip(['A1','A2','B1','B2','C1','C2']);
        $levels = Question::select('level')->distinct()->pluck('level')
            ->filter()
            ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
            ->values();

        return view('grammar-test', compact(
            'categories', 'minDifficulty', 'maxDifficulty', 'maxQuestions',
            'selectedCategories', 'difficultyFrom', 'difficultyTo', 'numQuestions',
            'manualInput', 'autocompleteInput', 'checkOneInput', 'builderInput',
            'includeAi', 'onlyAi', 'includeAiV2', 'onlyAiV2', 'questions',
            'sources', 'selectedSources', 'autoTestName',
            'allTags', 'selectedTags', 'levels', 'selectedLevels'
        ));
    }
    
    public function destroy(\App\Models\Test $test)
    {
        $test->delete();
        return redirect()->route('saved-tests.list')->with('success', 'Тест видалено!');
    }

    // AJAX-предиктивний пошук
    public function autocomplete(Request $request)
    {
        $q = $request->input('q', '');
        $words = \App\Models\Word::where('word', 'like', $q . '%')
            ->orderByRaw('word LIKE ? DESC', [$q . '%'])
            ->orderBy('word')
            ->limit(5)
            ->pluck('word');

        return response()->json($words);
    }
    

    // AJAX перевірка ВСЬОГО питання (Check answer)
    public function checkOneAnswer(Request $request)
    {
        $questionId = $request->input('question_id');
        $answers = $request->input('answers', []);
        $question = \App\Models\Question::with('answers.option')->find($questionId);
        if(!$question) {
            return response()->json(['result' => 'not_found']);
        }
        $allCorrect = true;
        $correctArr = [];
        foreach ($answers as $marker => $answer) {
            $answerRow = $question->answers->where('marker', $marker)->first();
            $correctValue = $answerRow?->option?->option ?? '';
            $correctArr[$marker] = $correctValue;
            if(!$answerRow || mb_strtolower(trim($answer)) !== mb_strtolower($correctValue)) {
                $allCorrect = false;
            }
        }
    
      
        return response()->json([
            'result' => $allCorrect ? 'correct' : 'incorrect',
            'correct' => $correctArr,
        ]);
    }

    public function index()
    {
        $categories = Category::all();
        $minDifficulty = Question::min('difficulty') ?? 1;
        $maxDifficulty = Question::max('difficulty') ?? 10;
        $maxQuestions = Question::count();
        // Show only tags that have at least one question assigned
        $allTags = \App\Models\Tag::whereHas('questions')->get();
        $selectedTags = [];
        $order = array_flip(['A1','A2','B1','B2','C1','C2']);
        $levels = Question::select('level')->distinct()->pluck('level')
            ->filter()
            ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
            ->values();

        return view('grammar-test', [
            'categories' => $categories,
            'minDifficulty' => $minDifficulty,
            'maxDifficulty' => $maxDifficulty,
            'maxQuestions' => $maxQuestions,
            'allTags' => $allTags,
            'selectedTags' => $selectedTags,
            'levels' => $levels,
            'selectedLevels' => [],
        ]);
    }

    public function check(Request $request)
    {
      
        $slug = $request->input('test_slug');
        $questions = Question::with(['answers.option', 'category'])->whereIn('id', array_keys($request->get('questions', [])))->get();
        $results = [];
        $gpt = app(\App\Services\ChatGPTService::class);

        foreach ($questions as $question) {
            $this->variantService->applyStoredVariant($slug, $question);
            $correctCount = 0;
            $total = $question->answers->count();
            $userAnswers = [];
            $explanations = [];

            foreach ($question->answers as $ans) {
                $inputName = "question_{$question->id}_{$ans->marker}";
                $userAnswer = $request->input($inputName, '');
                if (is_array($userAnswer)) {
                    $userAnswer = implode(' ', $userAnswer);
                }
                $userAnswers[$ans->marker] = $userAnswer;
                $correctValue = $ans->option->option;
                if (strtolower(trim($userAnswer)) === strtolower($correctValue)) {
                    $correctCount++;
                } else {
                    $explanations[$ans->marker] = $gpt->explainWrongAnswer($question->question, $userAnswer, $correctValue);
                }
            }

            $results[] = [
                'question' => $question,
                'user_answers' => $userAnswers,
                'is_correct' => ($correctCount === $total),
                'correct_answers' => $question->answers->mapWithKeys(fn($a) => [$a->marker => $a->option->option]),
                'explanations' => $explanations,
            ];
        }

        return view('grammar-test-result', compact('results'));
    }

    public function list()
    {
        $tests = \App\Models\Test::latest()->paginate(20); // пагінація, якщо тестів багато
        return view('saved-tests', compact('tests'));
    }

    public function catalog(Request $request)
    {
        $selectedTags = (array) $request->input('tags', []);
        $selectedLevels = (array) $request->input('levels', []);

        $tests = \App\Models\Test::latest()->get();

        $allQuestionIds = collect($tests)->flatMap(fn($t) => $t->questions)->unique();
        $questions = Question::with('tags')->whereIn('id', $allQuestionIds)->get()->keyBy('id');

        $order = array_flip(['A1','A2','B1','B2','C1','C2']);
        foreach ($tests as $test) {
            $testQuestions = collect($test->questions)->map(fn($id) => $questions[$id] ?? null)->filter();
            $tagNames = $testQuestions->flatMap(fn($q) => $q->tags->pluck('name'));
            $test->tag_names = $tagNames->unique()->values();
            $test->levels = $testQuestions->pluck('level')->unique()
                ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
                ->values();
        }

        $availableTags = $tests->flatMap(fn($t) => $t->tag_names)->unique()->values();
        $availableLevels = $tests->flatMap(fn($t) => $t->levels)->unique()
            ->filter()
            ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
            ->values();

        $tagModels = Tag::whereIn('name', $availableTags)->get();
        $tagsByCategory = $tagModels->groupBy(fn($t) => $t->category ?? 'Other')
            ->map(fn($group) => $group->pluck('name')->sort()->values());

        $tagsByCategory = $tagsByCategory->sortKeys();
        if ($tagsByCategory->has('Tenses')) {
            $tenses = $tagsByCategory->pull('Tenses');
            $tagsByCategory = $tagsByCategory->prepend($tenses, 'Tenses');
        }
        if ($tagsByCategory->has('Other')) {
            $other = $tagsByCategory->pull('Other');
            $tagsByCategory->put('Other', $other);
        }

        if (!empty($selectedTags)) {
            $tests = $tests->filter(function ($t) use ($selectedTags) {
                return collect($selectedTags)
                    ->every(fn($tag) => $t->tag_names->contains($tag));
            })->values();
        }
        if (!empty($selectedLevels)) {
            $tests = $tests->filter(function ($t) use ($selectedLevels) {
                return collect($selectedLevels)->every(fn($lvl) => $t->levels->contains($lvl));
            })->values();
        }

        $view = $request->routeIs('catalog-tests.cards')
            ? 'catalog-tests-cards'
            : 'saved-tests-cards';

        return view('engram.' . $view, [
            'tests' => $tests,
            'tags' => $tagsByCategory,
            'selectedTags' => $selectedTags,
            'availableLevels' => $availableLevels,
            'selectedLevels' => $selectedLevels,
            'breadcrumbs' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'Tests Catalog'],
            ],
        ]);
    }
    
}
