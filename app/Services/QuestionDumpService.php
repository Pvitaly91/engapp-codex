<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\VerbHint;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class QuestionDumpService
{
    private string $directory;

    public function __construct()
    {
        $this->directory = storage_path('question-dumps');
    }

    public function getEffectivePayload(Question $question): array
    {
        return $this->getDraft($question) ?? $this->buildDataset($question);
    }

    public function buildDataset(Question $question): array
    {
        $question->loadMissing(['answers.option', 'options', 'verbHints.option', 'hints', 'variants']);

        $payload = [
            'question' => [
                'id' => $question->id,
                'uuid' => $question->uuid,
                'question' => $question->question,
                'difficulty' => $question->difficulty,
                'level' => $question->level,
                'flag' => $question->flag,
                'category_id' => $question->category_id,
                'source_id' => $question->source_id,
            ],
            'options' => $question->options
                ->map(fn (QuestionOption $option) => [
                    'id' => $option->id,
                    'value' => $option->option,
                ])
                ->unique('id')
                ->values()
                ->toArray(),
            'answers' => $question->answers
                ->map(fn (QuestionAnswer $answer) => [
                    'id' => $answer->id,
                    'marker' => $answer->marker,
                    'option_id' => $answer->option_id,
                    'value' => $answer->option?->option,
                ])
                ->values()
                ->toArray(),
            'verb_hints' => $question->verbHints
                ->map(fn (VerbHint $hint) => [
                    'id' => $hint->id,
                    'marker' => $hint->marker,
                    'option_id' => $hint->option_id,
                    'value' => $hint->option?->option,
                ])
                ->values()
                ->toArray(),
            'variants' => $question->variants
                ->map(fn (QuestionVariant $variant) => [
                    'id' => $variant->id,
                    'text' => $variant->text,
                ])
                ->values()
                ->toArray(),
            'hints' => $question->hints
                ->map(fn (QuestionHint $hint) => [
                    'id' => $hint->id,
                    'provider' => $hint->provider,
                    'locale' => $hint->locale,
                    'hint' => $hint->hint,
                ])
                ->values()
                ->toArray(),
        ];

        return $this->withMeta($question, $payload, false);
    }

    public function storeDraft(Question $question, array $payload): array
    {
        $this->ensureDirectory();

        $normalized = $this->normalizePayload($question, $payload);
        $normalized = $this->withMeta($question, $normalized, true);

        File::put($this->pathFor($question), json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $normalized;
    }

    public function getDraft(Question $question): ?array
    {
        $path = $this->pathFor($question);

        if (! File::exists($path)) {
            return null;
        }

        $data = json_decode(File::get($path), true);

        if (! is_array($data)) {
            return null;
        }

        $updatedAt = Arr::get($data, 'meta.updated_at');

        return $this->withMeta($question, $data, true, $updatedAt);
    }

    public function applyDraft(Question $question): array
    {
        $draft = $this->getDraft($question);

        if (! $draft) {
            throw ValidationException::withMessages([
                'draft' => 'Для цього питання немає збережених змін.',
            ]);
        }

        $this->validateDraftForApply($draft);

        DB::transaction(function () use ($question, $draft) {
            $this->applyQuestionAttributes($question, $draft['question'] ?? []);

            $optionCache = [];
            $optionModels = $this->collectOptionModels($question, $draft, $optionCache);

            $this->syncAnswers($question, $draft['answers'] ?? [], $optionCache);
            $this->syncVerbHints($question, $draft['verb_hints'] ?? [], $optionCache);
            $this->syncVariants($question, $draft['variants'] ?? []);
            $this->syncHints($question, $draft['hints'] ?? []);

            $this->syncQuestionOptions($question, $optionModels);
        });

        File::delete($this->pathFor($question));

        $question->refresh();

        return $this->buildDataset($question);
    }

    private function ensureDirectory(): void
    {
        if (! File::isDirectory($this->directory)) {
            File::makeDirectory($this->directory, 0755, true);
        }
    }

    private function pathFor(Question $question): string
    {
        return $this->directory . '/' . $question->uuid . '.json';
    }

    private function relativePath(Question $question): string
    {
        return 'storage/question-dumps/' . $question->uuid . '.json';
    }

    private function withMeta(Question $question, array $payload, bool $hasDraft, ?string $timestamp = null): array
    {
        $meta = $payload['meta'] ?? [];
        $meta['has_draft'] = $hasDraft;
        $meta['file_path'] = $this->relativePath($question);
        $meta['source'] = $hasDraft ? 'draft' : 'database';
        $meta['updated_at'] = $timestamp ?? now()->toIso8601String();
        $payload['meta'] = $meta;

        return $payload;
    }

    private function normalizePayload(Question $question, array $payload): array
    {
        $questionData = $payload['question'] ?? [];

        $normalizedQuestion = [
            'id' => $question->id,
            'uuid' => $question->uuid,
            'question' => (string) ($questionData['question'] ?? $question->question),
            'difficulty' => isset($questionData['difficulty']) ? (int) $questionData['difficulty'] : $question->difficulty,
            'level' => $questionData['level'] ?? $question->level,
            'flag' => isset($questionData['flag']) ? (int) $questionData['flag'] : $question->flag,
            'category_id' => $questionData['category_id'] ?? $question->category_id,
            'source_id' => $questionData['source_id'] ?? $question->source_id,
        ];

        return [
            'question' => $normalizedQuestion,
            'options' => $this->normalizeCollection($payload['options'] ?? [], ['id', 'value']),
            'answers' => $this->normalizeCollection($payload['answers'] ?? [], ['id', 'marker', 'value', 'option_id']),
            'verb_hints' => $this->normalizeCollection($payload['verb_hints'] ?? [], ['id', 'marker', 'value', 'option_id']),
            'variants' => $this->normalizeCollection($payload['variants'] ?? [], ['id', 'text']),
            'hints' => $this->normalizeCollection($payload['hints'] ?? [], ['id', 'provider', 'locale', 'hint']),
        ];
    }

    private function normalizeCollection(array $items, array $allowedKeys): array
    {
        return collect($items)
            ->map(function ($item) use ($allowedKeys) {
                $filtered = Arr::only((array) $item, $allowedKeys);

                return array_merge(array_fill_keys($allowedKeys, null), $filtered);
            })
            ->values()
            ->toArray();
    }

    private function validateDraftForApply(array $draft): void
    {
        $validator = Validator::make($draft, [
            'question.question' => ['required', 'string'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.marker' => ['required', 'string'],
            'answers.*.value' => ['required', 'string'],
            'options' => ['array'],
            'options.*.value' => ['nullable', 'string'],
            'verb_hints' => ['array'],
            'verb_hints.*.marker' => ['nullable', 'string'],
            'verb_hints.*.value' => ['nullable', 'string'],
            'variants' => ['array'],
            'variants.*.text' => ['nullable', 'string'],
            'hints' => ['array'],
            'hints.*.provider' => ['nullable', 'string'],
            'hints.*.locale' => ['nullable', 'string'],
            'hints.*.hint' => ['nullable', 'string'],
        ], [
            'question.question.required' => 'Текст питання обовʼязковий.',
            'answers.min' => 'Необхідно вказати щонайменше одну правильну відповідь.',
            'answers.*.marker.required' => 'У кожної відповіді має бути маркер.',
            'answers.*.value.required' => 'Кожна відповідь повинна містити значення.',
        ]);

        $validator->after(function ($validator) use ($draft) {
            $emptyOptions = collect($draft['options'] ?? [])
                ->filter(fn ($item) => trim((string) ($item['value'] ?? '')) === '');

            if ($emptyOptions->isNotEmpty()) {
                $validator->errors()->add('options', 'У варіантах відповіді є порожні значення. Видаліть їх або заповніть.');
            }

            $invalidVerbHints = collect($draft['verb_hints'] ?? [])
                ->filter(function ($item) {
                    $marker = trim((string) ($item['marker'] ?? ''));
                    $value = trim((string) ($item['value'] ?? ''));

                    if ($marker === '' && $value === '') {
                        return false;
                    }

                    return $marker === '' || $value === '';
                });

            if ($invalidVerbHints->isNotEmpty()) {
                $validator->errors()->add('verb_hints', 'Verb hints мають містити і маркер, і значення.');
            }

            $invalidHints = collect($draft['hints'] ?? [])
                ->filter(function ($item) {
                    $provider = trim((string) ($item['provider'] ?? ''));
                    $locale = trim((string) ($item['locale'] ?? ''));
                    $hint = trim((string) ($item['hint'] ?? ''));

                    if ($provider === '' && $locale === '' && $hint === '') {
                        return false;
                    }

                    return $provider === '' || $locale === '' || $hint === '';
                });

            if ($invalidHints->isNotEmpty()) {
                $validator->errors()->add('hints', 'Кожна підказка повинна містити провайдера, мову та текст.');
            }
        });

        $validator->validate();
    }

    private function applyQuestionAttributes(Question $question, array $attributes): void
    {
        $question->question = trim((string) ($attributes['question'] ?? $question->question));
        $question->difficulty = isset($attributes['difficulty']) ? (int) $attributes['difficulty'] : $question->difficulty;
        $level = $attributes['level'] ?? null;
        $question->level = $level !== '' ? $level : null;
        $question->flag = isset($attributes['flag']) ? (int) $attributes['flag'] : $question->flag;
        $question->category_id = $attributes['category_id'] ?? $question->category_id;
        $question->source_id = $attributes['source_id'] ?? $question->source_id;
        $question->save();
    }

    private function collectOptionModels(Question $question, array $draft, array &$cache): Collection
    {
        $options = collect();

        foreach ($draft['options'] ?? [] as $option) {
            $value = trim((string) ($option['value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $options->push($this->resolveOption($option['id'] ?? null, $value, $cache));
        }

        foreach ($draft['answers'] ?? [] as $answer) {
            $value = trim((string) ($answer['value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $options->push($this->resolveOption($answer['option_id'] ?? null, $value, $cache));
        }

        foreach ($draft['verb_hints'] ?? [] as $hint) {
            $value = trim((string) ($hint['value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $options->push($this->resolveOption($hint['option_id'] ?? null, $value, $cache));
        }

        return $options->filter()
            ->unique(fn (QuestionOption $option) => $option->id)
            ->values();
    }

    private function resolveOption(?int $id, string $value, array &$cache): QuestionOption
    {
        $value = trim($value);

        if (isset($cache[$value])) {
            return $cache[$value];
        }

        if ($id) {
            $option = QuestionOption::find($id);
            if ($option && trim($option->option) === $value) {
                return $cache[$value] = $option;
            }
        }

        $option = QuestionOption::firstOrCreate(['option' => $value]);

        return $cache[$value] = $option;
    }

    private function syncQuestionOptions(Question $question, Collection $options): void
    {
        $syncData = $options->mapWithKeys(function (QuestionOption $option) {
            return [$option->id => ['flag' => 0]];
        })->all();

        $question->options()->sync($syncData);
    }

    private function syncAnswers(Question $question, array $answers, array &$optionCache): void
    {
        $existing = $question->answers()->get()->keyBy('id');
        $idsToKeep = [];

        foreach ($answers as $data) {
            $marker = strtoupper(trim((string) ($data['marker'] ?? '')));
            $value = trim((string) ($data['value'] ?? ''));

            if ($marker === '' || $value === '') {
                continue;
            }

            $option = $this->resolveOption($data['option_id'] ?? null, $value, $optionCache);

            if (! empty($data['id']) && $existing->has($data['id'])) {
                $answer = $existing->get($data['id']);
                $answer->marker = $marker;
                $answer->option()->associate($option);
                $answer->save();
            } else {
                $answer = $question->answers()->create([
                    'marker' => $marker,
                    'option_id' => $option->id,
                ]);
            }

            $idsToKeep[] = $answer->id;
        }

        if (! empty($idsToKeep)) {
            $question->answers()->whereNotIn('id', $idsToKeep)->delete();
        } else {
            $question->answers()->delete();
        }
    }

    private function syncVerbHints(Question $question, array $verbHints, array &$optionCache): void
    {
        $existing = $question->verbHints()->get()->keyBy('id');
        $idsToKeep = [];

        foreach ($verbHints as $data) {
            $marker = strtoupper(trim((string) ($data['marker'] ?? '')));
            $value = trim((string) ($data['value'] ?? ''));

            if ($marker === '' && $value === '') {
                continue;
            }

            if ($marker === '' || $value === '') {
                continue;
            }

            $option = $this->resolveOption($data['option_id'] ?? null, $value, $optionCache);

            if (! empty($data['id']) && $existing->has($data['id'])) {
                $hint = $existing->get($data['id']);
                $hint->marker = $marker;
                $hint->option()->associate($option);
                $hint->save();
            } else {
                $hint = $question->verbHints()->create([
                    'marker' => $marker,
                    'option_id' => $option->id,
                ]);
            }

            $idsToKeep[] = $hint->id;
        }

        if (! empty($idsToKeep)) {
            $question->verbHints()->whereNotIn('id', $idsToKeep)->delete();
        } else {
            $question->verbHints()->delete();
        }
    }

    private function syncVariants(Question $question, array $variants): void
    {
        $existing = $question->variants()->get()->keyBy('id');
        $idsToKeep = [];

        foreach ($variants as $data) {
            $text = trim((string) ($data['text'] ?? ''));

            if ($text === '') {
                continue;
            }

            if (! empty($data['id']) && $existing->has($data['id'])) {
                $variant = $existing->get($data['id']);
                $variant->text = $text;
                $variant->save();
            } else {
                $variant = $question->variants()->create(['text' => $text]);
            }

            $idsToKeep[] = $variant->id;
        }

        if (! empty($idsToKeep)) {
            $question->variants()->whereNotIn('id', $idsToKeep)->delete();
        } else {
            $question->variants()->delete();
        }
    }

    private function syncHints(Question $question, array $hints): void
    {
        $existing = $question->hints()->get()->keyBy('id');
        $idsToKeep = [];

        foreach ($hints as $data) {
            $provider = trim((string) ($data['provider'] ?? ''));
            $locale = trim((string) ($data['locale'] ?? ''));
            $hintText = trim((string) ($data['hint'] ?? ''));

            if ($provider === '' && $locale === '' && $hintText === '') {
                continue;
            }

            if (! empty($data['id']) && $existing->has($data['id'])) {
                $hint = $existing->get($data['id']);
                $hint->provider = $provider;
                $hint->locale = $locale;
                $hint->hint = $hintText;
                $hint->save();
            } else {
                $hint = $question->hints()->create([
                    'provider' => $provider,
                    'locale' => $locale,
                    'hint' => $hintText,
                ]);
            }

            $idsToKeep[] = $hint->id;
        }

        if (! empty($idsToKeep)) {
            $question->hints()->whereNotIn('id', $idsToKeep)->delete();
        } else {
            $question->hints()->delete();
        }
    }
}
