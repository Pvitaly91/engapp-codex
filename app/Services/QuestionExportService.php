<?php

namespace App\Services;

use App\Models\Question;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class QuestionExportService
{
    public function export(Question $question): void
    {
        if (blank($question->uuid)) {
            $question->uuid = (string) Str::uuid();
            $question->saveQuietly();
        }

        $question->loadMissing([
            'category',
            'source',
            'tags',
            'options',
            'answers.option',
            'verbHints.option',
            'variants',
            'hints',
            'chatgptExplanations',
        ]);

        $payload = [
            'question' => $this->formatQuestion($question),
            'category' => $question->category ? $this->formatCategory($question) : null,
            'source' => $question->source ? $this->formatSource($question) : null,
            'tags' => $this->formatTags($question),
            'options' => $this->formatOptions($question),
            'answers' => $this->formatAnswers($question),
            'verb_hints' => $this->formatVerbHints($question),
            'variants' => $this->formatVariants($question),
            'hints' => $this->formatHints($question),
            'chatgpt_explanations' => $this->formatChatGptExplanations($question),
        ];

        $this->writeJson($question->uuid, $payload);
    }

    private function formatQuestion(Question $question): array
    {
        return [
            'id' => $question->id,
            'uuid' => $question->uuid,
            'question' => $question->question,
            'difficulty' => $question->difficulty,
            'level' => $question->level,
            'category_id' => $question->category_id,
            'source_id' => $question->source_id,
            'flag' => $question->flag,
            'created_at' => $this->formatDate($question->created_at),
            'updated_at' => $this->formatDate($question->updated_at),
        ];
    }

    private function formatCategory(Question $question): array
    {
        return [
            'id' => $question->category->id,
            'name' => $question->category->name,
            'created_at' => $this->formatDate($question->category->created_at),
            'updated_at' => $this->formatDate($question->category->updated_at),
        ];
    }

    private function formatSource(Question $question): array
    {
        return [
            'id' => $question->source->id,
            'name' => $question->source->name,
            'created_at' => $this->formatDate($question->source->created_at),
            'updated_at' => $this->formatDate($question->source->updated_at),
        ];
    }

    private function formatTags(Question $question): array
    {
        return $question->tags->map(function ($tag) use ($question) {
            return [
                'tag' => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'category' => $tag->category,
                    'created_at' => $this->formatDate($tag->created_at),
                    'updated_at' => $this->formatDate($tag->updated_at),
                ],
                'pivot' => [
                    'question_id' => $tag->pivot?->question_id ?? $question->id,
                    'tag_id' => $tag->pivot?->tag_id ?? $tag->id,
                    'created_at' => $this->formatDate($tag->pivot?->created_at),
                    'updated_at' => $this->formatDate($tag->pivot?->updated_at),
                ],
            ];
        })->values()->all();
    }

    private function formatOptions(Question $question): array
    {
        return $question->options->map(function ($option) use ($question) {
            return [
                'option' => [
                    'id' => $option->id,
                    'option' => $option->option,
                    'created_at' => $this->formatDate($option->created_at),
                    'updated_at' => $this->formatDate($option->updated_at),
                ],
                'pivot' => [
                    'question_id' => $option->pivot?->question_id ?? $question->id,
                    'option_id' => $option->pivot?->option_id ?? $option->id,
                    'flag' => $option->pivot?->flag,
                ],
            ];
        })->values()->all();
    }

    private function formatAnswers(Question $question): array
    {
        return $question->answers->map(function ($answer) {
            return [
                'answer' => [
                    'id' => $answer->id,
                    'question_id' => $answer->question_id,
                    'marker' => $answer->marker,
                    'option_id' => $answer->option_id,
                    'created_at' => $this->formatDate($answer->created_at),
                    'updated_at' => $this->formatDate($answer->updated_at),
                ],
                'option' => $answer->option ? [
                    'id' => $answer->option->id,
                    'option' => $answer->option->option,
                    'created_at' => $this->formatDate($answer->option->created_at),
                    'updated_at' => $this->formatDate($answer->option->updated_at),
                ] : null,
            ];
        })->values()->all();
    }

    private function formatVerbHints(Question $question): array
    {
        return $question->verbHints->map(function ($hint) {
            return [
                'verb_hint' => [
                    'id' => $hint->id,
                    'question_id' => $hint->question_id,
                    'marker' => $hint->marker,
                    'option_id' => $hint->option_id,
                    'created_at' => $this->formatDate($hint->created_at),
                    'updated_at' => $this->formatDate($hint->updated_at),
                ],
                'option' => $hint->option ? [
                    'id' => $hint->option->id,
                    'option' => $hint->option->option,
                    'created_at' => $this->formatDate($hint->option->created_at),
                    'updated_at' => $this->formatDate($hint->option->updated_at),
                ] : null,
            ];
        })->values()->all();
    }

    private function formatVariants(Question $question): array
    {
        return $question->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'question_id' => $variant->question_id,
                'text' => $variant->text,
                'created_at' => $this->formatDate($variant->created_at),
                'updated_at' => $this->formatDate($variant->updated_at),
            ];
        })->values()->all();
    }

    private function formatHints(Question $question): array
    {
        return $question->hints->map(function ($hint) {
            return [
                'id' => $hint->id,
                'question_id' => $hint->question_id,
                'provider' => $hint->provider,
                'locale' => $hint->locale,
                'hint' => $hint->hint,
                'created_at' => $this->formatDate($hint->created_at),
                'updated_at' => $this->formatDate($hint->updated_at),
            ];
        })->values()->all();
    }

    private function formatChatGptExplanations(Question $question): array
    {
        return $question->chatgptExplanations->map(function ($explanation) {
            return [
                'id' => $explanation->id,
                'question' => $explanation->question,
                'wrong_answer' => $explanation->wrong_answer,
                'correct_answer' => $explanation->correct_answer,
                'language' => $explanation->language,
                'explanation' => $explanation->explanation,
                'created_at' => $this->formatDate($explanation->created_at),
                'updated_at' => $this->formatDate($explanation->updated_at),
            ];
        })->values()->all();
    }

    private function writeJson(string $uuid, array $payload): void
    {
        $directory = database_path('seeders/questions');
        File::ensureDirectoryExists($directory);

        $path = $directory . DIRECTORY_SEPARATOR . $uuid . '.json';
        File::put($path, json_encode($this->convertDates($payload), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    private function convertDates(array $payload): array
    {
        return json_decode(json_encode($payload), true);
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toISOString();
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value)->toISOString();
            } catch (\Throwable) {
                return $value;
            }
        }

        return null;
    }
}

