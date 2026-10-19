<?php

namespace App\Support;

use App\Models\Question;

/** Safe public interaction scope; no technical or connection metadata is exposed. */
final class PastPerfectContinuousTestIdentity
{
    private const SEEDERS = [
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousFormsAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousNegativesAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousQuestionsAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Tenses\\PastPerfectContinuous\\PastPerfectContinuousTimeExpressionsAllLevelsV3Seeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousFormsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousNegativesAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousQuestionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousTimeExpressionsAllLevelsLessonSeeder',
        'Database\\Seeders\\V3\\Polyglot\\PolyglotPastPerfectContinuousBasicsB2LessonSeeder',
    ];

    public static function forQuestion(Question $question): bool
    {
        if (in_array((string) $question->seeder, self::SEEDERS, true)) {
            return true;
        }

        $category = explode('/', (string) ($question->category?->name ?? ''));

        return strtolower(trim((string) end($category))) === 'past perfect continuous';
    }
}
