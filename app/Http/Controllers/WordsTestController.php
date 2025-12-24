<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class WordsTestController extends Controller
{
    private const SESSION_KEYS = [
        'words_selected_tags',
        'words_test_stats',
        'words_queue',
        'words_total_count',
        'words_current_question',
    ];

    private const DIFFICULTIES = ['easy', 'medium', 'hard'];

    private const STUDY_LANG_KEY = 'words_test_study_lang_%s';

    private const ALLOWED_LANGS = ['uk', 'en', 'pl'];

    private function isFailed(array $stats): bool
    {
        return $stats['wrong'] >= 3;
    }

    private function siteLocale(): string
    {
        $locale = app()->getLocale();

        if (! in_array($locale, self::ALLOWED_LANGS, true)) {
            return 'uk';
        }

        return $locale;
    }

    private function allowedStudyLang(string $lang): bool
    {
        return in_array($lang, self::ALLOWED_LANGS, true);
    }

    private function studyLangSessionKey(string $difficulty): string
    {
        return sprintf(self::STUDY_LANG_KEY, $difficulty);
    }

    private function getStudyLanguage(string $difficulty): ?string
    {
        $key = $this->studyLangSessionKey($difficulty);
        $studyLang = session($key);

        if ($studyLang && $this->allowedStudyLang($studyLang)) {
            return $studyLang;
        }

        $siteLocale = $this->siteLocale();

        if ($siteLocale !== 'en') {
            session([$key => $siteLocale]);

            return $siteLocale;
        }

        return null;
    }

    private function persistStudyLanguage(string $difficulty, string $studyLang): string
    {
        $lang = $this->allowedStudyLang($studyLang) ? $studyLang : $this->siteLocale();
        session([$this->studyLangSessionKey($difficulty) => $lang]);

        return $lang;
    }

    private function requiresStudyLanguageSelection(?string $studyLang, string $siteLocale): bool
    {
        if ($siteLocale !== 'en') {
            return $studyLang === null;
        }

        return $studyLang === null || $studyLang === 'en';
    }

    private function wordsQuery(string $studyLang, string $siteLocale)
    {
        $translationLang = $studyLang === 'en' ? $siteLocale : $studyLang;

        return Word::with(['translates' => fn ($q) => $q->where('lang', $translationLang), 'tags'])
            ->whereHas('translates', function ($q) use ($translationLang) {
                $q->where('lang', $translationLang)
                    ->whereNotNull('translation')
                    ->where('translation', '!=', '');
            });
    }

    private function difficulty(?string $difficulty = null): string
    {
        $difficulty = strtolower($difficulty ?? 'easy');

        if (! in_array($difficulty, self::DIFFICULTIES, true)) {
            return 'easy';
        }

        return $difficulty;
    }

    private function sessionKey(string $key, string $difficulty, string $studyLang): string
    {
        return sprintf('%s_%s_%s', $key, $difficulty, $studyLang);
    }

    private function initializeState(string $studyLang, string $siteLocale, string $difficulty): void
    {
        $statsKey = $this->sessionKey('words_test_stats', $difficulty, $studyLang);
        $queueKey = $this->sessionKey('words_queue', $difficulty, $studyLang);
        $totalKey = $this->sessionKey('words_total_count', $difficulty, $studyLang);

        if (! session()->has($statsKey)) {
            session([
                $statsKey => [
                    'correct' => 0,
                    'wrong' => 0,
                    'total' => 0,
                ],
            ]);
        }

        if (! session()->has($queueKey)) {
            $queue = $this->wordsQuery($studyLang, $siteLocale)->pluck('id')->shuffle()->toArray();
            session([
                $queueKey => $queue,
                $totalKey => count($queue),
            ]);
        }
    }

    private function calculatePercentage(array $stats): float
    {
        if ($stats['total'] === 0) {
            return 0;
        }

        return round(($stats['correct'] / $stats['total']) * 100, 2);
    }

    private function buildQuestionPayload(int $wordId, string $studyLang, string $siteLocale, string $difficulty): ?array
    {
        $word = $this->wordsQuery($studyLang, $siteLocale)->find($wordId);

        if (! $word) {
            return null;
        }

        $translation = optional($word->translates->first())->translation ?? '';
        $questionType = $studyLang === 'en' ? 'lang_to_en' : 'en_to_lang';
        $prompt = $studyLang === 'en' ? $translation : $word->word;
        $correct = $studyLang === 'en' ? $word->word : $translation;

        if ($difficulty === 'easy') {
            $otherWords = $this->wordsQuery($studyLang, $siteLocale)
                ->where('id', '!=', $wordId)
                ->inRandomOrder()
                ->take(4)
                ->get();

            $options = $studyLang === 'en'
                ? $otherWords->pluck('word')
                : $otherWords
                    ->map(fn ($w) => optional($w->translates->first())->translation ?? '')
                    ->filter()
                    ->values();

            $options = $options
                ->filter()
                ->push($correct)
                ->unique()
                ->shuffle()
                ->values()
                ->all();

            return [
                'word_id' => $word->id,
                'word' => $word->word,
                'translation' => $translation,
                'tags' => $word->tags->pluck('name')->all(),
                'questionType' => $questionType,
                'prompt' => $prompt,
                'options' => $options,
                'correct_answer' => $correct,
            ];
        }

        return [
            'word_id' => $word->id,
            'word' => $word->word,
            'translation' => $translation,
            'tags' => $word->tags->pluck('name')->all(),
            'questionType' => $questionType,
            'prompt' => $prompt,
            'options' => [],
            'correct_answer' => $correct,
        ];
    }

    private function ensureCurrentQuestion(string $studyLang, string $siteLocale, string $difficulty): ?array
    {
        $currentQuestionKey = $this->sessionKey('words_current_question', $difficulty, $studyLang);

        if ($question = session($currentQuestionKey)) {
            return $question;
        }

        $queueKey = $this->sessionKey('words_queue', $difficulty, $studyLang);
        $queue = session($queueKey, []);

        if (empty($queue)) {
            return null;
        }

        $question = null;

        while (! empty($queue) && ! $question) {
            $wordId = array_shift($queue);
            $question = $this->buildQuestionPayload($wordId, $studyLang, $siteLocale, $difficulty);
        }

        session([$queueKey => $queue]);

        if (! $question) {
            return null;
        }

        session([$currentQuestionKey => $question]);

        return $question;
    }

    private function completionStatus(?array $question, string $difficulty, string $studyLang): bool
    {
        $queue = session($this->sessionKey('words_queue', $difficulty, $studyLang), []);
        $totalCount = session($this->sessionKey('words_total_count', $difficulty, $studyLang), 0);

        if ($totalCount === 0) {
            return true;
        }

        return empty($queue) && ! $question;
    }

    private function statePayload(string $studyLang, string $siteLocale, string $difficulty): array
    {
        $this->initializeState($studyLang, $siteLocale, $difficulty);

        $statsKey = $this->sessionKey('words_test_stats', $difficulty, $studyLang);
        $queueKey = $this->sessionKey('words_queue', $difficulty, $studyLang);
        $currentQuestionKey = $this->sessionKey('words_current_question', $difficulty, $studyLang);
        $totalKey = $this->sessionKey('words_total_count', $difficulty, $studyLang);

        $stats = session($statsKey);
        $failed = $this->isFailed($stats);

        if ($failed) {
            session([$queueKey => []]);
            session()->forget($currentQuestionKey);
            $question = null;
        } else {
            $question = $this->ensureCurrentQuestion($studyLang, $siteLocale, $difficulty);
        }
        $percentage = $this->calculatePercentage($stats);

        return [
            'question' => $question ? Arr::except($question, ['correct_answer']) : null,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => session($totalKey, 0),
            'completed' => $failed ? false : $this->completionStatus($question, $difficulty, $studyLang),
            'failed' => $failed,
            'difficulty' => $difficulty,
            'studyLang' => $studyLang,
            'siteLocale' => $siteLocale,
            'needsStudyLanguage' => false,
        ];
    }

    public function index(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);
        $siteLocale = $this->siteLocale();
        $studyLang = $this->getStudyLanguage($difficulty);

        if (! $this->requiresStudyLanguageSelection($studyLang, $siteLocale)) {
            $this->initializeState($studyLang, $siteLocale, $difficulty);
        }

        return view('words.test', [
            'activeLang' => $siteLocale,
            'difficulty' => $difficulty,
            'studyLang' => $studyLang,
            'siteLocale' => $siteLocale,
            'stateUrl' => route($this->routeName('state', $difficulty)),
            'checkUrl' => route($this->routeName('check', $difficulty)),
            'resetUrl' => route($this->routeName('reset', $difficulty)),
            'setStudyLangUrl' => route($this->routeName('setStudyLanguage', $difficulty)),
        ]);
    }

    public function state(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);
        $siteLocale = $this->siteLocale();
        $studyLang = $this->getStudyLanguage($difficulty);

        if ($this->requiresStudyLanguageSelection($studyLang, $siteLocale)) {
            return response()->json([
                'question' => null,
                'stats' => [
                    'correct' => 0,
                    'wrong' => 0,
                    'total' => 0,
                ],
                'percentage' => 0,
                'totalCount' => 0,
                'completed' => false,
                'failed' => false,
                'difficulty' => $difficulty,
                'studyLang' => $studyLang,
                'siteLocale' => $siteLocale,
                'needsStudyLanguage' => true,
            ]);
        }

        return response()->json($this->statePayload($studyLang, $siteLocale, $difficulty));
    }

    public function check(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);
        $siteLocale = $this->siteLocale();
        $studyLang = $this->getStudyLanguage($difficulty);

        if ($this->requiresStudyLanguageSelection($studyLang, $siteLocale)) {
            return response()->json([
                'message' => __('words_test.select_language_first'),
            ], 422);
        }

        $request->validate([
            'word_id' => 'required|integer',
            'answer' => 'required|string',
        ]);

        $currentQuestionKey = $this->sessionKey('words_current_question', $difficulty, $studyLang);
        $question = session($currentQuestionKey);

        if (! $question || $question['word_id'] !== (int) $request->input('word_id')) {
            return response()->json([
                'message' => __('words_test.missing_question'),
            ], 422);
        }

        $statsKey = $this->sessionKey('words_test_stats', $difficulty, $studyLang);
        $queueKey = $this->sessionKey('words_queue', $difficulty, $studyLang);
        $totalKey = $this->sessionKey('words_total_count', $difficulty, $studyLang);

        $stats = session($statsKey, [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        $stats['total']++;

        $userAnswer = trim($request->input('answer'));
        $correctAnswer = trim($question['correct_answer']);
        $isCorrect = $difficulty === 'easy'
            ? $userAnswer === $correctAnswer
            : strcasecmp($userAnswer, $correctAnswer) === 0;

        if ($isCorrect) {
            $stats['correct']++;
        } else {
            $stats['wrong']++;
        }

        session([$statsKey => $stats]);
        session()->forget($currentQuestionKey);

        $failed = $this->isFailed($stats);

        if ($failed) {
            session([$queueKey => []]);
            $nextQuestion = null;
        } else {
            $nextQuestion = $this->ensureCurrentQuestion($studyLang, $siteLocale, $difficulty);
        }

        return response()->json([
            'result' => [
                'isCorrect' => $isCorrect,
                'correctAnswer' => $question['correct_answer'],
                'word' => $question['word'],
                'questionType' => $question['questionType'],
                'translation' => $question['translation'],
            ],
            'question' => $nextQuestion ? Arr::except($nextQuestion, ['correct_answer']) : null,
            'stats' => $stats,
            'percentage' => $this->calculatePercentage($stats),
            'totalCount' => session($totalKey, 0),
            'completed' => $failed ? false : $this->completionStatus($nextQuestion, $difficulty, $studyLang),
            'failed' => $failed,
        ]);
    }

    public function reset(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);
        $siteLocale = $this->siteLocale();
        $studyLang = $this->getStudyLanguage($difficulty);

        if ($this->requiresStudyLanguageSelection($studyLang, $siteLocale)) {
            return response()->json([
                'message' => __('words_test.select_language_first'),
                'needsStudyLanguage' => true,
                'studyLang' => $studyLang,
                'siteLocale' => $siteLocale,
            ], 422);
        }

        $keys = array_map(fn ($key) => $this->sessionKey($key, $difficulty, $studyLang), self::SESSION_KEYS);
        session()->forget($keys);

        $payload = $this->statePayload($studyLang, $siteLocale, $difficulty);

        return response()->json($payload);
    }

    private function routeName(string $action, string $difficulty): string
    {
        if ($difficulty === 'medium') {
            return "words.test.{$action}.medium";
        }

        if ($difficulty === 'hard') {
            return "words.test.{$action}.hard";
        }

        return "words.test.{$action}";
    }

    public function setStudyLanguage(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);

        $request->validate([
            'lang' => 'required|in:uk,en,pl',
        ]);

        $studyLang = $this->persistStudyLanguage($difficulty, $request->input('lang'));

        return response()->json([
            'ok' => true,
            'study_lang' => $studyLang,
        ]);
    }
}
