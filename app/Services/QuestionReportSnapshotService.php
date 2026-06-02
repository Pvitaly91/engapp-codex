<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class QuestionReportSnapshotService
{
    public const VERSION = 1;

    public function buildForQuestion(Question $question): array
    {
        return $this->build($question, 'report_time_db_state');
    }

    public function buildCurrentByReport(array $report): ?array
    {
        $question = $this->questionForReport($report);

        if (! $question instanceof Question) {
            return null;
        }

        return $this->build($question, 'current_db_state');
    }

    public function compare(?array $snapshot, ?array $current): array
    {
        $sections = [
            'question_text' => 'Question text',
            'answers' => 'Answers',
            'options' => 'Options',
            'verb_hints' => 'Verb hints',
            'hints' => 'Hints',
            'tags' => 'Tags',
            'seeder' => 'Seeder',
            'saved_tests' => 'Saved tests',
        ];

        $result = [];
        $hasChanges = false;

        foreach ($sections as $key => $label) {
            $snapshotValue = $this->comparableSection($snapshot, $key);
            $currentValue = $this->comparableSection($current, $key);
            $hasSnapshot = $snapshotValue !== null;
            $hasCurrent = $currentValue !== null;

            if (! $hasSnapshot && ! $hasCurrent) {
                $status = 'missing';
            } elseif ($snapshotValue === $currentValue) {
                $status = 'same';
            } else {
                $status = 'changed';
                $hasChanges = true;
            }

            $result[$key] = [
                'label' => $label,
                'status' => $status,
                'status_label' => match ($status) {
                    'changed' => 'Змінено',
                    'same' => 'Без змін',
                    default => 'Немає даних',
                },
            ];
        }

        $result['has_changes'] = $hasChanges;

        return $result;
    }

    private function build(Question $question, string $source): array
    {
        $this->loadQuestionRelations($question);
        $capturedAt = Carbon::now()->toIso8601String();

        return [
            'snapshot_version' => self::VERSION,
            'snapshot_source' => $source,
            'captured_at' => $capturedAt,
            'question' => [
                'id' => $question->id,
                'uuid' => $question->uuid,
                'text' => $question->question,
                'type' => $question->type,
                'level' => $question->level,
                'difficulty' => $question->difficulty,
                'flag' => $question->flag,
                'category' => [
                    'id' => $question->category?->id,
                    'name' => $question->category?->name,
                ],
                'source' => [
                    'id' => $question->source?->id ?? $question->source_id,
                    'name' => $question->source?->name,
                ],
                'seeder' => [
                    'class' => $question->seeder,
                    'file' => $this->seederFilePath($question->seeder),
                ],
                'created_at' => $this->dateString($question->created_at),
                'updated_at' => $this->dateString($question->updated_at),
            ],
            'answers' => $this->answers($question),
            'options' => $this->options($question),
            'verb_hints' => $this->verbHints($question),
            'hints' => $this->hints($question),
            'variants' => $this->variants($question),
            'tags' => $this->tags($question),
            'saved_tests' => $this->savedTests($question),
        ];
    }

    private function loadQuestionRelations(Question $question): void
    {
        $relations = [];

        foreach (['category', 'source', 'answers.option', 'options', 'verbHints.option', 'hints', 'variants', 'tags'] as $relation) {
            $method = Str::before($relation, '.');

            if (method_exists($question, $method) && $this->relationStorageExists($question, $method)) {
                $relations[] = $relation;
            }
        }

        if ($relations !== []) {
            $question->loadMissing($relations);
        }
    }

    private function relationStorageExists(Question $question, string $method): bool
    {
        try {
            $relation = $question->{$method}();

            if (method_exists($relation, 'getRelated') && ! Schema::hasTable($relation->getRelated()->getTable())) {
                return false;
            }

            if (method_exists($relation, 'getTable') && ! Schema::hasTable($relation->getTable())) {
                return false;
            }

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    private function questionForReport(array $report): ?Question
    {
        try {
            $uuid = trim((string) (data_get($report, 'question_snapshot.question.uuid') ?: data_get($report, 'question.uuid', '')));

            if ($uuid !== '') {
                $question = Question::query()->where('uuid', $uuid)->first();

                if ($question instanceof Question) {
                    return $question;
                }
            }

            $id = data_get($report, 'question_snapshot.question.id') ?: data_get($report, 'question.id');

            if (filled($id)) {
                return Question::query()->whereKey($id)->first();
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    private function answers(Question $question): array
    {
        if (! $question->relationLoaded('answers')) {
            return [];
        }

        return $question->answers
            ->sortBy(fn ($answer): string => $this->markerSortValue($answer->marker))
            ->map(fn ($answer): array => [
                'id' => $answer->id,
                'marker' => $answer->marker,
                'answer' => $answer->getAttribute('answer'),
                'option_id' => $answer->option_id,
                'option_value' => $answer->option?->option,
                'verb_hint' => $answer->getAttribute('verb_hint'),
                'created_at' => $this->dateString($answer->created_at),
                'updated_at' => $this->dateString($answer->updated_at),
            ])
            ->values()
            ->all();
    }

    private function options(Question $question): array
    {
        if (! $question->relationLoaded('options')) {
            return [];
        }

        return $question->options
            ->map(fn ($option): array => [
                'id' => $option->id,
                'option' => $option->option,
                'pivot' => [
                    'flag' => $option->pivot?->flag,
                ],
                'created_at' => $this->dateString($option->created_at),
                'updated_at' => $this->dateString($option->updated_at),
            ])
            ->sortBy(fn (array $option): string => mb_strtolower((string) ($option['option'] ?? '')))
            ->values()
            ->all();
    }

    private function verbHints(Question $question): array
    {
        if (! $question->relationLoaded('verbHints')) {
            return [];
        }

        return $question->verbHints
            ->sortBy(fn ($hint): string => $this->markerSortValue($hint->marker))
            ->map(fn ($hint): array => [
                'id' => $hint->id,
                'marker' => $hint->marker,
                'option_id' => $hint->option_id,
                'option_value' => $hint->option?->option,
                'hint' => $hint->option?->option,
                'created_at' => $this->dateString($hint->created_at),
                'updated_at' => $this->dateString($hint->updated_at),
            ])
            ->values()
            ->all();
    }

    private function hints(Question $question): array
    {
        if (! $question->relationLoaded('hints')) {
            return [];
        }

        return $question->hints
            ->map(fn ($hint): array => [
                'id' => $hint->id,
                'provider' => $hint->provider,
                'locale' => $hint->locale,
                'hint' => $hint->hint,
                'created_at' => $this->dateString($hint->created_at),
                'updated_at' => $this->dateString($hint->updated_at),
            ])
            ->values()
            ->all();
    }

    private function variants(Question $question): array
    {
        if (! $question->relationLoaded('variants')) {
            return [];
        }

        return $question->variants
            ->map(fn ($variant): array => [
                'id' => $variant->id,
                'question' => $variant->getAttribute('question') ?? $variant->getAttribute('text'),
                'text' => $variant->getAttribute('text') ?? $variant->getAttribute('question'),
                'metadata' => $variant->getAttribute('metadata') ?? [],
                'created_at' => $this->dateString($variant->created_at),
                'updated_at' => $this->dateString($variant->updated_at),
            ])
            ->values()
            ->all();
    }

    private function tags(Question $question): array
    {
        if (! $question->relationLoaded('tags')) {
            return [];
        }

        return $question->tags
            ->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'category' => $tag->category,
            ])
            ->sortBy(fn (array $tag): string => mb_strtolower((string) ($tag['name'] ?? '')))
            ->values()
            ->all();
    }

    private function savedTests(Question $question): array
    {
        if (! Schema::hasTable('saved_grammar_tests') || ! Schema::hasTable('saved_grammar_test_questions')) {
            return [];
        }

        try {
            return DB::table('saved_grammar_test_questions')
                ->join('saved_grammar_tests', 'saved_grammar_tests.id', '=', 'saved_grammar_test_questions.saved_grammar_test_id')
                ->where('saved_grammar_test_questions.question_uuid', $question->uuid)
                ->orderBy('saved_grammar_tests.slug')
                ->orderBy('saved_grammar_test_questions.position')
                ->get([
                    'saved_grammar_tests.id',
                    'saved_grammar_tests.uuid',
                    'saved_grammar_tests.slug',
                    'saved_grammar_tests.name',
                    'saved_grammar_test_questions.position',
                ])
                ->map(fn ($test): array => [
                    'id' => $test->id,
                    'uuid' => $test->uuid,
                    'slug' => $test->slug,
                    'name' => $test->name,
                    'position' => $test->position,
                ])
                ->values()
                ->all();
        } catch (Throwable) {
            return [];
        }
    }

    private function comparableSection(?array $snapshot, string $section): mixed
    {
        if ($snapshot === null) {
            return null;
        }

        return match ($section) {
            'question_text' => $this->nullableString(data_get($snapshot, 'question.text')),
            'answers' => $this->normalizedList($snapshot['answers'] ?? [], ['marker', 'answer', 'option_id', 'option_value', 'verb_hint']),
            'options' => $this->normalizedList($snapshot['options'] ?? [], ['id', 'option', 'pivot.flag']),
            'verb_hints' => $this->normalizedList($snapshot['verb_hints'] ?? [], ['marker', 'option_id', 'option_value', 'hint']),
            'hints' => $this->normalizedList($snapshot['hints'] ?? [], ['provider', 'locale', 'hint']),
            'tags' => $this->normalizedList($snapshot['tags'] ?? [], ['name', 'category']),
            'seeder' => [
                'class' => $this->nullableString(data_get($snapshot, 'question.seeder.class')),
                'file' => $this->nullableString(data_get($snapshot, 'question.seeder.file')),
            ],
            'saved_tests' => $this->normalizedList($snapshot['saved_tests'] ?? [], ['slug', 'name', 'position']),
            default => null,
        };
    }

    private function normalizedList(mixed $values, array $keys): ?array
    {
        if (! is_array($values)) {
            return null;
        }

        $normalized = collect($values)
            ->filter(fn ($value): bool => is_array($value))
            ->map(function (array $value) use ($keys): array {
                $row = [];

                foreach ($keys as $key) {
                    data_set($row, $key, $this->nullableString(data_get($value, $key)));
                }

                return $row;
            })
            ->sortBy(fn (array $row): string => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '')
            ->values()
            ->all();

        return $normalized;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }

    private function dateString(mixed $value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->toIso8601String();
        }

        return filled($value) ? (string) $value : null;
    }

    private function markerSortValue(?string $marker): string
    {
        $normalized = strtolower(trim((string) $marker));

        if (preg_match('/^([a-z_]+)(\d+)$/', $normalized, $matches) === 1) {
            return sprintf('%s%08d', $matches[1], (int) $matches[2]);
        }

        return $normalized;
    }

    private function seederFilePath(?string $seederClass): ?string
    {
        $normalized = str_replace('/', '\\', trim((string) $seederClass));

        if ($normalized === '') {
            return null;
        }

        $prefix = 'Database\\Seeders\\';
        if (! Str::startsWith($normalized, $prefix)) {
            return null;
        }

        $relativeClass = Str::after($normalized, $prefix);
        $relativePath = 'database/seeders/'.str_replace('\\', '/', $relativeClass).'.php';

        return file_exists(base_path($relativePath)) ? $relativePath : null;
    }
}
