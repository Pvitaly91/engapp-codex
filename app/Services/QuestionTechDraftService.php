<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\VerbHint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QuestionTechDraftService
{
    private const DIRECTORY = 'storage/question-dumps';

    /**
     * Returns the current draft for the question. If the draft file exists it will
     * be used, otherwise the data is built from the database.
     */
    public function getDraft(Question $question): array
    {
        $question = $this->ensureUuid($question);
        $path = $this->getDumpPath($question);

        if (File::exists($path)) {
            $decoded = json_decode(File::get($path), true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $dump = $this->makeDumpFromModel($question);

        return $dump + ['meta' => [
            'source' => 'database',
            'exported_at' => now()->toIso8601String(),
        ]];
    }

    /**
     * Persists the provided payload as a draft and returns the stored data.
     */
    public function storeDraftFromArray(Question $question, array $payload, ?string $testSlug = null): array
    {
        $question = $this->ensureUuid($question);

        $normalized = $this->normalizePayload($question, $payload);

        $data = [
            'question' => $normalized,
            'meta' => [
                'source' => 'draft',
                'exported_at' => now()->toIso8601String(),
                'test_slug' => $testSlug,
            ],
        ];

        $this->writeDump($question, $data);

        return $data;
    }

    /**
     * Applies the stored draft to the database and updates the draft file to the
     * latest database state.
     */
    public function applyDraft(Question $question): array
    {
        $question = $this->ensureUuid($question);
        $path = $this->getDumpPath($question);

        if (! File::exists($path)) {
            // Nothing to apply, just regenerate dump from database state.
            $dump = $this->makeDumpFromModel($question);
            $data = $dump + ['meta' => [
                'source' => 'database',
                'exported_at' => now()->toIso8601String(),
            ]];
            $this->writeDump($question, $data);

            return $data;
        }

        $contents = json_decode(File::get($path), true);
        $questionData = Arr::get($contents, 'question', []);

        DB::transaction(function () use ($question, $questionData) {
            $this->applyQuestionAttributes($question, $questionData);
            $optionMap = $this->applyOptions($question, $questionData);
            $this->applyAnswers($question, $questionData, $optionMap);
            $this->applyVerbHints($question, $questionData, $optionMap);
            $this->applyVariants($question, $questionData);
            $this->applyHints($question, $questionData);
        });

        $question->refresh()->load([
            'answers.option',
            'options',
            'verbHints.option',
            'hints',
            'variants',
        ]);

        $dump = $this->makeDumpFromModel($question);
        $data = $dump + ['meta' => [
            'source' => 'database',
            'exported_at' => now()->toIso8601String(),
        ]];

        $this->writeDump($question, $data);

        return $data;
    }

    /**
     * Builds a dump from the current database state.
     */
    public function makeDumpFromModel(Question $question): array
    {
        $relations = [
            'answers.option',
            'options',
            'verbHints.option',
            'hints',
            'variants',
        ];

        if (Schema::hasTable('tags')) {
            $relations[] = 'tags';
        }

        $question->loadMissing($relations);

        $options = $question->options->map(function (QuestionOption $option) {
            return [
                'id' => $option->id,
                'option' => $option->option,
            ];
        })->values();

        $optionTexts = $options->pluck('option')->map(fn ($value) => trim((string) $value))->filter()->all();

        $answers = $question->answers->map(function (QuestionAnswer $answer) use (&$options, &$optionTexts) {
            $optionText = optional($answer->option)->option;

            if ($optionText !== null && ! in_array($optionText, $optionTexts, true)) {
                $options->push([
                    'id' => optional($answer->option)->id,
                    'option' => $optionText,
                ]);
                $optionTexts[] = $optionText;
            }

            return [
                'id' => $answer->id,
                'marker' => $answer->marker,
                'option' => $optionText,
            ];
        })->values();

        $verbHints = $question->verbHints->map(function (VerbHint $hint) use (&$options, &$optionTexts) {
            $optionText = optional($hint->option)->option;

            if ($optionText !== null && ! in_array($optionText, $optionTexts, true)) {
                $options->push([
                    'id' => optional($hint->option)->id,
                    'option' => $optionText,
                ]);
                $optionTexts[] = $optionText;
            }

            return [
                'id' => $hint->id,
                'marker' => $hint->marker,
                'option' => $optionText,
            ];
        })->values();

        $variants = $question->variants->map(function (QuestionVariant $variant) {
            return [
                'id' => $variant->id,
                'text' => $variant->text,
            ];
        })->values();

        $hints = $question->hints->map(function (QuestionHint $hint) {
            return [
                'id' => $hint->id,
                'provider' => $hint->provider,
                'locale' => $hint->locale,
                'hint' => $hint->hint,
            ];
        })->values();

        return [
            'question' => [
                'id' => $question->id,
                'uuid' => $question->uuid,
                'question' => $question->question,
                'difficulty' => $question->difficulty,
                'level' => $question->level,
                'flag' => $question->flag,
                'category_id' => $question->category_id,
                'source_id' => $question->source_id,
                'options' => $options->unique('option')->values()->all(),
                'answers' => $answers->all(),
                'verb_hints' => $verbHints->all(),
                'variants' => $variants->all(),
                'hints' => $hints->all(),
                'tags' => $question->relationLoaded('tags')
                    ? $question->tags->map(fn ($tag) => [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ])->values()->all()
                    : [],
            ],
        ];
    }

    /**
     * Ensures the storage directory exists and writes the dump to the disk.
     */
    private function writeDump(Question $question, array $payload): void
    {
        $path = $this->getDumpPath($question);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function getDumpPath(Question $question): string
    {
        return base_path(self::DIRECTORY . '/' . $question->uuid . '.json');
    }

    private function ensureUuid(Question $question): Question
    {
        if (! $question->uuid) {
            $question->uuid = (string) Str::uuid();
            $question->save();
        }

        return $question;
    }

    private function normalizePayload(Question $question, array $payload): array
    {
        $options = collect(Arr::get($payload, 'options', []))
            ->map(function ($item) {
                $text = trim((string) ($item['option'] ?? ''));

                return [
                    'id' => isset($item['id']) ? (int) $item['id'] : null,
                    'option' => $text,
                ];
            })
            ->filter(fn ($item) => $item['option'] !== '')
            ->unique('option')
            ->values()
            ->all();

        $answers = collect(Arr::get($payload, 'answers', []))
            ->map(function ($item) {
                $marker = trim((string) ($item['marker'] ?? ''));
                $option = trim((string) ($item['option'] ?? ''));

                return [
                    'id' => isset($item['id']) ? (int) $item['id'] : null,
                    'marker' => $marker,
                    'option' => $option,
                ];
            })
            ->filter(fn ($item) => $item['marker'] !== '' && $item['option'] !== '')
            ->values()
            ->all();

        $verbHints = collect(Arr::get($payload, 'verb_hints', []))
            ->map(function ($item) {
                $marker = trim((string) ($item['marker'] ?? ''));
                $option = trim((string) ($item['option'] ?? ''));

                return [
                    'id' => isset($item['id']) ? (int) $item['id'] : null,
                    'marker' => $marker,
                    'option' => $option,
                ];
            })
            ->filter(fn ($item) => $item['marker'] !== '' && $item['option'] !== '')
            ->values()
            ->all();

        $variants = collect(Arr::get($payload, 'variants', []))
            ->map(function ($item) {
                $text = trim((string) ($item['text'] ?? ''));

                return [
                    'id' => isset($item['id']) ? (int) $item['id'] : null,
                    'text' => $text,
                ];
            })
            ->filter(fn ($item) => $item['text'] !== '')
            ->values()
            ->all();

        $hints = collect(Arr::get($payload, 'hints', []))
            ->map(function ($item) {
                $provider = trim((string) ($item['provider'] ?? ''));
                $locale = trim((string) ($item['locale'] ?? ''));
                $hint = trim((string) ($item['hint'] ?? ''));

                return [
                    'id' => isset($item['id']) ? (int) $item['id'] : null,
                    'provider' => $provider,
                    'locale' => $locale,
                    'hint' => $hint,
                ];
            })
            ->filter(fn ($item) => $item['provider'] !== '' && $item['locale'] !== '' && $item['hint'] !== '')
            ->values()
            ->all();

        return [
            'id' => $question->id,
            'uuid' => $question->uuid,
            'question' => (string) Arr::get($payload, 'question', ''),
            'difficulty' => $this->nullableValue(Arr::get($payload, 'difficulty')),
            'level' => $this->nullableValue(Arr::get($payload, 'level')),
            'flag' => $this->nullableInteger(Arr::get($payload, 'flag')),
            'category_id' => $this->nullableInteger(Arr::get($payload, 'category_id')),
            'source_id' => $this->nullableInteger(Arr::get($payload, 'source_id')),
            'options' => $options,
            'answers' => $answers,
            'verb_hints' => $verbHints,
            'variants' => $variants,
            'hints' => $hints,
            'tags' => collect(Arr::get($payload, 'tags', []))
                ->map(fn ($tag) => [
                    'id' => isset($tag['id']) ? (int) $tag['id'] : null,
                    'name' => (string) ($tag['name'] ?? ''),
                ])
                ->filter(fn ($tag) => $tag['id'] !== null || $tag['name'] !== '')
                ->values()
                ->all(),
        ];
    }

    private function applyQuestionAttributes(Question $question, array $payload): void
    {
        $question->fill([
            'question' => (string) Arr::get($payload, 'question', $question->question),
            'difficulty' => $this->nullableValue(Arr::get($payload, 'difficulty')),
            'level' => $this->nullableValue(Arr::get($payload, 'level')),
            'flag' => $this->nullableInteger(Arr::get($payload, 'flag')),
            'category_id' => $this->nullableInteger(Arr::get($payload, 'category_id')),
            'source_id' => $this->nullableInteger(Arr::get($payload, 'source_id')),
        ]);

        $question->save();
    }

    /**
     * Returns a map of option text to the QuestionOption model instance.
     *
     * @return array<string, QuestionOption>
     */
    private function applyOptions(Question $question, array $payload): array
    {
        $options = collect(Arr::get($payload, 'options', []))
            ->map(fn ($item) => trim((string) ($item['option'] ?? '')))
            ->filter()
            ->unique()
            ->values();

        $answerOptions = collect(Arr::get($payload, 'answers', []))
            ->map(fn ($item) => trim((string) ($item['option'] ?? '')))
            ->filter();

        $verbHintOptions = collect(Arr::get($payload, 'verb_hints', []))
            ->map(fn ($item) => trim((string) ($item['option'] ?? '')))
            ->filter();

        $allOptions = $options
            ->merge($answerOptions)
            ->merge($verbHintOptions)
            ->filter()
            ->unique()
            ->values();

        $optionMap = [];
        $optionIds = [];

        foreach ($allOptions as $text) {
            /** @var QuestionOption $model */
            $model = QuestionOption::firstOrCreate(['option' => $text]);
            $optionMap[$text] = $model;
            $optionIds[$model->id] = ['flag' => 0];
        }

        if (! empty($optionIds)) {
            $question->options()->sync($optionIds);
        } else {
            $question->options()->detach();
        }

        return $optionMap;
    }

    private function applyAnswers(Question $question, array $payload, array $optionMap): void
    {
        $answers = collect(Arr::get($payload, 'answers', []));

        $keptIds = [];

        foreach ($answers as $item) {
            $marker = trim((string) ($item['marker'] ?? ''));
            $option = trim((string) ($item['option'] ?? ''));

            if ($marker === '' || $option === '' || ! isset($optionMap[$option])) {
                continue;
            }

            $attributes = [
                'marker' => $marker,
                'option_id' => $optionMap[$option]->id,
            ];

            if (! empty($item['id'])) {
                $answer = QuestionAnswer::where('id', $item['id'])->where('question_id', $question->id)->first();

                if ($answer) {
                    $answer->fill($attributes);
                    $answer->save();
                    $keptIds[] = $answer->id;

                    continue;
                }
            }

            $answer = $question->answers()->create($attributes);
            $keptIds[] = $answer->id;
        }

        if (! empty($keptIds)) {
            $question->answers()->whereNotIn('id', $keptIds)->delete();
        } else {
            $question->answers()->delete();
        }
    }

    private function applyVerbHints(Question $question, array $payload, array $optionMap): void
    {
        $verbHints = collect(Arr::get($payload, 'verb_hints', []));
        $keptIds = [];

        foreach ($verbHints as $item) {
            $marker = trim((string) ($item['marker'] ?? ''));
            $option = trim((string) ($item['option'] ?? ''));

            if ($marker === '' || $option === '' || ! isset($optionMap[$option])) {
                continue;
            }

            $attributes = [
                'marker' => $marker,
                'option_id' => $optionMap[$option]->id,
            ];

            if (! empty($item['id'])) {
                $verbHint = VerbHint::where('id', $item['id'])->where('question_id', $question->id)->first();

                if ($verbHint) {
                    $verbHint->fill($attributes);
                    $verbHint->save();
                    $keptIds[] = $verbHint->id;

                    continue;
                }
            }

            $verbHint = $question->verbHints()->create($attributes);
            $keptIds[] = $verbHint->id;
        }

        if (! empty($keptIds)) {
            $question->verbHints()->whereNotIn('id', $keptIds)->delete();
        } else {
            $question->verbHints()->delete();
        }
    }

    private function applyVariants(Question $question, array $payload): void
    {
        $variants = collect(Arr::get($payload, 'variants', []));
        $keptIds = [];

        foreach ($variants as $item) {
            $text = trim((string) ($item['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            if (! empty($item['id'])) {
                $variant = QuestionVariant::where('id', $item['id'])->where('question_id', $question->id)->first();

                if ($variant) {
                    $variant->text = $text;
                    $variant->save();
                    $keptIds[] = $variant->id;

                    continue;
                }
            }

            $variant = $question->variants()->create(['text' => $text]);
            $keptIds[] = $variant->id;
        }

        if (! empty($keptIds)) {
            $question->variants()->whereNotIn('id', $keptIds)->delete();
        } else {
            $question->variants()->delete();
        }
    }

    private function applyHints(Question $question, array $payload): void
    {
        $hints = collect(Arr::get($payload, 'hints', []));
        $keptIds = [];

        foreach ($hints as $item) {
            $provider = trim((string) ($item['provider'] ?? ''));
            $locale = trim((string) ($item['locale'] ?? ''));
            $hintText = trim((string) ($item['hint'] ?? ''));

            if ($provider === '' || $locale === '' || $hintText === '') {
                continue;
            }

            $attributes = [
                'provider' => $provider,
                'locale' => $locale,
                'hint' => $hintText,
            ];

            if (! empty($item['id'])) {
                $hint = QuestionHint::where('id', $item['id'])->where('question_id', $question->id)->first();

                if ($hint) {
                    $hint->fill($attributes);
                    $hint->save();
                    $keptIds[] = $hint->id;

                    continue;
                }
            }

            $hint = $question->hints()->create($attributes);
            $keptIds[] = $hint->id;
        }

        if (! empty($keptIds)) {
            $question->hints()->whereNotIn('id', $keptIds)->delete();
        } else {
            $question->hints()->delete();
        }
    }

    private function nullableValue(mixed $value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }
}
