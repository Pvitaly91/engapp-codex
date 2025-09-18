<?php

namespace App\Services;

use App\Models\Question;
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

    public function applyRandomVariant(Test $test, Question $question): void
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
            ->unique()
            ->values();

        $choice = $options->random();

        session()->put($this->questionKey($test->slug, $question->id), $choice);
        $question->setAttribute('question', $choice);
    }

    public function applyRandomVariants(Test $test, Collection $questions): Collection
    {
        if (! $this->supportsVariants()) {
            return $questions;
        }

        return $questions->map(function (Question $question) use ($test) {
            $this->applyRandomVariant($test, $question);

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
