<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Source;
use App\Models\Tag;
use App\Models\Word;
use App\Services\ChatGPTService;
use App\Services\GeminiService;
use App\Services\QuestionSeedingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AiTestController extends Controller
{
    public function form()
    {
        $tags = Tag::whereNotNull('category')
            ->whereIn('category', ['Tenses', 'Modals', 'Structures', 'Quantifiers', 'Articles'])
            ->orderBy('category')
            ->get()
            ->groupBy('category');
        // dd($tags);
        $models = ChatGPTService::availableModels();
        $max = DB::table('question_answers')
            ->selectRaw('MAX(CAST(SUBSTRING(marker, 2) AS UNSIGNED)) as max_n')
            ->value('max_n');
        if ($max === null) {
            $max = PHP_INT_MAX;
        }

        return view('ai-test-form', compact('tags', 'models', 'max'));
    }

    public function start(Request $request)
    {

        $max = DB::table('question_answers')
            ->selectRaw('MAX(CAST(SUBSTRING(marker, 2) AS UNSIGNED)) as max_n')
            ->value('max_n');

        $rules = [
            'tags' => 'required|array|min:1',
            'answers_min' => 'required|integer|min:1',
            'answers_max' => 'required|integer|min:1|gte:answers_min',
            'provider' => 'required|in:chatgpt,gemini,mixed',
        ];
        if ($max !== null) {
            $rules['answers_min'] .= '|max:' . $max;
            $rules['answers_max'] .= '|max:' . $max;
        }
        if ($request->input('provider') === 'chatgpt') {
            $rules['model'] = ['required', Rule::in(array_merge(['random'], ChatGPTService::availableModels()))];
        }
        $validated = $request->validate($rules);

        $tagIds = $validated['tags'];
        $topic = Tag::whereIn('id', $tagIds)->pluck('name')->toArray();

        $min = (int) $validated['answers_min'];

        $provider = $validated['provider'];
        $model = $provider === 'chatgpt' ? $validated['model'] : 'random';

        session([
            'ai_step.tags' => $tagIds,
            'ai_step.answers_range' => [$min, $validated['answers_max']],
            'ai_step.stats' => ['correct' => 0, 'wrong' => 0, 'total' => 0],
            'ai_step.current_question' => null,
            'ai_step.next_question' => null,
            'ai_step.next_question_provider' => null,
            'ai_step.feedback' => null,
            'ai_step.last_question' => null,
            'ai_step.topic' => $topic,
            'ai_step.provider' => $provider,
            'ai_step.current_provider' => $provider === 'mixed' ? null : $provider,
            'ai_step.next_provider' => $provider === 'mixed' ? 'gemini' : null,
            'ai_step.model' => $model,
        ]);

        return redirect()->route('ai-test.step');
    }

    public function getRefferance($answersCountStart, $answersCountEnd, &$responseAnswersCount = 0)
    {
        $responseAnswersCount = $answersCountEnd;
        $min = (int) $answersCountStart; // мінімальне N, наприклад 3
        $upper = (int) $answersCountEnd; // верхня межа (можеш зробити < $upper або <= $upper)

        $perQuestionMax = DB::table('question_answers')
            ->select([
                'question_id',
                DB::raw('MAX(CAST(SUBSTR(marker, 2) AS INTEGER)) AS max_n'),
            ])
            ->where('marker', 'like', 'a%')
            ->groupBy('question_id');

        // Вибрати випадковий question_id, у якого власний max_n у потрібному діапазоні:
        // приклад: min <= max_n < upper (строго менше upper)
        $randomRow = DB::query()
            ->fromSub($perQuestionMax, 'qa')
            ->where('qa.max_n', '>=', $min)
            ->where('qa.max_n', '<=', $upper)
            ->inRandomOrder()
            ->first();
        if (! $randomRow) {
            $responseAnswersCount = $answersCountEnd;
            return '';
        }
        $responseAnswersCount = $randomRow->max_n;

        return \App\Models\Question::find($randomRow->question_id)->question;
    }

    public function step(ChatGPTService $gpt, GeminiService $gemini)
    {

        $tagIds = session('ai_step.tags');
        if (! $tagIds) {
            return redirect()->route('ai-test.form');
        }
        $tenseNames = Tag::whereIn('id', $tagIds)->orderBy('category')
            ->get()
            ->groupBy('category')->map(function ($items) {
                return $items->pluck('name')->toArray();
            })
            ->toArray();
        $flatTenses = [];
        foreach ($tenseNames as $arr) {
            $flatTenses = array_merge($flatTenses, $arr);
        }

        $providerSetting = session('ai_step.provider', 'chatgpt');
        $question = session('ai_step.current_question');
        $currentProvider = session('ai_step.current_provider');

        if (! $question) {
            if (session()->has('ai_step.next_question')) {
                $question = session('ai_step.next_question');
                $currentProvider = session('ai_step.next_question_provider', $providerSetting);
                session([
                    'ai_step.current_question' => $question,
                    'ai_step.current_provider' => $currentProvider,
                ]);
                session()->forget(['ai_step.next_question', 'ai_step.next_question_provider']);
            } else {
                if ($providerSetting === 'mixed') {
                    $currentProvider = session('ai_step.next_provider', 'gemini');
                    session([
                        'ai_step.current_provider' => $currentProvider,
                        'ai_step.next_provider' => $currentProvider === 'gemini' ? 'chatgpt' : 'gemini',
                    ]);
                } else {
                    $currentProvider = $providerSetting;
                    session(['ai_step.current_provider' => $providerSetting]);
                }

                $range = session('ai_step.answers_range', [1, 1]);

                $answersCount = random_int($range[0], $range[1]);
                $lastQuestion = session('ai_step.last_question');
                $attempts = 0;
                $responseAnswersCount = $answersCount;
                $refferance = $this->getRefferance($range[0], $range[1], $responseAnswersCount);

                session(['ai_step.refferance' => $refferance]);

                do {
                    if ($currentProvider === 'gemini') {
                        $question = $gemini->generateGrammarQuestion($flatTenses, $answersCount);
                    } else {
                        $model = session('ai_step.model') ?? 'random';
                        $question = $gpt->generateGrammarQuestion($flatTenses, $responseAnswersCount, $model);
                    }
                    $attempts++;
                } while ($question && $lastQuestion && $question['question'] === $lastQuestion && $attempts < 3);
                if ($question) {
                    $this->storeWords($question);
                    session(['ai_step.current_question' => $question]);
                }
            }
        }

        $stats = session('ai_step.stats', ['correct' => 0, 'wrong' => 0, 'total' => 0]);
        $percentage = $stats['total'] > 0 ? round(($stats['correct'] / $stats['total']) * 100, 2) : 0;

        $feedback = session('ai_step.feedback');
        session()->forget('ai_step.feedback');
        $_tenseNames = [];
        foreach ($tenseNames as $array) {
            $_tenseNames = array_merge($_tenseNames, $array);
        }

        return view('ai-test-step', [
            'tenseNames' => $_tenseNames,
            'question' => $question,
            'stats' => $stats,
            'percentage' => $percentage,
            'feedback' => $feedback,
            'topic' => implode(',', session('ai_step.topic')),
            'provider' => $providerSetting,
            'currentProvider' => $currentProvider ?? $providerSetting,
            'models' => ChatGPTService::availableModels(),
            'model' => session('ai_step.model', 'random'),
        ]);
    }

    public function check(Request $request, ChatGPTService $gpt, GeminiService $gemini)
    {
        $request->validate([
            'answers' => 'required|array',
        ]);

        $question = session('ai_step.current_question');
        if (! $question) {
            return redirect()->route('ai-test.step');
        }

        $userAnswers = $request->input('answers', []);
        $correct = true;
        $explanations = [];
        $givenAnswers = [];
        $sentenceHtml = e($question['question']);

        $provider = session('ai_step.current_provider', session('ai_step.provider', 'chatgpt'));
        $ai = $provider === 'gemini' ? $gemini : $gpt;

        foreach ($question['answers'] as $marker => $correctValue) {
            $given = $userAnswers[$marker] ?? '';
            if (is_array($given)) {
                $given = implode(' ', $given);
            }
            $given = trim($given);
            $givenAnswers[$marker] = $given;
            $isCorrectAnswer = mb_strtolower($given) === mb_strtolower($correctValue);
            if (! $isCorrectAnswer) {
                $correct = false;
                $explanations[$marker] = $ai->explainWrongAnswer($question['question'], $given, $correctValue);
            }
            $class = $isCorrectAnswer ? 'text-green-700 font-bold' : 'text-red-700 font-bold';
            $replacement = '<span class="'.$class.'">'.e($given).'</span>';
            $sentenceHtml = str_replace('{'.$marker.'}', $replacement, $sentenceHtml);
        }

        $stats = session('ai_step.stats', ['correct' => 0, 'wrong' => 0, 'total' => 0]);
        $stats['total']++;
        if ($correct) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }

        $tagIds = session('ai_step.tags', []);
        $provider = session('ai_step.current_provider', session('ai_step.provider', 'chatgpt'));
        $aiTagIds = [];
        if ($provider === 'chatgpt') {
            $aiTagIds[] = Tag::firstOrCreate([
                'name' => 'ChatGPT',
                'category' => 'AI',
            ])->id;
            $model = strtoupper($question['model'] ?? session('ai_step.model', 'unknown'));
            $aiTagIds[] = Tag::firstOrCreate([
                'name' => $model,
                'category' => 'AI',
            ])->id;
        } else {
            $aiTagIds[] = Tag::firstOrCreate([
                'name' => 'Gemini',
                'category' => 'AI',
            ])->id;
        }
        $tagIds = array_unique(array_merge($tagIds, $aiTagIds));

        $this->storeQuestion($question, $tagIds);

        session([
            'ai_step.stats' => $stats,
            'ai_step.current_question' => null,
            'ai_step.feedback' => [
                'isCorrect' => $correct,
                'explanations' => $explanations,
                'answers' => $givenAnswers,
                'answer_sentence' => $sentenceHtml,
            ],
            'ai_step.last_question' => $question['question'],
        ]);

        return redirect()->route('ai-test.step');
    }

    public function provider(Request $request)
    {
        $rules = ['provider' => 'required|in:chatgpt,gemini,mixed'];
        if ($request->input('provider') === 'chatgpt') {
            $rules['model'] = ['required', Rule::in(array_merge(['random'], ChatGPTService::availableModels()))];
        }
        $validated = $request->validate($rules);
        $provider = $validated['provider'];
        $model = $provider === 'chatgpt' ? $validated['model'] : 'random';
        session([
            'ai_step.provider' => $provider,
            'ai_step.current_question' => null,
            'ai_step.next_question' => null,
            'ai_step.next_question_provider' => null,
            'ai_step.feedback' => null,
            'ai_step.last_question' => null,
            'ai_step.current_provider' => $provider === 'mixed' ? null : $provider,
            'ai_step.next_provider' => $provider === 'mixed' ? 'gemini' : null,
            'ai_step.model' => $model,
        ]);

        return redirect()->route('ai-test.step');
    }

    public function reset()
    {
        session()->forget([
            'ai_step.tags',
            'ai_step.answers_range',
            'ai_step.stats',
            'ai_step.current_question',
            'ai_step.next_question',
            'ai_step.next_question_provider',
            'ai_step.feedback',
            'ai_step.last_question',
            'ai_step.topic',
            'ai_step.provider',
            'ai_step.current_provider',
            'ai_step.next_provider',
            'ai_step.model',
        ]);

        return redirect()->route('ai-test.form');
    }

    public function next(ChatGPTService $gpt, GeminiService $gemini)
    {
        $tagIds = session('ai_step.tags');
        if (! $tagIds) {
            return response()->json(['status' => 'no-session'], 400);
        }

        $providerSetting = session('ai_step.provider', 'chatgpt');
        if ($providerSetting === 'mixed') {
            $provider = session('ai_step.next_provider', 'gemini');
            session(['ai_step.next_provider' => $provider === 'gemini' ? 'chatgpt' : 'gemini']);
        } else {
            $provider = $providerSetting;
        }

        $tenseNames = Tag::whereIn('id', $tagIds)->orderBy('category')
            ->get()
            ->groupBy('category')->map(function ($items) {
                return $items->pluck('name')->toArray();
            })
            ->toArray();
        $range = session('ai_step.answers_range', [1, 1]);
        $answersCount = random_int($range[0], $range[1]);
        $lastQuestion = session('ai_step.last_question');
        $attempts = 0;
        $question = null;
        $responseAnswersCount = $answersCount;
        $refferance = $this->getRefferance($range[0], $range[1], $responseAnswersCount);
        session(['ai_step.refferance' => $refferance]);
        do {
            if ($provider === 'gemini') {
                $question = $gemini->generateGrammarQuestion($flatTenses, $answersCount);
            } else {
                $model = session('ai_step.model') ?? 'random';
                $question = $gpt->generateGrammarQuestion($flatTenses, $responseAnswersCount, $model);
            }
            $attempts++;
        } while ($question && $lastQuestion && $question['question'] === $lastQuestion && $attempts < 3);
        if ($question) {
            $this->storeWords($question);
            session([
                'ai_step.next_question' => $question,
                'ai_step.next_question_provider' => $provider,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function skip()
    {
        $question = session('ai_step.current_question');
        if ($question) {
            session(['ai_step.last_question' => $question['question']]);
        }
        session(['ai_step.current_question' => null]);

        return redirect()->route('ai-test.step');
    }

    public function determineTense(ChatGPTService $gpt)
    {
        $question = session('ai_step.current_question');
        if (! $question) {
            return response()->json(['tags' => []], 400);
        }

        $tags = Tag::where('category', 'Tenses')->pluck('name')->toArray();
        $suggested = $gpt->determineTenseTags($question['question'], $tags);

        return response()->json(['tags' => $suggested]);
    }

    public function determineTenseGemini(GeminiService $gemini)
    {
        $question = session('ai_step.current_question');
        if (! $question) {
            return response()->json(['tags' => []], 400);
        }

        $tags = Tag::where('category', 'Tenses')->pluck('name')->toArray();
        $suggested = $gemini->determineTenseTags($question['question'], $tags);

        return response()->json(['tags' => $suggested]);
    }

    public function determineLevel(ChatGPTService $gpt)
    {
        $question = session('ai_step.current_question');
        if (! $question) {
            return response()->json(['level' => null], 400);
        }

        $level = $gpt->determineDifficulty($question['question']);

        return response()->json(['level' => $level]);
    }

    public function determineLevelGemini(GeminiService $gemini)
    {
        $question = session('ai_step.current_question');
        if (! $question) {
            return response()->json(['level' => null], 400);
        }

        $level = $gemini->determineDifficulty($question['question']);

        return response()->json(['level' => $level]);
    }

    public function setLevel(Request $request)
    {
        $request->validate([
            'level' => 'required|in:A1,A2,B1,B2,C1,C2',
        ]);

        $question = session('ai_step.current_question');
        if (! $question) {
            return response()->json(['status' => 'error'], 400);
        }

        $question['level'] = $request->input('level');
        session(['ai_step.current_question' => $question]);

        return response()->json(['status' => 'ok']);
    }

    public function addTag(Request $request)
    {
        $request->validate([
            'tag' => 'required|string',
        ]);

        $tag = Tag::where('name', $request->input('tag'))->first();
        if (! $tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $tagIds = session('ai_step.tags', []);
        if (! in_array($tag->id, $tagIds)) {
            $tagIds[] = $tag->id;
            session(['ai_step.tags' => $tagIds]);
        }

        $names = Tag::whereIn('id', $tagIds)->pluck('name');

        return response()->json(['tags' => $names]);
    }

    public function removeTag(Request $request)
    {
        $request->validate([
            'tag' => 'required|string',
        ]);

        $tag = Tag::where('name', $request->input('tag'))->first();
        if (! $tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $tagIds = session('ai_step.tags', []);
        $tagIds = array_values(array_filter($tagIds, fn ($id) => $id !== $tag->id));
        session(['ai_step.tags' => $tagIds]);

        $names = Tag::whereIn('id', $tagIds)->pluck('name');

        return response()->json(['tags' => $names]);
    }

    private function storeQuestion(array $question, array $tagIds): void
    {
        $service = app(QuestionSeedingService::class);
        $sourceId = Source::firstOrCreate(['name' => 'AI Generated'])->id;

        $answers = [];
        $options = [];
        foreach ($question['answers'] as $marker => $val) {
            $ans = ['marker' => $marker, 'answer' => $val];
            if (! empty($question['verb_hints'][$marker] ?? null)) {
                $ans['verb_hint'] = $question['verb_hints'][$marker];
            }
            $answers[] = $ans;
            $options[] = $val;
        }

        $categoryId = Category::query()->value('id');

        $data = [
            'uuid' => Str::uuid()->toString(),
            'question' => $question['question'],
            'difficulty' => 1,
            'category_id' => $categoryId,
            'flag' => 1,
            'source_id' => $sourceId,
        ];
        if (Schema::hasColumn('questions', 'level')) {
            $data['level'] = $question['level'] ?? null;
        }
        $data['answers'] = $answers;
        $data['options'] = $options;
        $data['tag_ids'] = $tagIds;

        $service->seed([$data]);
    }

    private function storeWords(array $question): void
    {
        $words = $this->extractWords($question['question']);
        foreach ($question['answers'] as $answer) {
            $words = array_merge($words, $this->extractWords($answer));
        }

        $words = array_unique($words);
        foreach ($words as $word) {
            if ($word !== '') {
                Word::firstOrCreate(['word' => $word]);
            }
        }
    }

    private function extractWords(string $text): array
    {
        $text = preg_replace('/\{a\d+\}/', ' ', $text);
        preg_match_all("/[A-Za-z']+/u", strtolower($text), $matches);

        return $matches[0];
    }
}
