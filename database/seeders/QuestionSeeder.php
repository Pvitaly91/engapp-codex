<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

abstract class QuestionSeeder extends Seeder
{
    protected const UUID_LENGTH = 36;

    protected function generateQuestionUuid(int|string ...$segments): string
    {
        $base = Str::slug(class_basename(static::class));

        $normalizedSegments = [];
        foreach ($segments as $segment) {
            $segment = Str::slug((string) $segment);
            if ($segment === '') {
                continue;
            }

            $normalizedSegments[] = $segment;
        }

        $suffix = '';
        if ($normalizedSegments) {
            $suffix = '-' . implode('-', $normalizedSegments);
        }

        $maxLength = self::UUID_LENGTH - strlen($suffix);

        if ($maxLength <= 0) {
            return substr(ltrim($suffix, '-'), 0, self::UUID_LENGTH);
        }

        $base = substr($base, 0, $maxLength);

        if ($base === '') {
            return substr(ltrim($suffix, '-'), 0, self::UUID_LENGTH);
        }

        return $base . $suffix;
    }
}
