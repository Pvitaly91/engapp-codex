<?php

namespace App\Livewire\Words;

use App\Models\Tag;
use App\Models\Word;
use Illuminate\Support\Collection;
use Livewire\Component;

class PublicTest extends Component
{
    public array $selectedTags = [];
    public array $availableTags = [];
    public ?int $wordId = null;
    public ?array $word = null;
    public array $wordTags = [];
    public string $questionType = 'en_to_uk';
    public array $options = [];
    public array $stats = ['correct' => 0, 'wrong' => 0, 'total' => 0];
    public ?array $feedback = null;
    public bool $isComplete = false;
    public int $totalCount = 0;
    public int $currentIndex = 1;
    public int $progressPercent = 0;

    public function mount(): void
    {
        // Load selected tags from session
        $this->selectedTags = session('words_selected_tags', []);

        // Load stats from session
        $this->stats = session('words_test_stats', [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ]);

        // Load available tags for filter
        $this->availableTags = Tag::whereHas('words')
            ->get()
            ->map(fn (Tag $tag) => ['id' => $tag->id, 'name' => $tag->name])
            ->toArray();

        // Initialize queue and load first question
        $this->initQueueIfNeeded();
        $this->loadNextQuestion();
    }

    /**
     * Initialize the words queue if it doesn't exist in session.
     * Does NOT rebuild if queue exists but is empty (that means test is complete).
     */
    private function initQueueIfNeeded(): void
    {
        // If queue already exists in session (even if empty), don't rebuild
        if (session()->has('words_queue')) {
            return;
        }

        // Build new queue from database
        $words = $this->getWords($this->selectedTags);
        $queue = $words->pluck('id')->shuffle()->toArray();
        $totalCount = count($queue);

        session([
            'words_queue' => $queue,
            'words_total_count' => $totalCount,
        ]);
    }

    /**
     * Get words from database with optional tag filter.
     */
    private function getWords(array $tags): Collection
    {
        $query = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags']);

        if (! empty($tags)) {
            $query->whereHas('tags', fn ($q) => $q->whereIn('name', $tags));
        }

        return $query->get();
    }

    /**
     * Load the next question from the queue.
     * This is the SINGLE point where queue progression happens.
     */
    public function loadNextQuestion(): void
    {
        // Ensure queue exists
        $this->initQueueIfNeeded();

        $queue = session('words_queue', []);
        $this->totalCount = session('words_total_count', 0);

        // Calculate current index (how many questions answered + 1)
        $answeredCount = $this->stats['total'];
        $this->currentIndex = $answeredCount + 1;

        // Calculate progress
        $this->progressPercent = $this->totalCount > 0
            ? min(100, round(($answeredCount / $this->totalCount) * 100))
            : 0;

        // Check if test is complete
        if (empty($queue)) {
            $this->isComplete = true;
            $this->wordId = null;
            $this->word = null;
            $this->options = [];
            return;
        }

        // Get next word from queue (array_shift is only called here)
        $wordId = array_shift($queue);
        session(['words_queue' => $queue]);

        // Load word data
        $wordModel = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags'])->find($wordId);

        if (! $wordModel) {
            // Word was deleted, try next
            $this->loadNextQuestion();
            return;
        }

        $this->wordId = $wordModel->id;
        $this->word = [
            'id' => $wordModel->id,
            'word' => $wordModel->word,
            'translation' => optional($wordModel->translates->first())->translation ?? '',
        ];
        $this->wordTags = $wordModel->tags->pluck('name')->toArray();

        // Randomly choose question type
        $this->questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';

        // Build options
        $this->options = $this->buildOptions($wordModel, $this->questionType);
    }

    /**
     * Build answer options for the question.
     */
    private function buildOptions(Word $word, string $questionType): array
    {
        $otherWords = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])
            ->when($this->selectedTags, fn ($q) => $q->whereHas('tags', fn ($q2) => $q2->whereIn('name', $this->selectedTags)))
            ->where('id', '!=', $word->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        if ($questionType === 'en_to_uk') {
            $correct = optional($word->translates->first())->translation ?? '';
            $options = $otherWords->map(fn ($w) => optional($w->translates->first())->translation ?? '')->toArray();
        } else {
            $correct = $word->word;
            $options = $otherWords->pluck('word')->toArray();
        }

        $options[] = $correct;
        shuffle($options);

        return $options;
    }

    /**
     * Get the correct answer for the current question.
     */
    private function getCorrectAnswer(): string
    {
        if (! $this->word) {
            return '';
        }

        return $this->questionType === 'en_to_uk'
            ? $this->word['translation']
            : $this->word['word'];
    }

    /**
     * Submit an answer and check if it's correct.
     */
    public function submitAnswer(string $answer): void
    {
        if (! $this->word) {
            return;
        }

        $correct = $this->getCorrectAnswer();
        $isCorrect = trim($answer) === trim($correct);

        // Update stats
        $this->stats['total']++;
        if ($isCorrect) {
            $this->stats['correct']++;
        } else {
            $this->stats['wrong']++;
        }
        session(['words_test_stats' => $this->stats]);

        // Set feedback
        $this->feedback = [
            'type' => $isCorrect ? 'success' : 'error',
            'title' => $isCorrect ? 'Правильно!' : 'Помилка',
            'message' => $isCorrect
                ? "{$this->word['word']} = {$this->word['translation']}"
                : "Правильна відповідь: {$correct}",
            'word' => $this->word['word'],
            'userAnswer' => $answer,
            'correctAnswer' => $correct,
        ];

        // Load next question immediately (no redirect)
        $this->loadNextQuestion();
    }

    /**
     * Clear the feedback message.
     */
    public function clearFeedback(): void
    {
        $this->feedback = null;
    }

    /**
     * Apply tag filter and restart the test with new filter.
     */
    public function applyFilter(): void
    {
        // Save selected tags
        session(['words_selected_tags' => $this->selectedTags]);

        // Reset queue and stats
        session()->forget(['words_queue', 'words_total_count', 'words_test_stats']);

        // Reset local state
        $this->stats = ['correct' => 0, 'wrong' => 0, 'total' => 0];
        $this->feedback = null;
        $this->isComplete = false;

        // Rebuild queue and load first question
        $this->initQueueIfNeeded();
        $this->loadNextQuestion();
    }

    /**
     * Reset tag filter to show all words.
     */
    public function resetFilter(): void
    {
        $this->selectedTags = [];

        // Save to session and reset
        session()->forget(['words_selected_tags', 'words_queue', 'words_total_count', 'words_test_stats']);

        // Reset local state
        $this->stats = ['correct' => 0, 'wrong' => 0, 'total' => 0];
        $this->feedback = null;
        $this->isComplete = false;

        // Rebuild queue and load first question
        $this->initQueueIfNeeded();
        $this->loadNextQuestion();
    }

    /**
     * Reset progress but keep the current tag filter.
     */
    public function resetProgress(): void
    {
        // Keep selectedTags, but reset everything else
        session()->forget(['words_queue', 'words_total_count', 'words_test_stats']);

        // Reset local state
        $this->stats = ['correct' => 0, 'wrong' => 0, 'total' => 0];
        $this->feedback = null;
        $this->isComplete = false;

        // Rebuild queue and load first question
        $this->initQueueIfNeeded();
        $this->loadNextQuestion();
    }

    public function render()
    {
        return view('livewire.words.public-test');
    }
}
