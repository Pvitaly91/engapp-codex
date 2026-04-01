<?php

namespace App\Support;

use Illuminate\Support\Arr;
use JsonException;

class PromptGeneratorFilterNormalizer
{
    public static function normalize(mixed $payload): mixed
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (! is_string($payload)) {
            return $payload;
        }

        $pairs = preg_split('/\s*;\s*/u', trim($payload), -1, PREG_SPLIT_NO_EMPTY);

        if (! is_array($pairs) || $pairs === []) {
            return $payload;
        }

        $normalized = [];
        $parsed = false;

        foreach ($pairs as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);

            $key = trim((string) $key);

            if ($key === '' || $value === null) {
                continue;
            }

            Arr::set($normalized, $key, self::coerceValue(trim((string) $value)));
            $parsed = true;
        }

        return $parsed ? $normalized : $payload;
    }

    private static function coerceValue(string $value): mixed
    {
        if ($value === '') {
            return '';
        }

        if (
            (str_starts_with($value, '[') && str_ends_with($value, ']'))
            || (str_starts_with($value, '{') && str_ends_with($value, '}'))
        ) {
            try {
                return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                // Fall through to scalar coercion.
            }
        }

        $lower = strtolower($value);

        if ($lower === 'true') {
            return true;
        }

        if ($lower === 'false') {
            return false;
        }

        if ($lower === 'null') {
            return null;
        }

        if (preg_match('/^-?\d+$/', $value) === 1) {
            return (int) $value;
        }

        if (preg_match('/^-?\d+\.\d+$/', $value) === 1) {
            return (float) $value;
        }

        return $value;
    }
}
