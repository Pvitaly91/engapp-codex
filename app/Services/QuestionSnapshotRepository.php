<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class QuestionSnapshotRepository
{
    private const DIRECTORY = 'questions';

    /**
     * Ensure a snapshot exists for the given question and return its payload.
     */
    public function sync(Question $question): array
    {
        $question->loadMissing([
            'answers.option',
            'options',
            'variants',
            'verbHints.option',
            'hints',
            'tags',
        ]);

        $payload = [
            'id' => $question->id,
            'uuid' => $question->uuid,
            'question' => $question->question,
            'difficulty' => $question->difficulty,
            'level' => $question->level,
            'category_id' => $question->category_id,
            'source_id' => $question->source_id,
            'flag' => $question->flag,
            'synced_at' => now()->toIso8601String(),
            'options' => $question->options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'option' => $option->option,
                ];
            })->values()->all(),
            'answers' => $question->answers->map(function ($answer) {
                $option = $answer->option;

                return [
                    'id' => $answer->id,
                    'marker' => $answer->marker,
                    'option_id' => $answer->option_id,
                    'option' => $option ? [
                        'id' => $option->id,
                        'option' => $option->option,
                    ] : null,
                ];
            })->values()->all(),
            'variants' => $question->relationLoaded('variants')
                ? $question->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'text' => $variant->text,
                    ];
                })->values()->all()
                : [],
            'verb_hints' => $question->verbHints->map(function ($hint) {
                $option = $hint->option;

                return [
                    'id' => $hint->id,
                    'marker' => $hint->marker,
                    'option_id' => $hint->option_id,
                    'option' => $option ? [
                        'id' => $option->id,
                        'option' => $option->option,
                    ] : null,
                ];
            })->values()->all(),
            'hints' => $question->hints->map(function ($hint) {
                return [
                    'id' => $hint->id,
                    'provider' => $hint->provider,
                    'locale' => $hint->locale,
                    'hint' => $hint->hint,
                ];
            })->values()->all(),
            'tags' => $question->relationLoaded('tags')
                ? $question->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                })->values()->all()
                : [],
        ];

        $this->write($question->id, $payload);

        return $payload;
    }

    /**
     * Retrieve a snapshot for the given question ID if it exists.
     */
    public function get(int $questionId): ?array
    {
        $path = $this->path($questionId);

        if (! $this->disk()->exists($path)) {
            return null;
        }

        $contents = $this->disk()->get($path);
        $data = json_decode($contents, true);

        return is_array($data) ? $data : null;
    }

    /**
     * Retrieve the snapshot for a question, generating it if missing and the question exists.
     */
    public function getOrCreate(int $questionId): ?array
    {
        $snapshot = $this->get($questionId);

        if ($snapshot) {
            return $snapshot;
        }

        $question = Question::with([
            'answers.option',
            'options',
            'variants',
            'verbHints.option',
            'hints',
            'tags',
        ])->find($questionId);

        if (! $question) {
            return null;
        }

        return $this->sync($question);
    }

    public function delete(int $questionId): void
    {
        $path = $this->path($questionId);

        if ($this->disk()->exists($path)) {
            $this->disk()->delete($path);
        }
    }

    public function all(array $questionIds): array
    {
        $snapshots = [];

        foreach ($questionIds as $questionId) {
            $snapshot = $this->get((int) $questionId);

            if ($snapshot) {
                $snapshots[(int) $questionId] = $snapshot;
            }
        }

        return $snapshots;
    }

    private function write(int $questionId, array $payload): void
    {
        $path = $this->path($questionId);

        $this->disk()->makeDirectory(self::DIRECTORY);
        $this->disk()->put(
            $path,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function path(int $questionId): string
    {
        return self::DIRECTORY . '/' . $questionId . '.json';
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk('test_tech');
    }
}
