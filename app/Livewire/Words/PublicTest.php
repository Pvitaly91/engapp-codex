<?php

namespace App\Livewire\Words;

use App\Services\WordsTestService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.engram')]
#[Title('Тест слів')]
class PublicTest extends Component
{
    protected WordsTestService $service;

    public array $selectedTags = [];
    public array $availableTags = [];
    public ?int $wordId = null;
    public ?array $word = null;
    public array $wordTags = [];
    public string $questionType = 'en_to_uk';
    public array $options = [];
    public array $stats = [
        'correct' => 0,
        'wrong' => 0,
        'total' => 0,
    ];
    public ?array $feedback = null;
    public bool $isComplete = false;
    public int $totalCount = 0;
    public int $currentIndex = 1;
    public int $progressPercent = 0;

    public function boot(WordsTestService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->selectedTags = session('words_selected_tags', []);
        $this->stats = session('words_test_stats', $this->stats);
        $this->availableTags = $this->service->fetchAvailableTags()->pluck('name')->all();

        $this->initQueueIfNeeded();
        $this->loadNextQuestion();
    }

    public function render()
    {
        return view('livewire.words.public-test');
    }

    public function submitAnswer(string $answer): void
    {
        if ($this->isComplete || ! $this->wordId || ! $this->word) {
            return;
        }

        $isCorrect = $this->service->isCorrect($this->word, $this->questionType, $answer);

        $this->stats['total']++;
        $isCorrect ? $this->stats['correct']++ : $this->stats['wrong']++;
        session(['words_test_stats' => $this->stats]);

        $this->feedback = [
            'type' => $isCorrect ? 'success' : 'error',
            'title' => $isCorrect ? 'Правильно!' : 'Помилка',
            'message' => $this->questionType === 'en_to_uk'
                ? ($this->word['word'].' = '.$this->word['translation'])
                : ($this->word['translation'].' = '.$this->word['word']),
            'isCorrect' => $isCorrect,
            'correctAnswer' => $this->questionType === 'en_to_uk' ? $this->word['translation'] : $this->word['word'],
            'userAnswer' => $answer,
        ];

        $this->loadNextQuestion();
    }

    public function applyFilter(): void
    {
        session(['words_selected_tags' => $this->selectedTags]);
        $this->resetSessionState();

        $this->initQueueIfNeeded(force: true);
        $this->loadNextQuestion();
    }

    public function resetFilter(): void
    {
        $this->selectedTags = [];
        session(['words_selected_tags' => $this->selectedTags]);
        $this->resetSessionState();

        $this->initQueueIfNeeded(force: true);
        $this->loadNextQuestion();
    }

    public function resetProgress(): void
    {
        session(['words_selected_tags' => $this->selectedTags]);
        $this->resetSessionState();

        $this->initQueueIfNeeded(force: true);
        $this->loadNextQuestion();
    }

    protected function initQueueIfNeeded(bool $force = false): void
    {
        $queue = session('words_queue');

        if (! $force && is_array($queue)) {
            $this->totalCount = session('words_total_count', count($queue));

            if (count($queue) === 0 && $this->totalCount > 0) {
                return;
            }

            if (count($queue) > 0) {
                return;
            }
        }

        [$queue, $totalCount] = $this->service->buildQueue($this->selectedTags);
        session(['words_queue' => $queue, 'words_total_count' => $totalCount]);

        $this->totalCount = $totalCount;
    }

    protected function loadNextQuestion(): void
    {
        $this->stats = session('words_test_stats', $this->stats);
        $this->initQueueIfNeeded();

        $queue = session('words_queue', []);
        $this->totalCount = session('words_total_count', count($queue));

        if (empty($queue)) {
            $this->markComplete();

            return;
        }

        $wordId = array_shift($queue);
        session(['words_queue' => $queue]);

        $question = $this->service->makeQuestion($wordId, $this->selectedTags);

        if (! $question) {
            $this->markComplete();

            return;
        }

        $this->wordId = $wordId;
        $this->word = $question['word'] ?? null;
        $this->wordTags = $question['tags'] ?? [];
        $this->questionType = $question['questionType'] ?? 'en_to_uk';
        $this->options = $question['options'] ?? [];
        $this->isComplete = false;

        $answered = $this->stats['total'];
        $this->currentIndex = min($answered + 1, max($this->totalCount, 1));
        $this->progressPercent = $this->totalCount > 0
            ? (int) round(($answered / $this->totalCount) * 100)
            : 0;
    }

    protected function resetSessionState(): void
    {
        session()->forget(['words_test_stats', 'words_queue', 'words_total_count']);

        $this->stats = [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ];
        $this->feedback = null;
        $this->isComplete = false;
        $this->word = null;
        $this->wordId = null;
        $this->wordTags = [];
        $this->options = [];
        $this->questionType = 'en_to_uk';
        $this->currentIndex = 1;
        $this->progressPercent = 0;
        $this->totalCount = 0;
    }

    protected function markComplete(): void
    {
        $this->isComplete = true;
        $this->word = null;
        $this->wordId = null;
        $this->options = [];
        $this->wordTags = [];
        $this->currentIndex = $this->totalCount;
        $this->progressPercent = $this->totalCount > 0 ? 100 : 0;
    }
}
