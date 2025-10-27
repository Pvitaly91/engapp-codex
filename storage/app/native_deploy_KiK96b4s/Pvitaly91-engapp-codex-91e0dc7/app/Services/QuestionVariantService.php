<?php

namespace App\Services;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\Test;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class QuestionVariantService
{
    private ?bool $supportsVariants = null;

    public function supportsVariants(): bool
    {
        if ($this->supportsVariants === null) {
            $this->supportsVariants = Schema::hasTable('question_variants');
        }

        return $this->supportsVariants;
    }

    public function clearForTest(string $slug): void
    {
        session()->forget($this->namespaceKey($slug));
    }

    public function getStoredVariants(string $slug): array
    {
        $variants = session($this->namespaceKey($slug));

        if (! is_array($variants)) {
            return [];
        }

        return $variants;
    }

    public function applyRandomVariant(Test|SavedGrammarTest $test, Question $question, ?string $previousVariant = null): void
    {
        if (! $this->supportsVariants()) {
            return;
        }

        if (! $question->relationLoaded('variants')) {
            $question->load('variants');
        }

        $variants = $question->variants
            ->pluck('text')
            ->filter()
            ->unique()
            ->values();

        if ($variants->isEmpty()) {
            session()->forget($this->questionKey($test->slug, $question->id));
            return;
        }

        $options = $variants->prepend($question->getOriginal('question'))
            ->filter()
            ->unique()
            ->values();

        $previous = $previousVariant ?? session($this->questionKey($test->slug, $question->id));

        if ($previous && $options->count() > 1) {
            $filtered = $options->reject(fn($text) => $text === $previous)->values();

            if ($filtered->isNotEmpty()) {
                $options = $filtered;
            }
        }

        $choice = $options->random();

        session()->put($this->questionKey($test->slug, $question->id), $choice);
        $question->setAttribute('question', $choice);
    }

    public function applyRandomVariants(Test|SavedGrammarTest $test, Collection $questions, array $previousVariants = []): Collection
    {
        if (! $this->supportsVariants()) {
            return $questions;
        }

        return $questions->map(function (Question $question) use ($test, $previousVariants) {
            $this->applyRandomVariant($test, $question, $previousVariants[$question->id] ?? null);

            return $question;
        });
    }

    public function applyStoredVariant(?string $slug, Question $question): void
    {
        if (! $this->supportsVariants()) {
            return;
        }

        if (! $slug) {
            return;
        }

        $variant = session($this->questionKey($slug, $question->id));
        if ($variant) {
            $question->setAttribute('question', $variant);
        }
    }

    private function namespaceKey(string $slug): string
    {
        return "test_variants.$slug";
    }

    private function questionKey(string $slug, int $questionId): string
    {
        return $this->namespaceKey($slug) . '.' . $questionId;
    }
}
