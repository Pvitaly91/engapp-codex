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

        foreach (Arr::get($state, 'items', []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            if (self::hasAnswerValues($item['chosen'] ?? null) || self::hasAnswerValues($item['inputs'] ?? null)) {
                return true;
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
}
