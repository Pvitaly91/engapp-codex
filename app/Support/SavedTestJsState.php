<?php

namespace App\Support;

use Illuminate\Support\Arr;

class SavedTestJsState
{
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
