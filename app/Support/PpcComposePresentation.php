<?php

namespace App\Support;

use App\Models\Question;

/** Read-only finite presentation; canonical questions and answer tokens stay intact. */
final class PpcComposePresentation
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

    private static array $packages = [];

    public static function forQuestion(Question $question, string $locale, string $rawSource): ?array
    {
        $locale = self::locale($locale);
        $entry = self::entry($question);
        $localized = $entry['locales'][$locale] ?? null;
        if (! $localized || $localized['expected_source'] !== $rawSource) {
            return null;
        }

        return ['display_source' => $localized['display_source'], 'instructions' => $localized['instructions']];
    }

    /** Locale-independent display revision; stale persisted sources do not opt in. */
    public static function revision(Question $question): ?string
    {
        $entry = self::entry($question);
        if (! $entry || ! $question->relationLoaded('hints')) {
            return null;
        }
        foreach ($entry['locales'] as $locale => $localized) {
            $sources = $question->hints->filter(static fn ($hint): bool => $hint->provider === 'compose_prompt'
                && self::locale((string) $hint->locale) === $locale);
            if ($sources->count() !== 1 || $sources->first()->hint !== $localized['expected_source']) {
                return null;
            }
        }

        return hash('sha256', json_encode(['ppc-compose-presentation-v1', $entry], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private static function locale(string $locale): string
    {
        $locale = strtolower(trim($locale));
        return $locale === 'ua' ? 'uk' : $locale;
    }

    private static function entry(Question $question): ?array
    {
        $seeder = (string) $question->seeder;
        $uuid = (string) $question->uuid;
        if (! in_array($seeder, self::SEEDERS, true) || $uuid === '') {
            return null;
        }
        $scope = str_contains($seeder, '\\Polyglot\\') ? 'builder' : 'mixed';
        $path = base_path('database/content-patches/ppc-compose-presentation/'.$scope.'.json');
        if (! array_key_exists($path, self::$packages)) {
            self::$packages[$path] = self::readPackage($path, $scope);
        }
        return self::$packages[$path][$seeder."\0".$uuid] ?? null;
    }

    private static function readPackage(string $path, string $scope): array
    {
        if (! is_file($path) || ! is_readable($path) || is_link($path) || is_link(dirname($path))) {
            return [];
        }
        try {
            $package = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }
        $count = $scope === 'builder' ? 336 : 288;
        if (($package['schema_version'] ?? null) !== 1 || ($package['projection'] ?? null) !== 'ppc-compose-presentation-v1'
            || ($package['scope'] ?? null) !== $scope || ($package['counts']['questions'] ?? null) !== $count
            || ($package['counts']['locale_rows'] ?? null) !== $count * 3 || ! is_array($package['questions'] ?? null)
            || count($package['questions']) !== $count) {
            return [];
        }
        $entries = [];
        foreach ($package['questions'] as $row) {
            if (! is_array($row) || array_keys($row) !== ['seeder_class', 'editorial_uuid', 'persistent_uuid', 'locales']
                || ! is_string($row['seeder_class']) || ! in_array($row['seeder_class'], self::SEEDERS, true)
                || (str_contains($row['seeder_class'], '\\Polyglot\\') ? 'builder' : 'mixed') !== $scope
                || ! is_string($row['editorial_uuid']) || $row['editorial_uuid'] === ''
                || ! is_string($row['persistent_uuid']) || $row['persistent_uuid'] === ''
                || ! is_array($row['locales']) || array_keys($row['locales']) !== ['uk', 'en', 'pl']) {
                return [];
            }
            foreach ($row['locales'] as $localized) {
                if (! is_array($localized) || array_keys($localized) !== ['expected_source', 'display_source', 'instructions']
                    || ! is_string($localized['expected_source']) || trim($localized['expected_source']) === ''
                    || ! is_string($localized['display_source']) || trim($localized['display_source']) === ''
                    || ! is_string($localized['instructions'])) {
                    return [];
                }
            }
            $key = $row['seeder_class']."\0".$row['persistent_uuid'];
            if (isset($entries[$key])) { return []; }
            $entries[$key] = $row;
        }
        return $entries;
    }
}
