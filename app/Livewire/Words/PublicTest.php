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

    public function mount(WordsTestService $service): void
    {
        $this->selectedTags = session('words_selected_tags', []);
        $this->stats = session('words_test_stats', $this->stats);
        $this->availableTags = $service->fetchAvailableTags()->pluck('name')->all();

        $this->initQueueIfNeeded($service);
        $this->loadNextQuestion($service);
    }

    public function render()
    {
        return view('livewire.words.public-test');
    }

    public function submitAnswer(WordsTestService $service, string $answer): void
    {
        if ($this->isComplete || ! $this->wordId || ! $this->word) {
            return;
        }

        $isCorrect = $service->isCorrect($this->word, $this->questionType, $answer);

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

        $this->loadNextQuestion($service);
    }

    public function applyFilter(WordsTestService $service): void
    {
        session(['words_selected_tags' => $this->selectedTags]);
        $this->resetSessionState();

        $this->initQueueIfNeeded($service);
        $this->loadNextQuestion($service);
    }

    public function resetFilter(WordsTestService $service): void
    {
        $this->selectedTags = [];
        session(['words_selected_tags' => $this->selectedTags]);
        $this->resetSessionState();

        $this->initQueueIfNeeded($service);
        $this->loadNextQuestion($service);
    }

    public function resetProgress(WordsTestService $service): void
    {
        session(['words_selected_tags' => $this->selectedTags]);
        $this->resetSessionState();

        $this->initQueueIfNeeded($service);
        $this->loadNextQuestion($service);
    }

    protected function initQueueIfNeeded(WordsTestService $service): void
    {
        $queue = session('words_queue');

        if (! empty($queue)) {
            $this->totalCount = session('words_total_count', count($queue));

            return;
        }

        [$queue, $totalCount] = $service->buildQueue($this->selectedTags);
        session(['words_queue' => $queue, 'words_total_count' => $totalCount]);

        $this->totalCount = $totalCount;
    }

    protected function loadNextQuestion(WordsTestService $service): void
    {
        $this->initQueueIfNeeded($service);

        $queue = session('words_queue', []);
        $this->totalCount = session('words_total_count', 0);
        $this->stats = session('words_test_stats', $this->stats);

        if (empty($queue)) {
            $this->markComplete();

            return;
        }

        $wordId = array_shift($queue);
        session(['words_queue' => $queue]);

        $question = $service->makeQuestion($wordId, $this->selectedTags);

        if (! $question) {
            $this->markComplete();

            return;
        }

        $this->wordId = $wordId;
        $this->word = $question['word'] ?? null;
        $this->wordTags = $question['tags'] ?? [];
        $this->questionType = $question['questionType'] ?? 'en_to_uk';
        $this->options = $question['options'] ?? [];
        $this->currentIndex = max(1, $this->totalCount - count($queue));
        $this->progressPercent = $this->totalCount > 0
            ? (int) round(($this->stats['total'] / $this->totalCount) * 100)
            : 0;
        $this->isComplete = false;
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
