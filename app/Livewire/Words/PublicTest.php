<?php

namespace App\Livewire\Words;

use App\Services\WordsTestService;
use Illuminate\Support\Arr;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.engram')]
class PublicTest extends Component
{
    public array $selectedTags = [];

    public ?int $wordId = null;

    public array $options = [];

    public string $questionType = 'en_to_uk';

    public array $stats = [
        'correct' => 0,
        'wrong' => 0,
        'total' => 0,
    ];

    public ?array $feedback = null;

    public bool $isComplete = false;

    public ?array $word = null;

    public int $totalCount = 0;

    public string $correctAnswer = '';

    public array $allTags = [];

    public function mount(WordsTestService $service): void
    {
        $this->selectedTags = session(WordsTestService::SESSION_SELECTED_TAGS, []);
        $this->stats = session(WordsTestService::SESSION_STATS, $service->defaultStats());
        $this->totalCount = session(WordsTestService::SESSION_TOTAL_COUNT, 0);
        $this->allTags = $service->allTagsWithWords()->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name])->toArray();

        $this->initQueueIfNeeded($service);
        $this->loadNextQuestion($service);
    }

    public function render()
    {
        return view('livewire.words.public-test');
    }

    public function submitAnswer(WordsTestService $service, string $answer): void
    {
        if (! $this->wordId) {
            return;
        }

        $isCorrect = trim($answer) === trim($this->correctAnswer);

        $this->stats['total']++;
        $isCorrect ? $this->stats['correct']++ : $this->stats['wrong']++;
        session([WordsTestService::SESSION_STATS => $this->stats]);

        $this->feedback = [
            'type' => $isCorrect ? 'success' : 'error',
            'title' => $isCorrect ? 'Правильно!' : 'Невірно',
            'message' => $isCorrect
                ? 'Ви обрали правильний варіант.'
                : 'Правильна відповідь: ' . $this->correctAnswer,
            'word' => Arr::get($this->word, 'word', ''),
            'correctAnswer' => $this->correctAnswer,
            'userAnswer' => $answer,
            'questionType' => $this->questionType,
        ];

        $this->loadNextQuestion($service);
    }

    public function applyFilter(WordsTestService $service): void
    {
        $this->selectedTags = array_values(array_unique($this->selectedTags));
        session([WordsTestService::SESSION_SELECTED_TAGS => $this->selectedTags]);

        $service->resetProgress();
        $this->stats = $service->defaultStats();
        $this->totalCount = 0;
        $this->isComplete = false;
        $this->feedback = null;

        $this->initQueueIfNeeded($service);
        $this->loadNextQuestion($service);
    }

    public function resetFilter(WordsTestService $service): void
    {
        $this->selectedTags = [];
        session([WordsTestService::SESSION_SELECTED_TAGS => $this->selectedTags]);

        $service->resetProgress();
        $this->stats = $service->defaultStats();
        $this->totalCount = 0;
        $this->isComplete = false;
        $this->feedback = null;

        $this->initQueueIfNeeded($service);
        $this->loadNextQuestion($service);
    }

    public function resetProgress(WordsTestService $service): void
    {
        $service->resetProgress();
        $this->stats = $service->defaultStats();
        $this->totalCount = 0;
        $this->isComplete = false;
        $this->feedback = null;

        $this->initQueueIfNeeded($service);
        $this->loadNextQuestion($service);
    }

    public function closeFeedback(): void
    {
        $this->feedback = null;
    }

    protected function initQueueIfNeeded(WordsTestService $service): void
    {
        $queue = session(WordsTestService::SESSION_QUEUE);

        if (is_array($queue)) {
            $this->totalCount = session(WordsTestService::SESSION_TOTAL_COUNT, count($queue));

            return;
        }

        $queue = $service->initQueue($this->selectedTags);
        $this->totalCount = count($queue);
    }

    protected function loadNextQuestion(WordsTestService $service): void
    {
        $this->wordId = null;
        $this->word = null;
        $this->options = [];
        $this->correctAnswer = '';
        $this->questionType = 'en_to_uk';

        $nextWordId = $service->pullNextWordId();
        $this->totalCount = session(WordsTestService::SESSION_TOTAL_COUNT, $this->totalCount);

        if ($nextWordId === null) {
            $this->isComplete = $this->totalCount > 0;

            return;
        }

        $question = $service->buildQuestion($nextWordId, $this->selectedTags);

        $this->wordId = $question['word']['id'];
        $this->word = $question['word'];
        $this->options = $question['options'];
        $this->questionType = $question['question_type'];
        $this->correctAnswer = $question['correct_answer'];
        $this->isComplete = false;
    }
}
