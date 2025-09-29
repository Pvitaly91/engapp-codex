<?php

namespace Database\Seeders;

use App\Models\ChatGPTExplanation;
use App\Models\Question;
use App\Models\QuestionHint;
use App\Services\QuestionSeedingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

abstract class QuestionSeeder extends Seeder
{
    protected const UUID_LENGTH = 36;

    protected function generateQuestionUuid(int|string ...$segments): string
    {
        $base = Str::slug(class_basename(static::class));

        $normalizedSegments = [];
        foreach ($segments as $segment) {
            $segment = Str::slug((string) $segment);
            if ($segment === '') {
                continue;
            }

            $normalizedSegments[] = $segment;
        }

        $suffix = '';
        if ($normalizedSegments) {
            $suffix = '-' . implode('-', $normalizedSegments);
        }

        $maxLength = self::UUID_LENGTH - strlen($suffix);

        if ($maxLength <= 0) {
            return substr(ltrim($suffix, '-'), 0, self::UUID_LENGTH);
        }

        $base = substr($base, 0, $maxLength);

        if ($base === '') {
            return substr(ltrim($suffix, '-'), 0, self::UUID_LENGTH);
        }

        return $base . $suffix;
    }

    protected function seedQuestionData(array $items, array $meta): void
    {
        if (empty($items)) {
            return;
        }

        $service = new QuestionSeedingService();
        $service->seed($items);

        foreach ($meta as $data) {
            $question = Question::where('uuid', $data['uuid'] ?? null)->first();

            if (! $question) {
                continue;
            }

            $hintText = $this->formatHints($data['hints'] ?? []);
            if ($hintText !== null) {
                QuestionHint::updateOrCreate(
                    ['question_id' => $question->id, 'provider' => 'chatgpt', 'locale' => 'uk'],
                    ['hint' => $hintText]
                );
            }

            $answers = $data['answers'] ?? [];
            $optionMarkers = $data['option_markers'] ?? [];

            foreach ($data['explanations'] ?? [] as $option => $text) {
                $marker = $optionMarkers[$option] ?? array_key_first($answers);
                $correct = $marker !== null ? ($answers[$marker] ?? reset($answers)) : reset($answers);

                if (! is_string($correct)) {
                    $correct = (string) $correct;
                }

                ChatGPTExplanation::updateOrCreate(
                    [
                        'question' => $question->question,
                        'wrong_answer' => $option,
                        'correct_answer' => $correct,
                        'language' => 'ua',
                    ],
                    ['explanation' => $text]
                );
            }
        }
    }

    protected function normalizeHint(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim($value, "() \t\n\r");
    }

    protected function formatHints(array $hints): ?string
    {
        if (empty($hints)) {
            return null;
        }

        $parts = [];
        foreach ($hints as $text) {
            $clean = trim((string) $text);

            if ($clean === '') {
                continue;
            }

            $parts[] = $clean;
        }

        if (empty($parts)) {
            return null;
        }

        return implode("\n", $parts);
    }

    protected function formatExample(string $question, string $answer): string
    {
        $sentence = str_replace('{a1}', $answer, $question);
        $sentence = preg_replace_callback('/^[a-zа-яёіїєґ]/iu', fn ($m) => mb_strtoupper($m[0], 'UTF-8'), $sentence);

        return $sentence;
    }

    protected function titleCase(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        $first = mb_substr($value, 0, 1, 'UTF-8');
        $rest = mb_substr($value, 1, null, 'UTF-8');

        return mb_strtoupper($first, 'UTF-8') . $rest;
    }
}
