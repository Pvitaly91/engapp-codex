<?php

namespace App\Http\Controllers;

use App\Models\Translate;
use App\Models\Word;
use App\Modules\LanguageManager\Services\LocaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

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

    private const MIN_TRANSLATIONS_COUNT = 100;

    /**
     * Get available study languages from LanguageManager, excluding English,
     * and only languages with at least MIN_TRANSLATIONS_COUNT translations.
     * Results are cached for the duration of the request.
     */
    private ?array $cachedAvailableStudyLanguages = null;

    private function getAvailableStudyLanguages(): array
    {
        // Return cached result if available
        if ($this->cachedAvailableStudyLanguages !== null) {
            return $this->cachedAvailableStudyLanguages;
        }

        // Get active languages from LanguageManager (excluding English)
        $activeLanguages = LocaleService::getActiveLanguages()
            ->filter(fn ($lang) => $lang->code !== 'en')
            ->pluck('code')
            ->toArray();

        if (empty($activeLanguages)) {
            $this->cachedAvailableStudyLanguages = [];

            return [];
        }

        // Count translations per language and filter by minimum count
        $translationCounts = Translate::select('lang', DB::raw('COUNT(*) as count'))
            ->whereIn('lang', $activeLanguages)
            ->whereNotNull('translation')
            ->where('translation', '!=', '')
            ->groupBy('lang')
            ->having('count', '>=', self::MIN_TRANSLATIONS_COUNT)
            ->pluck('count', 'lang')
            ->toArray();

        // Return only languages that have enough translations
        $this->cachedAvailableStudyLanguages = array_keys($translationCounts);

        return $this->cachedAvailableStudyLanguages;
    }

    /**
     * Check if a study language is valid (exists in available languages).
     */
    private function isValidStudyLang(?string $lang): bool
    {
        if ($lang === null) {
            return false;
        }

        return in_array($lang, $this->getAvailableStudyLanguages(), true);
    }

    private function isFailed(array $stats): bool
    {
        return $stats['wrong'] >= 3;
    }

    /**
     * Get the site locale (active language of the interface).
     */
    private function siteLocale(): string
    {
        $lang = session('locale', app()->getLocale());
        $availableLangs = LocaleService::getActiveLanguages()->pluck('code')->toArray();

        if (! in_array($lang, $availableLangs, true)) {
            return 'uk';
        }

        return $lang;
    }

    /**
     * Get the study language for the test.
     * Returns null if no valid study language is set (user must choose).
     */
    private function getStudyLang(string $difficulty): ?string
    {
        $sessionKey = $this->sessionKey('words_test_study_lang', $difficulty);
        $availableStudyLangs = $this->getAvailableStudyLanguages();
        $studyLang = session($sessionKey);

        // Auto-select the only available study language
        if (count($availableStudyLangs) === 1) {
            $singleLang = $availableStudyLangs[0];
            if ($studyLang !== $singleLang) {
                $this->setStudyLangInSession($singleLang, $difficulty);
            }

            return $singleLang;
        }

        // If stored study language is valid, return it
        if ($studyLang && in_array($studyLang, $availableStudyLangs, true)) {
            return $studyLang;
        }

        // Default: if site locale is in available study languages, use it
        $siteLocale = $this->siteLocale();
        if (in_array($siteLocale, $availableStudyLangs, true)) {
            return $siteLocale;
        }

        // No valid study language found - return null to force selection
        // This happens when site locale is EN or site locale doesn't have enough translations
        return null;
    }

    /**
     * Set the study language in session.
     */
    private function setStudyLangInSession(string $lang, string $difficulty): void
    {
        $sessionKey = $this->sessionKey('words_test_study_lang', $difficulty);
        session([$sessionKey => $lang]);
    }

    /**
     * Check if study language selection is needed.
     */
    private function needsStudyLanguageSelection(string $difficulty): bool
    {
        return $this->getStudyLang($difficulty) === null;
    }

    /**
     * Get the language to use for filtering words (the language that must have translations).
     * Since we no longer support studying English, this just returns the study language.
     */
    private function getTranslationLang(string $studyLang): string
    {
        return $studyLang;
    }

    private function wordsQuery(string $lang)
    {
        return Word::with(['translates' => fn ($q) => $q->where('lang', $lang), 'tags'])
            ->whereHas('translates', function ($q) use ($lang) {
                $q->where('lang', $lang)
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

    private function sessionKey(string $key, string $difficulty): string
    {
        // For study_lang key itself, don't append language suffix
        if ($key === 'words_test_study_lang') {
            return sprintf('%s_%s', $key, $difficulty);
        }

        // Read study language directly to avoid circular dependency
        $studyLangKey = sprintf('words_test_study_lang_%s', $difficulty);
        $studyLang = session($studyLangKey);

        // For other keys, append both difficulty and study language for separate progress
        if ($studyLang) {
            return sprintf('%s_%s_%s', $key, $difficulty, $studyLang);
        }

        return sprintf('%s_%s', $key, $difficulty);
    }

    private function initializeState(string $translationLang, string $difficulty): void
    {
        $statsKey = $this->sessionKey('words_test_stats', $difficulty);
        $queueKey = $this->sessionKey('words_queue', $difficulty);
        $totalKey = $this->sessionKey('words_total_count', $difficulty);

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
            $queue = $this->wordsQuery($translationLang)->pluck('id')->shuffle()->toArray();
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

    /**
     * Build question payload based on study language.
     * Prompt = EN word, answer = translation in study_lang
     * (English is no longer available as a study language)
     */
    private function buildQuestionPayload(int $wordId, string $translationLang, string $difficulty, string $studyLang): ?array
    {
        $word = $this->wordsQuery($translationLang)->find($wordId);

        if (! $word) {
            return null;
        }

        $translation = optional($word->translates->first())->translation ?? '';

        if ($difficulty === 'easy') {
            $otherWords = $this->wordsQuery($translationLang)
                ->where('id', '!=', $wordId)
                ->inRandomOrder()
                ->take(4)
                ->get();

            // Mix question types: EN -> translation or translation -> EN
            $questionType = rand(0, 1) === 0 ? 'en_to_translation' : 'translation_to_en';

            if ($questionType === 'en_to_translation') {
                $correct = $translation;
                $prompt = $word->word;
                $options = $otherWords
                    ->map(fn ($w) => optional($w->translates->first())->translation ?? '')
                    ->filter()
                    ->values();
            } else {
                $correct = $word->word;
                $prompt = $translation;
                $options = $otherWords->pluck('word');
            }

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
                'studyLang' => $studyLang,
            ];
        }

        // Medium/Hard: prompt = EN word, answer = translation
        return [
            'word_id' => $word->id,
            'word' => $word->word,
            'translation' => $translation,
            'tags' => $word->tags->pluck('name')->all(),
            'questionType' => 'en_to_translation',
            'prompt' => $word->word,
            'options' => [],
            'correct_answer' => $translation,
            'studyLang' => $studyLang,
        ];
    }

    private function ensureCurrentQuestion(string $translationLang, string $difficulty, string $studyLang): ?array
    {
        $currentQuestionKey = $this->sessionKey('words_current_question', $difficulty);

        if ($question = session($currentQuestionKey)) {
            return $question;
        }

        $queueKey = $this->sessionKey('words_queue', $difficulty);
        $queue = session($queueKey, []);

        if (empty($queue)) {
            return null;
        }

        $question = null;

        while (! empty($queue) && ! $question) {
            $wordId = array_shift($queue);
            $question = $this->buildQuestionPayload($wordId, $translationLang, $difficulty, $studyLang);
        }

        session([$queueKey => $queue]);

        if (! $question) {
            return null;
        }

        session([$currentQuestionKey => $question]);

        return $question;
    }

    private function completionStatus(?array $question, string $difficulty): bool
    {
        $queue = session($this->sessionKey('words_queue', $difficulty), []);
        $totalCount = session($this->sessionKey('words_total_count', $difficulty), 0);

        if ($totalCount === 0) {
            return true;
        }

        return empty($queue) && ! $question;
    }

    private function statePayload(string $difficulty): array
    {
        $siteLocale = $this->siteLocale();
        $studyLang = $this->getStudyLang($difficulty);
        $availableStudyLangs = $this->getAvailableStudyLanguages();

        // If study language is not set, return special state
        if ($studyLang === null) {
            return [
                'needsStudyLanguage' => true,
                'siteLocale' => $siteLocale,
                'studyLang' => null,
                'availableStudyLangs' => $availableStudyLangs,
                'question' => null,
                'stats' => ['correct' => 0, 'wrong' => 0, 'total' => 0],
                'percentage' => 0,
                'totalCount' => 0,
                'completed' => false,
                'failed' => false,
                'difficulty' => $difficulty,
            ];
        }

        $translationLang = $this->getTranslationLang($studyLang);
        $this->initializeState($translationLang, $difficulty);

        $statsKey = $this->sessionKey('words_test_stats', $difficulty);
        $queueKey = $this->sessionKey('words_queue', $difficulty);
        $currentQuestionKey = $this->sessionKey('words_current_question', $difficulty);
        $totalKey = $this->sessionKey('words_total_count', $difficulty);

        $stats = session($statsKey);
        $failed = $this->isFailed($stats);

        if ($failed) {
            session([$queueKey => []]);
            session()->forget($currentQuestionKey);
            $question = null;
        } else {
            $question = $this->ensureCurrentQuestion($translationLang, $difficulty, $studyLang);
        }
        $percentage = $this->calculatePercentage($stats);

        return [
            'needsStudyLanguage' => false,
            'siteLocale' => $siteLocale,
            'studyLang' => $studyLang,
            'availableStudyLangs' => $this->getAvailableStudyLanguages(),
            'question' => $question ? Arr::except($question, ['correct_answer']) : null,
            'stats' => $stats,
            'percentage' => $percentage,
            'totalCount' => session($totalKey, 0),
            'completed' => $failed ? false : $this->completionStatus($question, $difficulty),
            'failed' => $failed,
            'difficulty' => $difficulty,
        ];
    }

    public function index(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);
        $siteLocale = $this->siteLocale();
        $studyLang = $this->getStudyLang($difficulty);
        $availableStudyLangs = $this->getAvailableStudyLanguages();

        // Only initialize state if we have a study language
        if ($studyLang !== null) {
            $translationLang = $this->getTranslationLang($studyLang);
            $this->initializeState($translationLang, $difficulty);
        }

        // Build study language options from LanguageManager
        $studyLangOptions = [];
        $allLanguages = LocaleService::getActiveLanguages();
        foreach ($availableStudyLangs as $langCode) {
            $lang = $allLanguages->firstWhere('code', $langCode);
            if ($lang) {
                $studyLangOptions[$langCode] = $lang->native_name ?: $lang->name ?: strtoupper($langCode);
            }
        }
        $singleStudyLangName = count($studyLangOptions) === 1 ? array_values($studyLangOptions)[0] : null;

        return view('words.test', [
            'siteLocale' => $siteLocale,
            'studyLang' => $studyLang,
            'activeLang' => $siteLocale, // Keep for backward compatibility
            'difficulty' => $difficulty,
            'availableStudyLangs' => $availableStudyLangs,
            'studyLangOptions' => $studyLangOptions,
            'singleStudyLangName' => $singleStudyLangName,
            'stateUrl' => route($this->routeName('state', $difficulty)),
            'checkUrl' => route($this->routeName('check', $difficulty)),
            'resetUrl' => route($this->routeName('reset', $difficulty)),
            'setStudyLangUrl' => route('words.test.set-study-language'),
        ]);
    }

    public function state(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);

        return response()->json($this->statePayload($difficulty));
    }

    public function setStudyLanguage(Request $request)
    {
        $availableStudyLangs = $this->getAvailableStudyLanguages();

        $request->validate([
            'lang' => ['required', 'string', function ($attribute, $value, $fail) use ($availableStudyLangs) {
                if (! in_array($value, $availableStudyLangs, true)) {
                    $fail(__('words_test.invalid_study_lang'));
                }
            }],
            'difficulty' => 'nullable|string|in:easy,medium,hard',
        ]);

        $lang = $request->input('lang');
        $difficulty = $this->difficulty($request->input('difficulty', 'easy'));

        // Set the study language
        $this->setStudyLangInSession($lang, $difficulty);

        // Reset the test progress for this difficulty (start fresh with new language)
        $keys = array_map(fn ($key) => $this->sessionKey($key, $difficulty), self::SESSION_KEYS);
        session()->forget($keys);

        // Re-set the study language after clearing (since sessionKey depends on it)
        $this->setStudyLangInSession($lang, $difficulty);

        return response()->json([
            'ok' => true,
            'studyLang' => $lang,
            'siteLocale' => $this->siteLocale(),
        ]);
    }

    public function check(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);
        $studyLang = $this->getStudyLang($difficulty);

        if ($studyLang === null) {
            return response()->json([
                'needsStudyLanguage' => true,
                'message' => __('words_test.select_study_lang'),
            ], 422);
        }

        $request->validate([
            'word_id' => 'required|integer',
            'answer' => 'required|string',
        ]);

        $currentQuestionKey = $this->sessionKey('words_current_question', $difficulty);
        $question = session($currentQuestionKey);

        if (! $question || $question['word_id'] !== (int) $request->input('word_id')) {
            return response()->json([
                'message' => __('words_test.no_questions'),
            ], 422);
        }

        $statsKey = $this->sessionKey('words_test_stats', $difficulty);
        $queueKey = $this->sessionKey('words_queue', $difficulty);
        $totalKey = $this->sessionKey('words_total_count', $difficulty);

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

        $translationLang = $this->getTranslationLang($studyLang);
        $failed = $this->isFailed($stats);

        if ($failed) {
            session([$queueKey => []]);
            $nextQuestion = null;
        } else {
            $nextQuestion = $this->ensureCurrentQuestion($translationLang, $difficulty, $studyLang);
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
            'completed' => $failed ? false : $this->completionStatus($nextQuestion, $difficulty),
            'failed' => $failed,
            'studyLang' => $studyLang,
            'siteLocale' => $this->siteLocale(),
        ]);
    }

    public function reset(Request $request, string $difficulty = 'easy')
    {
        $difficulty = $this->difficulty($difficulty);
        $studyLang = $this->getStudyLang($difficulty);

        // Keep study language when resetting
        $keys = array_map(fn ($key) => $this->sessionKey($key, $difficulty), self::SESSION_KEYS);
        session()->forget($keys);

        // Don't forget the study language key
        $payload = $this->statePayload($difficulty);

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
}
