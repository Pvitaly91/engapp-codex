<?php

namespace App\Http\Livewire;

use App\Models\Word;
use Illuminate\Support\Arr;
use Livewire\Component;

class WordsTest extends Component
{
    public array $stats = [
        'correct' => 0,
        'wrong' => 0,
        'total' => 0,
    ];

    public array $queue = [];
    public int $totalCount = 0;
    public ?array $currentQuestion = null;
    public bool $completed = false;
    public bool $failed = false;
    public float $percentage = 0;

    public ?array $lastResult = null;
    public bool $showFeedback = false;
    public bool $isLoading = false;
    public bool $showFailureModal = false;

    public string $selectedAnswer = '';
    public string $highlightedButton = '';
    public bool $highlightCorrect = false;

    protected function activeLang(): string
    {
        $lang = session('locale', 'uk');

        if (! in_array($lang, ['uk', 'pl', 'en'], true)) {
            return 'uk';
        }

        return $lang;
    }

    protected function wordsQuery(string $lang)
    {
        return Word::with(['translates' => fn ($q) => $q->where('lang', $lang), 'tags'])
            ->whereHas('translates', function ($q) use ($lang) {
                $q->where('lang', $lang)
                    ->whereNotNull('translation')
                    ->where('translation', '!=', '');
            });
    }

    public function mount(): void
    {
        $this->initializeState();
        $this->loadNextQuestion();
    }

    protected function initializeState(): void
    {
        $lang = $this->activeLang();

        if (session()->has('words_test_stats')) {
            $this->stats = session('words_test_stats');
        }

        if (session()->has('words_queue')) {
            $this->queue = session('words_queue', []);
            $this->totalCount = session('words_total_count', 0);
        } else {
            $this->queue = $this->wordsQuery($lang)->pluck('id')->shuffle()->toArray();
            $this->totalCount = count($this->queue);
            session([
                'words_queue' => $this->queue,
                'words_total_count' => $this->totalCount,
                'words_test_stats' => $this->stats,
            ]);
        }

        if (session()->has('words_current_question')) {
            $this->currentQuestion = session('words_current_question');
        }

        $this->updatePercentage();
        $this->checkFailed();
    }

    protected function updatePercentage(): void
    {
        if ($this->totalCount === 0) {
            $this->percentage = 0;

            return;
        }

        $progress = ($this->stats['total'] / $this->totalCount) * 100;
        $this->percentage = round(min($progress, 100), 2);
    }

    protected function checkFailed(): void
    {
        $this->failed = $this->stats['wrong'] >= 3;
        $this->showFailureModal = $this->failed;

        if ($this->failed) {
            $this->queue = [];
            $this->currentQuestion = null;
            session(['words_queue' => []]);
            session()->forget('words_current_question');
        }
    }

    protected function buildQuestionPayload(int $wordId, string $lang): ?array
    {
        $word = $this->wordsQuery($lang)->find($wordId);

        if (! $word) {
            return null;
        }

        $otherWords = $this->wordsQuery($lang)
            ->where('id', '!=', $wordId)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $questionType = random_int(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';

        $translation = optional($word->translates->first())->translation ?? '';

        if ($questionType === 'en_to_uk') {
            $correct = $translation;
            $options = $otherWords
                ->map(fn ($w) => optional($w->translates->first())->translation ?? '')
                ->filter()
                ->values();
        } else {
            $correct = $word->word;
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
            'prompt' => $questionType === 'en_to_uk' ? $word->word : $translation,
            'options' => $options,
            'correct_answer' => $correct,
        ];
    }

    protected function loadNextQuestion(): void
    {
        $this->highlightedButton = '';

        if ($this->failed) {
            return;
        }

        if ($this->currentQuestion) {
            return;
        }

        $lang = $this->activeLang();

        while (! empty($this->queue) && ! $this->currentQuestion) {
            $wordId = array_shift($this->queue);
            $this->currentQuestion = $this->buildQuestionPayload($wordId, $lang);
        }

        session(['words_queue' => $this->queue]);

        if ($this->currentQuestion) {
            session(['words_current_question' => $this->currentQuestion]);
        }

        $this->completed = empty($this->queue) && ! $this->currentQuestion;
    }

    public function submitAnswer(int $wordId, int $optionIndex): void
    {
        if (! $this->currentQuestion || $this->isLoading) {
            return;
        }

        if (($this->currentQuestion['word_id'] ?? null) !== $wordId) {
            return;
        }

        $options = $this->currentQuestion['options'] ?? [];
        if (! isset($options[$optionIndex])) {
            return;
        }

        $answer = $options[$optionIndex];

        $this->isLoading = true;
        $this->selectedAnswer = $answer;

        $isCorrect = trim($answer) === trim($this->currentQuestion['correct_answer']);

        $this->stats['total']++;

        if ($isCorrect) {
            $this->stats['correct']++;
            $this->highlightCorrect = true;
        } else {
            $this->stats['wrong']++;
            $this->highlightCorrect = false;
        }

        $this->highlightedButton = $answer;

        session(['words_test_stats' => $this->stats]);
        $this->updatePercentage();

        $this->lastResult = [
            'isCorrect' => $isCorrect,
            'correctAnswer' => $this->currentQuestion['correct_answer'],
            'word' => $this->currentQuestion['word'],
            'questionType' => $this->currentQuestion['questionType'],
            'translation' => $this->currentQuestion['translation'],
            'userAnswer' => $answer,
        ];
        $this->showFeedback = true;

        $this->currentQuestion = null;
        session()->forget('words_current_question');

        $this->checkFailed();

        if (! $this->failed) {
            $this->loadNextQuestion();
        }

        $this->completed = empty($this->queue) && ! $this->currentQuestion && ! $this->failed;

        $this->isLoading = false;
    }

    public function resetTest(): void
    {
        session()->forget([
            'words_selected_tags',
            'words_test_stats',
            'words_queue',
            'words_total_count',
            'words_current_question',
        ]);

        $this->stats = [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ];
        $this->queue = [];
        $this->totalCount = 0;
        $this->currentQuestion = null;
        $this->completed = false;
        $this->failed = false;
        $this->percentage = 0;
        $this->lastResult = null;
        $this->showFeedback = false;
        $this->showFailureModal = false;

        $this->initializeState();
        $this->loadNextQuestion();
    }

    public function closeFailureModal(): void
    {
        $this->showFailureModal = false;
    }

    public function getQuestionLabelProperty(): string
    {
        if ($this->failed) {
            return 'Тест не пройдено';
        }

        if ($this->completed) {
            return 'Все пройдено';
        }

        if (! $this->currentQuestion) {
            return 'Немає питань';
        }

        return $this->currentQuestion['questionType'] === 'en_to_uk'
            ? 'Обрати переклад українською'
            : 'Обрати слово англійською';
    }

    public function getQuestionWithoutAnswerProperty(): ?array
    {
        if (! $this->currentQuestion) {
            return null;
        }

        return Arr::except($this->currentQuestion, ['correct_answer']);
    }

    public function render()
    {
        return view('livewire.words-test', [
            'activeLang' => $this->activeLang(),
        ])->extends('layouts.engram')->section('content');
    }
}
