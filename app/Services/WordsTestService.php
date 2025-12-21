<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Word;

class WordsTestService
{
    public function fetchAvailableTags()
    {
        return Tag::whereHas('words')->get();
    }

    public function buildQueue(array $selectedTags): array
    {
        $words = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags'])
            ->when($selectedTags, fn ($q) => $q->whereHas('tags', fn ($q2) => $q2->whereIn('name', $selectedTags)))
            ->get();

        $queue = $words->pluck('id')->shuffle()->values()->all();

        return [$queue, count($queue)];
    }

    public function makeQuestion(int $wordId, array $selectedTags): array
    {
        $word = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk'), 'tags'])->find($wordId);

        if (! $word) {
            return [];
        }

        $questionType = rand(0, 1) === 0 ? 'en_to_uk' : 'uk_to_en';
        $options = $this->buildOptions($word, $questionType, $selectedTags);

        return [
            'word' => [
                'id' => $word->id,
                'word' => $word->word,
                'translation' => optional($word->translates->first())->translation ?? '',
            ],
            'tags' => $word->tags->pluck('name')->all(),
            'questionType' => $questionType,
            'options' => $options,
        ];
    }

    public function isCorrect(array $word, string $questionType, string $answer): bool
    {
        $correct = $questionType === 'en_to_uk'
            ? ($word['translation'] ?? '')
            : ($word['word'] ?? '');

        return trim($answer) === trim($correct);
    }

    protected function buildOptions(Word $word, string $questionType, array $selectedTags): array
    {
        $otherWords = Word::with(['translates' => fn ($q) => $q->where('lang', 'uk')])
            ->when($selectedTags, fn ($q) => $q->whereHas('tags', fn ($q2) => $q2->whereIn('name', $selectedTags)))
            ->where('id', '!=', $word->id)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $correct = $questionType === 'en_to_uk'
            ? optional($word->translates->first())->translation ?? ''
            : $word->word;

        $options = $questionType === 'en_to_uk'
            ? $otherWords->map(fn ($w) => optional($w->translates->first())->translation ?? '')->toArray()
            : $otherWords->pluck('word')->toArray();

        $options[] = $correct;

        shuffle($options);

        return array_values($options);
    }
}
