<?php

namespace App\Livewire;

use App\Models\Tag;
use App\Models\Word;
use Illuminate\Support\Collection;
use Livewire\Component;

class WordsTest extends Component
{
    public array $selectedTags = [];
    public array $stats = [
        'correct' => 0,
        'wrong' => 0,
        'total' => 0,
    ];
    public float $percentage = 0;
    public array $queue = [];
    public int $totalCount = 0;
    public int $currentIndex = 0;
    public float $progressPercent = 0;

    public ?int $wordId = null;
    public string $wordText = '';
    public string $translation = '';
    public array $options = [];
    public string $questionType = 'en_to_uk';
    public array $wordTags = [];

    public ?array $feedback = null;
    public bool $isComplete = false;
    public bool $showFilters = false;

    public function mount(): void
    {
        $this->selectedTags = session('words_selected_tags', []);
        $this->stats = session('words_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);
        $this->queue = session('words_queue', []);
        $this->totalCount = session('words_total_count', 0);

        $this->loadNextQuestion();
    }

    private function getWords(array $tags): Collection
    {
        $query = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags'])
            ->whereHas('translates', fn ($q) => $q->where('lang', 'uk'));

        if (! empty($tags)) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('name', $tags));
        }

        return $query->get();
    }

    private function calculateProgress(): void
    {
        $this->percentage = $this->stats['total'] > 0
            ? round(($this->stats['correct'] / $this->stats['total']) * 100, 2)
            : 0;

        $remainingCount = count($this->queue);
        $this->currentIndex = $this->totalCount - $remainingCount;
        $this->progressPercent = $this->totalCount > 0
            ? round(($this->currentIndex / $this->totalCount) * 100, 2)
            : 0;
    }

    private function ensureQueue(): void
    {
        // Build a queue whenever it's empty so the component can keep moving forward
        if (empty($this->queue)) {
            $words = $this->getWords($this->selectedTags);
            $this->queue = $words->pluck('id')->shuffle()->toArray();
            $this->totalCount = count($this->queue);
        }

        session([
            'words_queue' => $this->queue,
            'words_total_count' => $this->totalCount,
        ]);
    }

    private function setCompleteState(): void
    {
        $this->isComplete = true;
        $this->wordId = null;
        $this->wordText = '';
        $this->translation = '';
        $this->options = [];
        $this->questionType = 'en_to_uk';
        $this->wordTags = [];
        $this->calculateProgress();
    }

    private function resetQuestionState(): void
    {
        $this->wordId = null;
        $this->wordText = '';
        $this->translation = '';
        $this->options = [];
        $this->questionType = 'en_to_uk';
        $this->wordTags = [];
        $this->isComplete = false;
    }

    private function loadNextQuestion(): void
    {
        $this->resetQuestionState();
        $this->ensureQueue();

        if (empty($this->queue)) {
            $this->setCompleteState();

            return;
        }

        // Check for completion
        if ($this->percentage >= 95 && $this->stats['total'] >= $this->totalCount) {
            $this->setCompleteState();

            return;
        }

        $wordId = array_shift($this->queue);
        session(['words_queue' => $this->queue]);

        $word = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags'])->find($wordId);

        if (! $word) {
            $this->loadNextQuestion();
            return;
        }

        $this->wordId = $word->id;
        $this->wordText = $word->word;
        $this->translation = $word->translates->first()?->translation ?? '';
        $this->wordTags = $word->tags->pluck('name')->toArray();

        // Generate options
        $otherWords = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])
            ->whereHas('translates', fn ($q) => $q->where('lang', 'uk'))
            ->when($this->selectedTags, fn ($q) => $q->whereHas('tags', fn ($q2) => $q2->whereIn('name', $this->selectedTags)))
            ->where('id', '!=', $wordId)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $this->questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';

        if ($this->questionType === 'en_to_uk') {
            $correct = $this->translation;
            $this->options = $otherWords->map(fn ($w) => $w->translates->first()?->translation ?? '')->toArray();
        } else {
            $correct = $this->wordText;
            $this->options = $otherWords->pluck('word')->toArray();
        }

        $this->options[] = $correct;
        shuffle($this->options);

        $this->calculateProgress();
    }

    public function submitAnswer(string $answer): void
    {
        if (! $this->wordId) {
            return;
        }

        $this->stats['total']++;

        $correct = $this->questionType === 'en_to_uk' ? $this->translation : $this->wordText;
        $isCorrect = trim($answer) === trim($correct);

        if ($isCorrect) {
            $this->stats['correct']++;
        } else {
            $this->stats['wrong']++;
        }

        session(['words_test_stats' => $this->stats]);

        $this->feedback = [
            'isCorrect' => $isCorrect,
            'correctAnswer' => $correct,
            'userAnswer' => $answer,
            'word' => $this->wordText,
            'questionType' => $this->questionType,
        ];

        $this->calculateProgress();
        $this->loadNextQuestion();
    }

    public function submitAnswerByIndex(int $index): void
    {
        if (isset($this->options[$index])) {
            $this->submitAnswer($this->options[$index]);
        }
    }

    public function applyFilter(array $tags): void
    {
        if ($tags !== $this->selectedTags) {
            $this->selectedTags = $tags;
            session(['words_selected_tags' => $tags]);

            // Reset queue and stats when filter changes
            $this->queue = [];
            $this->totalCount = 0;
            $this->stats = ['correct' => 0, 'wrong' => 0, 'total' => 0];
            session()->forget(['words_test_stats', 'words_queue', 'words_total_count']);

            $this->feedback = null;
            $this->isComplete = false;
            $this->loadNextQuestion();
        }
        $this->showFilters = false;
    }

    public function resetFilter(): void
    {
        $this->selectedTags = [];
        session()->forget(['words_selected_tags', 'words_test_stats', 'words_queue', 'words_total_count']);

        $this->queue = [];
        $this->totalCount = 0;
        $this->stats = ['correct' => 0, 'wrong' => 0, 'total' => 0];
        $this->feedback = null;
        $this->isComplete = false;

        $this->loadNextQuestion();
    }

    public function resetProgress(): void
    {
        session()->forget('words_test_stats');
        $this->stats = ['correct' => 0, 'wrong' => 0, 'total' => 0];
        $this->queue = [];
        $this->totalCount = 0;
        $this->feedback = null;
        $this->isComplete = false;

        $this->loadNextQuestion();
    }

    public function restartTest(): void
    {
        session()->forget(['words_test_stats', 'words_queue', 'words_total_count']);
        $this->queue = [];
        $this->totalCount = 0;
        $this->stats = ['correct' => 0, 'wrong' => 0, 'total' => 0];
        $this->feedback = null;
        $this->isComplete = false;

        $this->loadNextQuestion();
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function dismissFeedback(): void
    {
        $this->feedback = null;
    }

    public function render()
    {
        return view('livewire.words-test', [
            'allTags' => Tag::whereHas('words')->get(),
        ]);
    }
}
