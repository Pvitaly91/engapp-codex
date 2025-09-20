<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionDumpState;
use App\Models\QuestionHint;
use App\Models\QuestionOption;
use App\Models\QuestionVariant;
use App\Models\Source;
use App\Models\Tag;
use App\Models\VerbHint;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class QuestionDumpService
{
    private const DIRECTORY = 'database/question_dumps';

    public function storeDump(Question $question): void
    {
        $question = $this->loadQuestion($question);
        $this->ensureQuestionUuid($question);

        $payload = $this->buildPayload($question);
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $path = $this->getFilePath($question->uuid);
        $this->ensureDirectoryExists($path);
        File::put($path, $json);

        $this->markApplied($question->uuid, hash('sha256', $json));
    }

    public function getDumpInfo(Question $question): ?array
    {
        $question->refresh();
        if (empty($question->uuid)) {
            return null;
        }

        $path = $this->getFilePath($question->uuid);
        if (! File::exists($path)) {
            return null;
        }

        $json = File::get($path);
        $hash = hash('sha256', $json);
        $state = $this->tableExists('question_dump_states')
            ? QuestionDumpState::where('question_uuid', $question->uuid)->first()
            : null;
        $applied = $state && $state->file_hash === $hash;

        return [
            'uuid' => $question->uuid,
            'json' => $json,
            'hash' => $hash,
            'pending' => ! $applied,
            'applied_at' => $state?->applied_at,
            'path' => $path,
            'relative_path' => Str::after($path, base_path() . DIRECTORY_SEPARATOR),
        ];
    }

    public function applyDump(Question $question): void
    {
        $question->refresh();
        if (empty($question->uuid)) {
            throw new RuntimeException('Question does not have an associated UUID.');
        }

        $path = $this->getFilePath($question->uuid);
        if (! File::exists($path)) {
            throw new RuntimeException('Не знайдено файл з дампом для цього питання.');
        }

        $json = File::get($path);
        $payload = json_decode($json, true);
        if (! is_array($payload)) {
            throw new RuntimeException('Неможливо прочитати дамп питання.');
        }

        DB::transaction(function () use ($question, $payload) {
            $this->applyQuestionPayload($question, $payload);
        });

        $this->markApplied($question->uuid, hash('sha256', $json));
    }

    private function applyQuestionPayload(Question $question, array $payload): void
    {
        $attributes = Arr::get($payload, 'attributes', []);
        foreach (['question', 'difficulty', 'level', 'flag'] as $field) {
            if (array_key_exists($field, $attributes)) {
                $question->{$field} = $attributes[$field];
            }
        }

        if (! empty($payload['question_uuid']) && $question->uuid !== $payload['question_uuid']) {
            $question->uuid = $payload['question_uuid'];
        }

        $category = Arr::get($payload, 'category', []);
        $categoryId = $attributes['category_id'] ?? ($category['id'] ?? null);
        if ($categoryId) {
            $categoryModel = Category::find($categoryId);
            if (! $categoryModel && ! empty($category['name'])) {
                $categoryModel = Category::firstOrCreate(['name' => $category['name']]);
            }
            if ($categoryModel) {
                $question->category_id = $categoryModel->id;
            }
        } elseif (! empty($category['name'])) {
            $categoryModel = Category::firstOrCreate(['name' => $category['name']]);
            $question->category_id = $categoryModel->id;
        }

        $source = Arr::get($payload, 'source', []);
        $sourceId = $attributes['source_id'] ?? ($source['id'] ?? null);
        if ($sourceId) {
            $sourceModel = Source::find($sourceId);
            if (! $sourceModel && ! empty($source['name'])) {
                $sourceModel = Source::firstOrCreate(['name' => $source['name']]);
            }
            if ($sourceModel) {
                $question->source_id = $sourceModel->id;
            }
        } elseif (! empty($source['name'])) {
            $sourceModel = Source::firstOrCreate(['name' => $source['name']]);
            $question->source_id = $sourceModel->id;
        }

        $question->save();

        $optionMap = $this->syncOptions($question, $payload);
        $this->syncAnswers($question, $payload, $optionMap);
        $this->syncVerbHints($question, $payload, $optionMap);
        $this->syncTags($question, $payload);
        $this->syncHints($question, $payload);
        $this->syncVariants($question, $payload);
    }

    private function syncOptions(Question $question, array $payload): array
    {
        $optionEntries = collect(Arr::get($payload, 'options', []));
        $optionEntries = $optionEntries->filter(function ($entry) {
            return isset($entry['value']) && trim((string) $entry['value']) !== '';
        });

        $optionMap = [];
        foreach ($optionEntries as $entry) {
            $value = trim((string) $entry['value']);
            if (! array_key_exists($value, $optionMap)) {
                $option = QuestionOption::firstOrCreate(['option' => $value]);
                $optionMap[$value] = $option->id;
            }
        }

        foreach (Arr::get($payload, 'answers', []) as $answer) {
            $value = isset($answer['option']) ? trim((string) $answer['option']) : null;
            if ($value === null || $value === '') {
                continue;
            }
            if (! array_key_exists($value, $optionMap)) {
                $option = QuestionOption::firstOrCreate(['option' => $value]);
                $optionMap[$value] = $option->id;
            }
        }

        foreach (Arr::get($payload, 'verb_hints', []) as $hint) {
            $value = isset($hint['value']) ? trim((string) $hint['value']) : null;
            if ($value === null || $value === '') {
                continue;
            }
            if (! array_key_exists($value, $optionMap)) {
                $option = QuestionOption::firstOrCreate(['option' => $value]);
                $optionMap[$value] = $option->id;
            }
        }

        if ($this->tableExists('question_option_question')) {
            DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->delete();

            $inserted = [];
            foreach ($optionEntries as $entry) {
                $value = trim((string) $entry['value']);
                $flag = $entry['flag'] ?? null;
                $optionId = $optionMap[$value] ?? null;
                if (! $optionId) {
                    continue;
                }

                $key = $optionId . '|' . ($flag === null ? 'null' : (string) $flag);
                if (isset($inserted[$key])) {
                    continue;
                }

                DB::table('question_option_question')->insert([
                    'question_id' => $question->id,
                    'option_id' => $optionId,
                    'flag' => $flag,
                ]);
                $inserted[$key] = true;
            }
        }

        return $optionMap;
    }

    private function syncAnswers(Question $question, array $payload, array $optionMap): void
    {
        if (! $this->tableExists('question_answers')) {
            return;
        }

        $markers = [];
        foreach (Arr::get($payload, 'answers', []) as $entry) {
            $marker = $entry['marker'] ?? null;
            $value = isset($entry['option']) ? trim((string) $entry['option']) : null;
            if (! $marker || $value === null || $value === '') {
                continue;
            }

            $optionId = $optionMap[$value] ?? null;
            if (! $optionId) {
                $option = QuestionOption::firstOrCreate(['option' => $value]);
                $optionId = $option->id;
            }

            QuestionAnswer::updateOrCreate(
                ['question_id' => $question->id, 'marker' => $marker],
                ['option_id' => $optionId]
            );
            $markers[] = $marker;
        }

        if (! empty($markers)) {
            $question->answers()->whereNotIn('marker', $markers)->delete();
        } else {
            $question->answers()->delete();
        }
    }

    private function syncVerbHints(Question $question, array $payload, array $optionMap): void
    {
        if (! $this->tableExists('verb_hints')) {
            return;
        }

        $ids = [];
        foreach (Arr::get($payload, 'verb_hints', []) as $entry) {
            $marker = $entry['marker'] ?? null;
            $value = isset($entry['value']) ? trim((string) $entry['value']) : null;
            if (! $marker || $value === null || $value === '') {
                continue;
            }

            $optionId = $optionMap[$value] ?? null;
            if (! $optionId) {
                $option = QuestionOption::firstOrCreate(['option' => $value]);
                $optionId = $option->id;
            }

            $verbHint = VerbHint::updateOrCreate(
                ['question_id' => $question->id, 'marker' => $marker],
                ['option_id' => $optionId]
            );
            $ids[] = $verbHint->id;

            if ($this->tableExists('question_option_question')) {
                $existing = DB::table('question_option_question')
                    ->where('question_id', $question->id)
                    ->where('option_id', $optionId)
                    ->where('flag', 1)
                    ->first();

                if (! $existing) {
                    DB::table('question_option_question')->insert([
                        'question_id' => $question->id,
                        'option_id' => $optionId,
                        'flag' => 1,
                    ]);
                }
            }
        }

        if (! empty($ids)) {
            $question->verbHints()->whereNotIn('id', $ids)->delete();
        } else {
            $question->verbHints()->delete();
        }
    }

    private function syncTags(Question $question, array $payload): void
    {
        if (! $this->tableExists('question_tag')) {
            return;
        }

        $tagIds = [];
        foreach (Arr::get($payload, 'tags', []) as $entry) {
            $name = isset($entry['name']) ? trim((string) $entry['name']) : null;
            if ($name === null || $name === '') {
                continue;
            }
            $tag = Tag::firstOrCreate(
                ['name' => $name],
                ['category' => $entry['category'] ?? null]
            );
            if (array_key_exists('category', $entry) && $entry['category'] !== $tag->category) {
                $tag->category = $entry['category'];
                $tag->save();
            }
            $tagIds[] = $tag->id;
        }

        if (! empty($tagIds)) {
            $question->tags()->sync($tagIds);
        } else {
            $question->tags()->detach();
        }
    }

    private function syncHints(Question $question, array $payload): void
    {
        if (! $this->tableExists('question_hints')) {
            return;
        }

        $ids = [];
        foreach (Arr::get($payload, 'hints', []) as $entry) {
            $provider = $entry['provider'] ?? null;
            $locale = $entry['locale'] ?? null;
            if (! $provider || ! $locale) {
                continue;
            }

            $hint = QuestionHint::updateOrCreate(
                [
                    'question_id' => $question->id,
                    'provider' => $provider,
                    'locale' => $locale,
                ],
                ['hint' => $entry['hint'] ?? '']
            );
            $ids[] = $hint->id;
        }

        if (! empty($ids)) {
            $question->hints()->whereNotIn('id', $ids)->delete();
        } else {
            $question->hints()->delete();
        }
    }

    private function syncVariants(Question $question, array $payload): void
    {
        if (! $this->tableExists('question_variants')) {
            return;
        }

        $question->variants()->delete();
        foreach (Arr::get($payload, 'variants', []) as $entry) {
            $text = isset($entry['text']) ? trim((string) $entry['text']) : null;
            if ($text === null || $text === '') {
                continue;
            }
            $question->variants()->create(['text' => $text]);
        }
    }

    private function loadQuestion(Question $question): Question
    {
        $relations = [
            'category',
            'source',
        ];

        if ($this->tableExists('question_answers')) {
            $relations[] = 'answers.option';
        }

        if ($this->tableExists('verb_hints')) {
            $relations[] = 'verbHints.option';
        }

        if ($this->tableExists('question_variants')) {
            $relations[] = 'variants';
        }

        if ($this->tableExists('question_tag')) {
            $relations[] = 'tags';
        }

        if ($this->tableExists('question_hints')) {
            $relations[] = 'hints';
        }

        return Question::with($relations)->findOrFail($question->id);
    }

    private function buildPayload(Question $question): array
    {
        $optionsPayload = [];

        if ($this->tableExists('question_option_question')) {
            $pivotRows = DB::table('question_option_question')
                ->where('question_id', $question->id)
                ->orderBy('id')
                ->get();

            $optionIds = $pivotRows->pluck('option_id')->unique()->values();
            $options = QuestionOption::whereIn('id', $optionIds)->get()->keyBy('id');

            $optionsPayload = $pivotRows->map(function ($row) use ($options) {
                $option = $options[$row->option_id] ?? null;
                if (! $option) {
                    return null;
                }

                return [
                    'value' => $option->option,
                    'flag' => $row->flag,
                ];
            })->filter()->unique(fn ($entry) => $entry['value'] . '|' . ($entry['flag'] ?? ''))->values()->all();
        }

        return [
            'question_uuid' => $question->uuid,
            'question_id' => $question->id,
            'generated_at' => Carbon::now()->toIso8601String(),
            'attributes' => [
                'question' => $question->question,
                'difficulty' => $question->difficulty,
                'level' => $question->level,
                'flag' => $question->flag,
                'category_id' => $question->category_id,
                'source_id' => $question->source_id,
            ],
            'category' => $question->category ? [
                'id' => $question->category->id,
                'name' => $question->category->name,
            ] : null,
            'source' => $question->source ? [
                'id' => $question->source->id,
                'name' => $question->source->name,
            ] : null,
            'answers' => $question->relationLoaded('answers')
                ? $question->answers->map(function ($answer) {
                    return [
                        'marker' => $answer->marker,
                        'option' => $answer->option?->option,
                    ];
                })->values()->all()
                : [],
            'options' => $optionsPayload,
            'verb_hints' => $question->relationLoaded('verbHints')
                ? $question->verbHints->map(function ($hint) {
                    return [
                        'marker' => $hint->marker,
                        'value' => $hint->option?->option,
                    ];
                })->values()->all()
                : [],
            'tags' => $question->relationLoaded('tags')
                ? $question->tags->map(function ($tag) {
                    return [
                        'name' => $tag->name,
                        'category' => $tag->category,
                    ];
                })->values()->all()
                : [],
            'hints' => $this->tableExists('question_hints') && $question->relationLoaded('hints')
                ? $question->hints->map(function ($hint) {
                    return [
                        'provider' => $hint->provider,
                        'locale' => $hint->locale,
                        'hint' => $hint->hint,
                    ];
                })->values()->all()
                : [],
            'variants' => $question->relationLoaded('variants')
                ? $question->variants->map(function ($variant) {
                    return [
                        'text' => $variant->text,
                    ];
                })->values()->all()
                : [],
        ];
    }

    private function ensureQuestionUuid(Question $question): void
    {
        if (! empty($question->uuid)) {
            return;
        }

        $question->uuid = (string) Str::uuid();
        $question->save();
    }

    private function getFilePath(string $uuid): string
    {
        return base_path(self::DIRECTORY . DIRECTORY_SEPARATOR . $uuid . '.json');
    }

    private function ensureDirectoryExists(string $path): void
    {
        $directory = dirname($path);
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    private function markApplied(string $uuid, string $hash): void
    {
        if (! $this->tableExists('question_dump_states')) {
            return;
        }

        QuestionDumpState::updateOrCreate(
            ['question_uuid' => $uuid],
            ['file_hash' => $hash, 'applied_at' => Carbon::now()]
        );
    }

    private function tableExists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
