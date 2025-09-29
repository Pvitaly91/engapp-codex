<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\TechnicalQuestionResource;
use App\Models\Question;
use App\Models\Category;
use Illuminate\Support\Arr;
use App\Models\Source;
use Illuminate\Support\Str;
use App\Models\Test;
use App\Models\Tag;
use App\Models\SavedGrammarTest;
use App\Models\ChatGPTExplanation;
use App\Services\QuestionVariantService;
use App\Services\ResolvedSavedTest;
use App\Services\SavedTestResolver;
use App\Models\QuestionHint;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\VerbHint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

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

    public function __construct(
        private QuestionVariantService $variantService,
        private SavedTestResolver $savedTestResolver,
    )
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
        while ($this->slugExists($slug)) {
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

    public function saveV2(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'filters' => 'required|string',
            'question_uuids' => 'required|string',
        ]);

        $filters = json_decode(html_entity_decode($request->input('filters')), true);
        $questionUuids = json_decode(html_entity_decode($request->input('question_uuids')), true);

        if (! is_array($questionUuids)) {
            $questionUuids = [];
        }

        $questionUuids = collect($questionUuids)
            ->filter(fn ($uuid) => is_string($uuid) && $uuid !== '')
            ->unique()
            ->values();

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $i = 1;
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $i++;
        }

        $test = SavedGrammarTest::create([
            'uuid' => (string) Str::uuid(),
            'name' => $request->name,
            'slug' => $slug,
            'filters' => is_array($filters) ? $filters : [],
        ]);

        $questionUuids->each(function ($uuid, $index) use ($test) {
            $test->questionLinks()->create([
                'question_uuid' => $uuid,
                'position' => $index,
            ]);
        });

        return redirect()->route('saved-test.show', $slug);
    }

    public function showSavedTest($slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;

        $supportsVariants = $this->variantService->supportsVariants();
        $relations = ['category', 'answers.option', 'options', 'verbHints.option', 'tags'];
        if ($supportsVariants) {
            $relations[] = 'variants';
        }

        $questions = $this->savedTestResolver->loadQuestions($resolved, $relations);

        if ($supportsVariants) {
            $previousVariants = $this->variantService->getStoredVariants($test->slug);
            $this->variantService->clearForTest($test->slug);
            $questions = $this->variantService->applyRandomVariants($test, $questions, $previousVariants);
        }

        $filters = $this->filtersFromTest($test);
        $manualInput = !empty($filters['manual_input']);
        $autocompleteInput = !empty($filters['autocomplete_input']);
        $builderInput = !empty($filters['builder_input']);

        return view('saved-test', [
            'test' => $test,
            'questions' => $questions,
            'manualInput' => $manualInput,
            'autocompleteInput' => $autocompleteInput,
            'builderInput' => $builderInput,
            'usesUuidLinks' => $resolved->usesUuidLinks,
        ]);
    }

    public function showSavedTestTech(string $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $supportsVariants = $this->variantService->supportsVariants();

        $relations = ['answers.option', 'options', 'verbHints.option', 'hints'];
        if ($supportsVariants) {
            $relations[] = 'variants';
        }

        $questions = $this->savedTestResolver->loadQuestions($resolved, $relations);

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

        $hintProviders = [];

        if (Schema::hasTable('question_hints')) {
            $hintProviders = QuestionHint::query()
                ->select('provider')
                ->distinct()
                ->orderBy('provider')
                ->pluck('provider')
                ->filter(fn ($provider) => is_string($provider) && trim($provider) !== '')
                ->values()
                ->all();
        }

        return view('engram.saved-test-tech', [
            'test' => $test,
            'questions' => $questions,
            'explanationsByQuestionId' => $explanationsByQuestionId,
            'hintProviders' => $hintProviders,
            'usesUuidLinks' => $resolved->usesUuidLinks,
        ]);
    }

    public function storeSavedTestQuestion(Request $request, string $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;

        $data = $request->validate([
            'question' => ['required', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        if (! preg_match_all('/\{a\d+\}/i', $data['question'], $matches)) {
            throw ValidationException::withMessages([
                'question' => 'Питання повинно містити хоча б одну позначку {a1}.',
            ]);
        }

        $categoryId = $data['category_id'] ?? null;

        if (! $categoryId) {
            $existingQuestionId = $resolved->questionIds->first();

            if ($existingQuestionId) {
                $categoryId = Question::query()
                    ->whereKey($existingQuestionId)
                    ->value('category_id');
            }
        }

        if (! $categoryId) {
            $categoryId = Category::query()->value('id');
        }

        if (! $categoryId) {
            throw ValidationException::withMessages([
                'category_id' => 'Не вдалося визначити категорію для створення питання.',
            ]);
        }

        $question = Question::create([
            'uuid' => (string) Str::uuid(),
            'question' => $data['question'],
            'difficulty' => 1,
            'category_id' => $categoryId,
            'flag' => 0,
        ]);

        if ($resolved->usesUuidLinks) {
            $position = $resolved->questionUuids->count();
            $test->questionLinks()->create([
                'question_uuid' => $question->uuid,
                'position' => $position,
            ]);
        } else {
            $testQuestionIds = $resolved->questionIds
                ->push($question->id)
                ->filter(fn ($id) => filled($id))
                ->unique()
                ->values()
                ->all();

            $test->update(['questions' => $testQuestionIds]);
        }

        return TechnicalQuestionResource::make($question)->response()->setStatusCode(201);
    }

    public function showSavedTestRandom($slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $supportsVariants = $this->variantService->supportsVariants();
        $relations = ['category', 'answers.option', 'options', 'verbHints.option', 'tags'];
        if ($supportsVariants) {
            $relations[] = 'variants';
        }

        $questions = $this->savedTestResolver->loadQuestions($resolved, $relations);

        if ($supportsVariants) {
            $previousVariants = $this->variantService->getStoredVariants($test->slug);
            $this->variantService->clearForTest($test->slug);
            $questions = $this->variantService->applyRandomVariants($test, $questions, $previousVariants);
        }

        return view('saved-test-random', [
            'test' => $test,
            'questions' => $questions,
            'usesUuidLinks' => $resolved->usesUuidLinks,
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
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $stateKey = $this->jsStateSessionKey($test, $view);
        $savedState = session($stateKey);
        $questions = $this->buildQuestionDataset($resolved, empty($savedState));

        return view("engram.$view", [
            'test' => $test,
            'questionData' => $questions,
            'jsStateMode' => $view,
            'savedState' => $savedState,
            'usesUuidLinks' => $resolved->usesUuidLinks,
        ]);
    }

    public function fetchSavedTestJsQuestions(Request $request, string $slug)
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

    public function storeSavedTestJsState(Request $request, string $slug)
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

    private function buildQuestionDataset(ResolvedSavedTest $resolved, bool $freshVariants = false)
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

    private function jsStateSessionKey(Test|SavedGrammarTest $test, string $view): string
    {
        return sprintf('saved_test_js_state:%s:%s', $test->slug, $view);
    }

    public function showSavedTestStep(Request $request, $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;

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
            $queue = $resolved->questionIds->toArray();
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
                'usesUuidLinks' => $resolved->usesUuidLinks,
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

        $questionNumber = array_search($currentId, $resolved->questionIds->toArray(), true);
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
            'usesUuidLinks' => $resolved->usesUuidLinks,
        ]);
    }

    public function refreshDescription($slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $questions = Question::whereIn('id', $resolved->questionIds)->pluck('question');
        $gpt = app(\App\Services\ChatGPTService::class);
        $test->description = $gpt->generateTestDescription($questions->toArray());
        $test->save();

        return redirect()->back();
    }

    public function refreshDescriptionGemini($slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $questions = Question::whereIn('id', $resolved->questionIds)->pluck('question');
        $gemini = app(\App\Services\GeminiService::class);
        $test->description = $gemini->generateTestDescription($questions->toArray());
        $test->save();

        return redirect()->back();
    }

    public function checkSavedTestStep(Request $request, $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $key = 'step_' . $test->slug;
        $request->validate([
            'question_id' => 'required|integer',
            'answers' => 'required|array',
        ]);
        $question = \App\Models\Question::with('answers.option')->findOrFail($request->input('question_id'));

        if (! $resolved->questionIds->contains($question->id)) {
            abort(404);
        }

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
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;

        $request->validate([
            'question_id' => ['required', 'integer', 'exists:questions,id'],
        ]);

        $question = Question::with(['answers.option'])->findOrFail($request->integer('question_id'));

        if (! $resolved->questionIds->contains($question->id)) {
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
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $request->validate([
            'question_id' => 'required|integer',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! $resolved->questionIds->contains($question->id)) {
            abort(404);
        }

        $this->variantService->applyStoredVariant($slug, $question);

        $gpt = app(\App\Services\ChatGPTService::class);
        $level = $gpt->determineDifficulty($question->question);

        return response()->json(['level' => $level]);
    }
 
    public function determineLevelGemini(Request $request, $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $request->validate([
            'question_id' => 'required|integer',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! $resolved->questionIds->contains($question->id)) {
            abort(404);
        }

        $this->variantService->applyStoredVariant($slug, $question);

        $gemini = app(\App\Services\GeminiService::class);
        $level = $gemini->determineDifficulty($question->question);

        return response()->json(['level' => $level]);
    }

    public function setLevel(Request $request, $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $request->validate([
            'question_id' => 'required|integer',
            'level' => 'required|in:A1,A2,B1,B2,C1,C2',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! $resolved->questionIds->contains($question->id)) {
            abort(404);
        }

        $question->level = $request->input('level');
        $question->save();

        return response()->json(['status' => 'ok']);
    }

    public function addTag(Request $request, $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $request->validate([
            'question_id' => 'required|integer',
            'tag' => 'required|string',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! $resolved->questionIds->contains($question->id)) {
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
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
        $request->validate([
            'question_id' => 'required|integer',
            'tag' => 'required|string',
        ]);

        $question = Question::findOrFail($request->input('question_id'));
        if (! $resolved->questionIds->contains($question->id)) {
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
        $resolved = $this->savedTestResolver->resolve($slug);
        $test = $resolved->model;
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

    public function deleteQuestion(Request $request, $slug, Question $question)
    {
        $resolved = $this->savedTestResolver->resolve($slug);

        if (! $resolved->questionIds->contains($question->id)) {
            abort(404);
        }

        $this->performQuestionDeletion($resolved, $question);
        $this->removeQuestionFromSessionQueue($resolved->model, $question->id);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->back();
    }

    public function deleteAllQuestions(Request $request, $slug)
    {
        $resolved = $this->savedTestResolver->resolve($slug);

        if ($resolved->questionIds->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'status' => 'ok',
                    'deleted_ids' => [],
                    'message' => 'Немає питань для видалення.',
                ]);
            }

            return redirect()->back();
        }

        $questions = Question::whereIn('id', $resolved->questionIds)->get();
        $deletedIds = [];

        foreach ($questions as $question) {
            $this->performQuestionDeletion($resolved, $question);
            $this->removeQuestionFromSessionQueue($resolved->model, $question->id);
            $deletedIds[] = $question->id;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'deleted_ids' => $deletedIds,
                'message' => 'Усі питання видалено.',
            ]);
        }

        return redirect()->back();
    }

    private function performQuestionDeletion(ResolvedSavedTest $resolved, Question $question): void
    {
        $test = $resolved->model;
        $deletedQuestionUuid = (string) $question->uuid;
        $categoryId = $question->category_id;
        $sourceId = $question->source_id;

        DB::transaction(function () use ($test, $question, $resolved) {
            if ($resolved->usesUuidLinks) {
                $test->questionLinks()
                    ->where('question_uuid', $question->uuid)
                    ->delete();

                $links = $test->questionLinks()->orderBy('position')->get();
                foreach ($links as $index => $link) {
                    if ($link->position !== $index) {
                        $link->update(['position' => $index]);
                    }
                }
            } else {
                $test->questions = array_values(array_filter(
                    Arr::wrap($test->questions),
                    fn ($id) => (int) $id !== $question->id
                ));
                $test->save();
            }

            $optionIds = collect();

            if (Schema::hasTable('question_option_question')) {
                $optionIds = $optionIds->merge(
                    DB::table('question_option_question')
                        ->where('question_id', $question->id)
                        ->pluck('option_id')
                );

                DB::table('question_option_question')
                    ->where('question_id', $question->id)
                    ->delete();
            }

            if (Schema::hasTable('question_answers')) {
                $optionIds = $optionIds->merge(
                    QuestionAnswer::query()
                        ->where('question_id', $question->id)
                        ->pluck('option_id')
                );

                QuestionAnswer::query()
                    ->where('question_id', $question->id)
                    ->delete();
            }

            if (Schema::hasTable('verb_hints')) {
                $optionIds = $optionIds->merge(
                    VerbHint::query()
                        ->where('question_id', $question->id)
                        ->pluck('option_id')
                );

                VerbHint::query()
                    ->where('question_id', $question->id)
                    ->delete();
            }

            if (Schema::hasTable('question_hints')) {
                QuestionHint::query()
                    ->where('question_id', $question->id)
                    ->delete();
            }

            if (Schema::hasTable('question_variants')) {
                QuestionVariant::query()
                    ->where('question_id', $question->id)
                    ->delete();
            }

            $questionText = trim((string) $question->question);
            $hasDuplicateText = $questionText !== '' && Question::query()
                ->where('id', '!=', $question->id)
                ->where('question', $questionText)
                ->exists();

            $question->tags()->detach();
            $question->delete();

            if ($questionText !== '' && Schema::hasTable('chatgpt_explanations') && ! $hasDuplicateText) {
                ChatGPTExplanation::query()
                    ->where('question', $questionText)
                    ->delete();
            }

            $optionIds->filter()->unique()->each(function ($optionId) {
                $stillUsed = (Schema::hasTable('question_answers')
                        && QuestionAnswer::query()->where('option_id', $optionId)->exists())
                    || (Schema::hasTable('verb_hints')
                        && VerbHint::query()->where('option_id', $optionId)->exists())
                    || (Schema::hasTable('question_option_question')
                        && DB::table('question_option_question')->where('option_id', $optionId)->exists());

                if (! $stillUsed) {
                    QuestionOption::query()->where('id', $optionId)->delete();
                }
            });
        });

        $this->pruneUnusedCategoryAndSource($categoryId, $sourceId);

        if ($deletedQuestionUuid !== '') {
            $this->appendDeletedQuestionUuid($deletedQuestionUuid);

            $resolved->questionUuids = $resolved->questionUuids
                ->filter(fn ($uuid) => $uuid !== $deletedQuestionUuid)
                ->values();
        }

        $resolved->questionIds = $resolved->questionIds
            ->filter(fn ($id) => (int) $id !== $question->id)
            ->values();
    }

    private function removeQuestionFromSessionQueue(Model $test, int $questionId): void
    {
        $key = 'step_' . $test->slug;
        $queue = session($key . '_queue', []);
        $index = session($key . '_index', 0);
        $removedIndex = array_search($questionId, $queue, true);
        $queue = array_values(array_filter($queue, fn ($id) => (int) $id !== $questionId));

        if ($removedIndex !== false && $removedIndex < $index) {
            $index = max($index - 1, 0);
        } elseif ($removedIndex !== false && $removedIndex === $index) {
            $index = min($index, max(count($queue) - 1, 0));
        }

        session([$key . '_queue' => $queue, $key . '_index' => $index]);

        if (session()->has($key . '_total')) {
            $currentTotal = (int) session($key . '_total');
            session([$key . '_total' => max($currentTotal - 1, 0)]);
        }
    }

    private function appendDeletedQuestionUuid(string $uuid): void
    {
        if ($uuid === '' || app()->runningInConsole()) {
            return;
        }

        $path = database_path('seeders/questions/deleted-questions.json');

        File::ensureDirectoryExists(dirname($path));

        $existing = [];
        if (File::exists($path)) {
            $decoded = json_decode(File::get($path), true);
            if (is_array($decoded)) {
                $existing = array_values(array_filter($decoded, fn ($value) => is_string($value) && $value !== ''));
            }
        }

        if (in_array($uuid, $existing, true)) {
            return;
        }

        $existing[] = $uuid;

        File::put(
            $path,
            json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );
    }

    private function pruneUnusedCategoryAndSource(?int $categoryId, ?int $sourceId): void
    {
        if ($categoryId && Schema::hasTable('categories')) {
            $hasQuestions = Question::query()->where('category_id', $categoryId)->exists();

            if (! $hasQuestions) {
                Category::query()->whereKey($categoryId)->delete();
            }
        }

        if ($sourceId && Schema::hasTable('sources')) {
            $hasQuestions = Question::query()->where('source_id', $sourceId)->exists();

            if (! $hasQuestions) {
                Source::query()->whereKey($sourceId)->delete();
            }
        }
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
        $data = $this->prepareGenerateData($request);

        return view('grammar-test', array_merge($data, $this->legacyBuilderConfig()));
    }

    public function generateV2(Request $request)
    {
        $data = $this->prepareGenerateData($request);

        return view('grammar-test-v2', array_merge(
            $data,
            $this->uuidBuilderConfig(),
            $this->uuidFormExtras()
        ));
    }
    
    public function destroy(string $slug)
    {
        if ($legacy = Test::where('slug', $slug)->first()) {
            $legacy->delete();

            return redirect()->route('saved-tests.list')->with('success', 'Тест видалено!');
        }

        if ($saved = SavedGrammarTest::where('slug', $slug)->first()) {
            $saved->delete();

            return redirect()->route('saved-tests.list')->with('success', 'Тест видалено!');
        }

        abort(404);
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
        return view('grammar-test', array_merge($this->defaultFormState(), $this->legacyBuilderConfig()));
    }

    public function indexV2()
    {
        return view('grammar-test-v2', array_merge(
            $this->defaultFormState(),
            $this->uuidBuilderConfig(),
            $this->uuidFormExtras()
        ));
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

    public function list(Request $request)
    {
        $tests = $this->paginateSavedTests($request);

        return view('saved-tests', ['tests' => $tests]);
    }

    public function catalog(Request $request)
    {
        $selectedTags = (array) $request->input('tags', []);
        $selectedLevels = (array) $request->input('levels', []);

        $tests = $this->allSavedTests();

        $allQuestionIds = $tests->flatMap(fn($t) => $t->question_ids ?? [])->unique();
        $questions = Question::with('tags')->whereIn('id', $allQuestionIds)->get()->keyBy('id');

        $order = array_flip(['A1','A2','B1','B2','C1','C2']);
        foreach ($tests as $test) {
            $questionIds = collect($test->question_ids ?? []);
            $testQuestions = $questionIds->map(fn($id) => $questions[$id] ?? null)->filter();
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

    private function defaultFormState(): array
    {
        $base = $this->baseFormData();

        return array_merge($base, [
            'selectedCategories' => [],
            'difficultyFrom' => $base['minDifficulty'],
            'difficultyTo' => $base['maxDifficulty'],
            'numQuestions' => 10,
            'manualInput' => false,
            'autocompleteInput' => false,
            'checkOneInput' => false,
            'builderInput' => false,
            'includeAi' => false,
            'onlyAi' => false,
            'includeAiV2' => false,
            'onlyAiV2' => false,
            'questions' => collect(),
            'selectedTags' => [],
            'selectedLevels' => [],
            'selectedSources' => [],
            'sources' => Source::orderBy('name')->get(),
            'autoTestName' => '',
            'randomizeFiltered' => false,
            'canRandomizeFiltered' => false,
        ]);
    }

    private function baseFormData(): array
    {
        $categories = Category::all();
        $minDifficulty = Question::min('difficulty') ?? 1;
        $maxDifficulty = Question::max('difficulty') ?? 10;
        $maxQuestions = Question::count();
        $allTags = Tag::whereHas('questions')->get();
        $order = array_flip(['A1','A2','B1','B2','C1','C2']);
        $levels = Question::select('level')->distinct()->pluck('level')
            ->filter()
            ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
            ->values();

        return [
            'categories' => $categories,
            'minDifficulty' => $minDifficulty,
            'maxDifficulty' => $maxDifficulty,
            'maxQuestions' => $maxQuestions,
            'allTags' => $allTags,
            'levels' => $levels,
        ];
    }

    private function legacyBuilderConfig(): array
    {
        return [
            'generateRoute' => route('grammar-test.generate'),
            'saveRoute' => route('grammar-test.save'),
            'savePayloadField' => 'questions',
            'savePayloadKey' => 'id',
            'builderVersion' => 'legacy',
        ];
    }

    private function uuidBuilderConfig(): array
    {
        return [
            'generateRoute' => route('grammar-test-v2.generate'),
            'saveRoute' => route('grammar-test-v2.save'),
            'savePayloadField' => 'question_uuids',
            'savePayloadKey' => 'uuid',
            'builderVersion' => 'uuid',
        ];
    }

    private function uuidFormExtras(): array
    {
        $tagModels = Tag::whereHas('questions')->orderBy('name')->get();
        $otherLabel = __('Other');
        $tagsByCategory = $tagModels
            ->groupBy(fn ($tag) => $tag->category ?: $otherLabel)
            ->map(fn ($group) => $group->sortBy('name')->values());

        $tagsByCategory = $tagsByCategory->sortKeys();
        if ($tagsByCategory->has('Tenses')) {
            $tenses = $tagsByCategory->pull('Tenses');
            $tagsByCategory = $tagsByCategory->prepend($tenses, 'Tenses');
        }
        if ($tagsByCategory->has($otherLabel)) {
            $other = $tagsByCategory->pull($otherLabel);
            $tagsByCategory->put($otherLabel, $other);
        }

        $categoriesDesc = Category::orderByDesc('id')->get();

        $sourceCategoryPairs = Question::query()
            ->select('source_id', 'category_id')
            ->whereNotNull('source_id')
            ->groupBy('source_id', 'category_id')
            ->get();

        $sourceIds = $sourceCategoryPairs
            ->pluck('source_id')
            ->filter()
            ->unique()
            ->values();

        $sources = $sourceIds->isEmpty()
            ? collect()
            : Source::whereIn('id', $sourceIds)
                ->orderByDesc('id')
                ->get()
                ->keyBy('id');

        $sourcesByCategory = $categoriesDesc
            ->mapWithKeys(function ($category) use ($sourceCategoryPairs, $sources) {
                $sourcesForCategory = $sourceCategoryPairs
                    ->where('category_id', $category->id)
                    ->pluck('source_id')
                    ->filter()
                    ->unique()
                    ->sortDesc()
                    ->map(fn ($id) => $sources->get($id))
                    ->filter()
                    ->values();

                if ($sourcesForCategory->isEmpty()) {
                    return [];
                }

                return [$category->id => [
                    'category' => $category,
                    'sources' => $sourcesForCategory,
                ]];
            })
            ->filter()
            ->sortKeysDesc();

        return [
            'tagsByCategory' => $tagsByCategory,
            'categoriesDesc' => $categoriesDesc,
            'sourcesByCategory' => $sourcesByCategory,
        ];
    }

    private function prepareGenerateData(Request $request): array
    {
        $categories = Category::all();
        $minDifficulty = 1;
        $maxDifficulty = 10;
        $maxQuestions = 999999;

        $selectedCategories = $request->input('categories', []);
        $difficultyFrom = $request->input('difficulty_from', $minDifficulty);
        $difficultyTo = $request->input('difficulty_to', $maxDifficulty);
        $numQuestions = max(1, min($request->input('num_questions', 10), $maxQuestions));

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
        $randomizeFiltered = $request->boolean('randomize_filtered');

        $selectedSources = $request->input('sources', []);
        $groupBy = ! empty($selectedSources) ? 'source_id' : 'category_id';
        $groups = ! empty($selectedCategories) ? $selectedCategories : $categories->pluck('id')->toArray();
        if ($groupBy === 'source_id') {
            $groups = $selectedSources;
        }
        $groups = array_values($groups);
        if (empty($groups)) {
            $groups = [null];
        }

        $groupCount = max(count($groups), 1);
        $questionsPerGroup = floor($numQuestions / $groupCount);
        $remaining = $numQuestions % $groupCount;

        $questions = collect();
        $canRandomizeFiltered = false;

        foreach ($groups as $group) {
            $take = $questionsPerGroup + ($remaining > 0 ? 1 : 0);
            if ($remaining > 0) {
                $remaining--;
            }

            $query = Question::with(['category', 'answers.option', 'options', 'verbHints.option', 'source'])
                ->whereBetween('difficulty', [$difficultyFrom, $difficultyTo]);

            if (! empty($selectedLevels)) {
                $query->whereIn('level', $selectedLevels);
            }

            if ($groupBy === 'source_id') {
                if ($group !== null) {
                    $query->where('source_id', $group);
                }
            } else {
                if ($group !== null) {
                    $query->where('category_id', $group);
                }
            }

            if (! empty($selectedSources) && $groupBy !== 'source_id') {
                $query->whereIn('source_id', $selectedSources);
            }

            if (! empty($selectedCategories) && $groupBy !== 'category_id') {
                $query->whereIn('category_id', $selectedCategories);
            }

            if (! empty($selectedTags)) {
                $query->whereHas('tags', fn ($q) => $q->whereIn('name', $selectedTags));
            }

            $onlyFlags = [];
            if ($onlyAi) {
                $onlyFlags[] = 1;
            }
            if ($onlyAiV2) {
                $onlyFlags[] = 2;
            }

            if (! empty($onlyFlags)) {
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

            $availableCount = (clone $query)->count();

            if ($availableCount > $take && $take > 0) {
                $canRandomizeFiltered = true;
            }

            if ($take > 0) {
                $selectionQuery = clone $query;

                if ($randomizeFiltered && $availableCount > $take) {
                    $selectionQuery->inRandomOrder();
                } else {
                    $selectionQuery->orderBy('id');
                }

                $questions = $questions->merge($selectionQuery->limit($take)->get());
            }
        }

        $questions = $randomizeFiltered
            ? $questions->values()
            : $questions->sortBy('id')->values();

        $categoryNames = $questions->pluck('category.name')->filter()->unique()->values();
        $autoTestName = ucwords($categoryNames->join(' - '));

        $sources = Source::orderBy('name')->get();
        $allTags = Tag::whereHas('questions')->get();
        $order = array_flip(['A1','A2','B1','B2','C1','C2']);
        $levels = Question::select('level')->distinct()->pluck('level')
            ->filter()
            ->sortBy(fn($lvl) => $order[$lvl] ?? 99)
            ->values();

        return [
            'categories' => $categories,
            'minDifficulty' => $minDifficulty,
            'maxDifficulty' => $maxDifficulty,
            'maxQuestions' => $maxQuestions,
            'selectedCategories' => $selectedCategories,
            'difficultyFrom' => $difficultyFrom,
            'difficultyTo' => $difficultyTo,
            'numQuestions' => $numQuestions,
            'manualInput' => $manualInput,
            'autocompleteInput' => $autocompleteInput,
            'checkOneInput' => $checkOneInput,
            'builderInput' => $builderInput,
            'includeAi' => $includeAi,
            'onlyAi' => $onlyAi,
            'includeAiV2' => $includeAiV2,
            'onlyAiV2' => $onlyAiV2,
            'questions' => $questions,
            'sources' => $sources,
            'selectedSources' => $selectedSources,
            'autoTestName' => $autoTestName,
            'allTags' => $allTags,
            'selectedTags' => $selectedTags,
            'levels' => $levels,
            'selectedLevels' => $selectedLevels,
            'randomizeFiltered' => $randomizeFiltered,
            'canRandomizeFiltered' => $canRandomizeFiltered,
        ];
    }

    private function filtersFromTest($test): array
    {
        $filters = $test->filters ?? [];

        if (is_string($filters)) {
            $decoded = json_decode($filters, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($filters) ? $filters : [];
    }

    private function slugExists(string $slug): bool
    {
        return Test::where('slug', $slug)->exists()
            || SavedGrammarTest::where('slug', $slug)->exists();
    }

    private function paginateSavedTests(Request $request): LengthAwarePaginator
    {
        $tests = $this->allSavedTests();
        $perPage = 20;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $slice = $tests->slice(($page - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator($slice, $tests->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    private function allSavedTests(): Collection
    {
        $legacyTests = Test::query()->latest()->get();
        $legacyTests->each(function (Test $test) {
            $questions = collect($test->questions ?? [])
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $test->setAttribute('questions', $questions);
            $test->setAttribute('question_ids', $questions);
            $test->setAttribute('question_count', count($questions));
            $test->setAttribute('test_type', 'legacy');
            $test->setAttribute('usesUuidLinks', false);
        });

        $uuidTests = SavedGrammarTest::query()->with('questionLinks')->latest()->get();
        $allUuids = $uuidTests->flatMap(fn($test) => $test->questionLinks->pluck('question_uuid'))
            ->filter()
            ->unique()
            ->values();
        $idMap = $allUuids->isEmpty()
            ? collect()
            : Question::whereIn('uuid', $allUuids)->pluck('id', 'uuid');

        $uuidTests->each(function (SavedGrammarTest $test) use ($idMap) {
            $uuids = $test->questionLinks->sortBy('position')->pluck('question_uuid')->filter()->values();
            $ids = $uuids->map(fn ($uuid) => isset($idMap[$uuid]) ? (int) $idMap[$uuid] : null)
                ->filter(fn ($id) => $id !== null)
                ->values()
                ->all();

            $test->setAttribute('questions', $ids);
            $test->setAttribute('question_ids', $ids);
            $test->setAttribute('question_uuids', $uuids->toArray());
            $test->setAttribute('question_count', count($ids));
            $test->setAttribute('test_type', 'uuid');
            $test->setAttribute('usesUuidLinks', true);
        });

        return $legacyTests->merge($uuidTests)->sortByDesc('created_at')->values();
    }

}
