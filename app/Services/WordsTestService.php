<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Word;
use Illuminate\Support\Collection;

class WordsTestService
{
    public const SESSION_SELECTED_TAGS = 'words_selected_tags';
    public const SESSION_STATS = 'words_test_stats';
    public const SESSION_QUEUE = 'words_queue';
    public const SESSION_TOTAL_COUNT = 'words_total_count';

    public function allTagsWithWords(): Collection
    {
        return Tag::whereHas('words')->orderBy('name')->get();
    }

    public function initQueue(array $selectedTags): array
    {
        $words = $this->getWordsQuery($selectedTags)->get();
        $queue = $words->pluck('id')->shuffle()->values()->toArray();

        session([
            self::SESSION_QUEUE => $queue,
            self::SESSION_TOTAL_COUNT => count($queue),
        ]);

        return $queue;
    }

    public function resetProgress(): void
    {
        session()->forget([self::SESSION_QUEUE, self::SESSION_STATS, self::SESSION_TOTAL_COUNT]);
    }

    public function defaultStats(): array
    {
        return [
            'correct' => 0,
            'wrong' => 0,
            'total' => 0,
        ];
    }

    public function pullNextWordId(): ?int
    {
        $queue = session(self::SESSION_QUEUE, []);

        if (empty($queue)) {
            return null;
        }

        $wordId = array_shift($queue);
        session([self::SESSION_QUEUE => $queue]);

        return $wordId;
    }

    public function buildQuestion(int $wordId, array $selectedTags): array
    {
        $word = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags'])->findOrFail($wordId);

        $questionType = random_int(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';
        $correctAnswer = $questionType === 'en_to_uk'
            ? optional($word->translates->first())->translation ?? ''
            : $word->word;

        $otherWords = $this->getWordsQuery($selectedTags)
            ->with(['translates' => fn ($q) => $q->where('lang', 'uk')])
            ->where('id', '!=', $word->id)
            ->inRandomOrder()
            ->take(5)
            ->get();

        $options = $questionType === 'en_to_uk'
            ? $otherWords->map(fn ($w) => optional($w->translates->first())->translation ?? '')->toArray()
            : $otherWords->pluck('word')->toArray();

        $options[] = $correctAnswer;

        $options = collect($options)
            ->filter()
            ->unique()
            ->shuffle()
            ->take(6)
            ->values()
            ->toArray();

        if (! in_array($correctAnswer, $options, true)) {
            $options[] = $correctAnswer;
            $options = collect($options)->shuffle()->take(6)->values()->toArray();
        }

        return [
            'word' => [
                'id' => $word->id,
                'word' => $word->word,
                'translation' => optional($word->translates->first())->translation ?? '',
                'tags' => $word->tags->pluck('name')->toArray(),
            ],
            'question_type' => $questionType,
            'options' => $options,
            'correct_answer' => $correctAnswer,
        ];
    }

    private function getWordsQuery(array $selectedTags)
    {
        return Word::query()
            ->when(! empty($selectedTags), fn ($query) => $query->whereHas('tags', fn ($q) => $q->whereIn('name', $selectedTags)));
    }
}
