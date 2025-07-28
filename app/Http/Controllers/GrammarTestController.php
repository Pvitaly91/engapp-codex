<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Category;
use App\Models\QuestionAnswer;
use App\Models\Source;
use Illuminate\Support\Str;
use App\Models\Test;

class GrammarTestController extends Controller
{
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
        $questions = \App\Models\Question::with(['category', 'answers.option', 'options', 'verbHints.option', 'tags'])
            ->whereIn('id', $test->questions)
            ->get();
            $manualInput = !empty($test->filters['manual_input']);
            $autocompleteInput = !empty($test->filters['autocomplete_input']);
        // Показати тільки питання — без фільтрів, флагів, тощо
        return view('saved-test', [
            'test' => $test,
            'questions' => $questions,
            'manualInput' => $manualInput,
            'autocompleteInput' => $autocompleteInput,
        ]);
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
        $questions = [];

        return view('grammar-test', compact(
            'categories', 'minDifficulty', 'maxDifficulty', 'maxQuestions',
            'selectedCategories', 'difficultyFrom', 'difficultyTo', 'numQuestions',
            'manualInput', 'autocompleteInput', 'checkOneInput', 'questions'
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
        $includeAi = $request->boolean('include_ai');
        $onlyAi = $request->boolean('only_ai');
        $selectedTags = $request->input('tags', []);
    
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
            if ($onlyAi) {
                $query->where('flag', 1);
            } elseif (!$includeAi) {
                $query->where('flag', 0);
            }
    
            $questions = $questions->merge($query->inRandomOrder()->limit($take)->get());
        }
    
        // Перемішати всі питання
        $questions = $questions->shuffle();
    
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

        return view('grammar-test', compact(
            'categories', 'minDifficulty', 'maxDifficulty', 'maxQuestions',
            'selectedCategories', 'difficultyFrom', 'difficultyTo', 'numQuestions',
            'manualInput', 'autocompleteInput', 'checkOneInput',
            'includeAi', 'onlyAi', 'questions',
            'sources', 'selectedSources', 'autoTestName',
            'allTags', 'selectedTags'
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
        $query = \App\Models\QuestionAnswer::query()
            ->join('question_options', 'question_answers.option_id', '=', 'question_options.id');
    
        if ($q) {
            $query->where('question_options.option', 'like', '%' . $q . '%')
                ->orderByRaw('question_options.option LIKE ? DESC', [$q . '%'])
                ->orderBy('question_options.option');
        }
    
        $answers = $query->distinct()->limit(5)->pluck('question_options.option');
    
        return response()->json($answers);
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

        return view('grammar-test', [
            'categories' => $categories,
            'minDifficulty' => $minDifficulty,
            'maxDifficulty' => $maxDifficulty,
            'maxQuestions' => $maxQuestions,
            'allTags' => $allTags,
            'selectedTags' => $selectedTags,
        ]);
    }

    public function check(Request $request)
    {
        $questions = Question::with(['answers.option', 'category'])->whereIn('id', array_keys($request->get('questions', [])))->get();
        $results = [];

        foreach ($questions as $question) {
            $correctCount = 0;
            $total = $question->answers->count();
            $userAnswers = [];

            foreach ($question->answers as $ans) {
                $inputName = "question_{$question->id}_{$ans->marker}";
                $userAnswer = $request->input($inputName, '');
                $userAnswers[$ans->marker] = $userAnswer;
                $correctValue = $ans->option->option;
                if (strtolower(trim($userAnswer)) === strtolower($correctValue)) {
                    $correctCount++;
                }
            }

            $results[] = [
                'question' => $question,
                'user_answers' => $userAnswers,
                'is_correct' => ($correctCount === $total),
                'correct_answers' => $question->answers->mapWithKeys(fn($a) => [$a->marker => $a->option->option]),
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
        $selectedTags = $request->input('tags', []);
        if (empty($selectedTags) && $request->filled('tag')) {
            $selectedTags = [$request->input('tag')];
        }

        $tests = \App\Models\Test::latest()->get();

        $allQuestionIds = collect($tests)->flatMap(fn($t) => $t->questions)->unique();
        $questions = Question::with('tags')->whereIn('id', $allQuestionIds)->get()->keyBy('id');

        foreach ($tests as $test) {
            $testQuestions = collect($test->questions)->map(fn($id) => $questions[$id] ?? null)->filter();
            $tagNames = $testQuestions->flatMap(fn($q) => $q->tags->pluck('name'));
            $test->tag_names = $tagNames->unique()->values();
        }

        $availableTagNames = $tests->flatMap(fn($t) => $t->tag_names)->unique()->values();
        $availableTags = \App\Models\Tag::with('category')
            ->whereIn('name', $availableTagNames)
            ->get();

        if ($selectedTags) {
            $tests = $tests->filter(function ($t) use ($selectedTags) {
                return collect($selectedTags)->every(fn($tag) => $t->tag_names->contains($tag));
            })->values();
        }

        return view('saved-tests-cards', [
            'tests' => $tests,
            'tags' => $availableTags,
            'selectedTags' => $selectedTags,
        ]);
    }
    
}
