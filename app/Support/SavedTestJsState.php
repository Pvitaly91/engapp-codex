<?php

namespace App\Support;

use Illuminate\Support\Arr;

class SavedTestJsState
{
    private const CURRENT_QUESTION_FIELDS = [
        'id',
        'uuid',
        'type',
        'question',
        'answer',
        'answers',
        'answer_map',
        'accepted_answers',
        'accepted_answers_by_marker',
        'answer_synonyms_by_marker',
        'answer_synonym_tokens_by_marker',
        'markers',
        'markers_count',
        'options_by_marker',
        'verb_hint',
        'verb_hints',
        'options',
        'tense',
        'level',
        'theory_block',
        'theory_blocks',
        'marker_tags',
        'tech_info',
    ];

    public static function isStarted(mixed $state): bool
    {
        if (! is_array($state)) {
            return false;
        }

        $explicit = Arr::get($state, '__meta.started');

        if (is_bool($explicit)) {
            return $explicit;
        }

        if (self::hasConnections($state['connections'] ?? null) || self::hasConnections($state['placements'] ?? null)) {
            return true;
        }

        if (($state['completed'] ?? false) === true || ($state['evaluated'] ?? false) === true) {
            return true;
        }

        if (self::positiveNumber($state['answered'] ?? null)
            || self::positiveNumber($state['current'] ?? null)
            || self::positiveNumber($state['activeCardIdx'] ?? null)) {
            return true;
        }

        foreach (Arr::get($state, 'items', []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            if (self::hasAnswerValues($item['chosen'] ?? null)
                || self::hasAnswerValues($item['inputs'] ?? null)
                || self::hasAnswerValues($item['manualInputsBySlot'] ?? null)) {
                return true;
            }

            if (($item['done'] ?? false) === true && ($item['status'] ?? null) !== 'auto') {
                return true;
            }

            if (array_key_exists('isCorrect', $item) && $item['isCorrect'] !== null) {
                return true;
            }

            if (array_key_exists('wasCorrect', $item) && $item['wasCorrect'] !== null) {
                return true;
            }

            if (($item['wrongAttempt'] ?? false) === true || self::positiveNumber($item['attempts'] ?? null)) {
                return true;
            }

            foreach (Arr::wrap($item['manualWordIndexBySlot'] ?? []) as $wordIndex) {
                if (self::positiveNumber($wordIndex)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function questionData(mixed $state): ?array
    {
        $questionData = Arr::get(is_array($state) ? $state : [], '__meta.question_data');

        return is_array($questionData) ? $questionData : null;
    }

    /**
     * Refresh authored question content without changing the user's progress.
     */
    public static function mergeCurrentQuestionData(?array $state, array $questions): ?array
    {
        if ($state === null) {
            return null;
        }

        $byUuid = [];
        $byId = [];

        foreach ($questions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $uuid = trim((string) ($question['uuid'] ?? ''));
            $id = trim((string) ($question['id'] ?? ''));

            if ($uuid !== '') {
                $byUuid[$uuid] = $question;
            }
            if ($id !== '') {
                $byId[$id] = $question;
            }
        }

        if (is_array($state['items'] ?? null)) {
            foreach ($state['items'] as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $uuid = trim((string) ($item['uuid'] ?? ''));
                $id = trim((string) ($item['id'] ?? ''));
                $current = $uuid !== ''
                    ? ($byUuid[$uuid] ?? null)
                    : ($id !== '' ? ($byId[$id] ?? null) : null);

                if (! is_array($current)) {
                    continue;
                }

                $state['items'][$index] = array_replace(
                    $item,
                    Arr::only($current, self::CURRENT_QUESTION_FIELDS)
                );
            }
        }

        $state['__meta'] = is_array($state['__meta'] ?? null) ? $state['__meta'] : [];
        $state['__meta']['question_data'] = $questions;

        return $state;
    }

    protected static function hasConnections(mixed $value): bool
    {
        return is_array($value) && $value !== [];
    }

    protected static function hasAnswerValues(mixed $value): bool
    {
        if (! is_array($value)) {
            return self::isFilledScalar($value);
        }

        foreach ($value as $item) {
            if (self::hasAnswerValues($item)) {
                return true;
            }
        }

        return false;
    }

    protected static function isFilledScalar(mixed $value): bool
    {
        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_int($value) || is_float($value)) {
            return true;
        }

        return $value !== null && $value !== false;
    }

    protected static function positiveNumber(mixed $value): bool
    {
        return is_numeric($value) && (float) $value > 0;
    }
}
