<?php

namespace App\Support;

use App\Models\Question;
use App\Models\SavedGrammarTest;
use App\Models\Test;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class ComposeModeEligibility
{
    public static function normalizedFilters(mixed $test): array
    {
        $filters = self::extractFilters($test);

        if (is_string($filters)) {
            $decoded = json_decode($filters, true);
            $filters = is_array($decoded) ? $decoded : [];
        }

        return is_array($filters) ? $filters : [];
    }

    public static function supportsQuestion(?Question $question, array $filters = []): bool
    {
        if (self::supportsFilters($filters)) {
            return true;
        }

        return $question !== null
            && (string) $question->type === Question::TYPE_COMPOSE_TOKENS;
    }

    public static function isAvailableForTest(mixed $test, iterable $questions = []): bool
    {
        $filters = self::normalizedFilters($test);

        if (self::supportsFilters($filters)) {
            return true;
        }

        foreach ($questions as $question) {
            if ($question instanceof Question && self::supportsQuestion($question, $filters)) {
                return true;
            }
        }

        $questionUuids = self::extractQuestionUuids($test);
        if ($questionUuids !== []) {
            return Question::query()
                ->whereIn('uuid', $questionUuids)
                ->where('type', Question::TYPE_COMPOSE_TOKENS)
                ->exists();
        }

        $questionIds = self::extractQuestionIds($test);
        if ($questionIds !== []) {
            return Question::query()
                ->whereIn('id', $questionIds)
                ->where('type', Question::TYPE_COMPOSE_TOKENS)
                ->exists();
        }

        return false;
    }

    public static function supportsFilters(array $filters): bool
    {
        return ($filters['mode'] ?? null) === 'compose_tokens'
            || (string) ($filters['question_type'] ?? '') === Question::TYPE_COMPOSE_TOKENS;
    }

    private static function extractFilters(mixed $test): mixed
    {
        if (is_object($test) && isset($test->filters)) {
            return $test->filters;
        }

        if ($test instanceof Arrayable) {
            return $test->toArray()['filters'] ?? [];
        }

        if (is_array($test)) {
            return $test['filters'] ?? [];
        }

        return [];
    }

    private static function extractQuestionUuids(mixed $test): array
    {
        if ($test instanceof SavedGrammarTest) {
            if ($test->relationLoaded('questionLinks')) {
                return $test->questionLinks
                    ->pluck('question_uuid')
                    ->filter()
                    ->values()
                    ->all();
            }

            return $test->questionLinks()
                ->pluck('question_uuid')
                ->filter()
                ->values()
                ->all();
        }

        if (is_object($test) && isset($test->questionUuids)) {
            $questionUuids = $test->questionUuids;

            if ($questionUuids instanceof Collection) {
                return $questionUuids->filter()->values()->all();
            }

            if (is_array($questionUuids)) {
                return array_values(array_filter($questionUuids));
            }
        }

        if (is_array($test) && is_array($test['question_uuids'] ?? null)) {
            return array_values(array_filter($test['question_uuids']));
        }

        return [];
    }

    private static function extractQuestionIds(mixed $test): array
    {
        if ($test instanceof Test) {
            return collect($test->questions ?? [])
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        if (is_object($test) && isset($test->questions) && is_array($test->questions)) {
            return collect($test->questions)
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        if (is_array($test) && is_array($test['questions'] ?? null)) {
            return collect($test['questions'])
                ->filter(fn ($id) => filled($id))
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        return [];
    }
}
