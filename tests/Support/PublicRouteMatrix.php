<?php

namespace Tests\Support;

final class PublicRouteMatrix
{
    public const DEFAULT_LOCALE = 'uk';

    public const SUPPORTED_LOCALES = ['uk', 'en', 'pl'];

    public const PAGE_CATEGORY_SLUG = 'basic-grammar';

    public const PAGE_SLUG = 'present-simple-smoke-page';

    public const PAGE_TITLE = 'Present Simple Guide';

    public const LEGACY_TEST_SLUG = 'present-simple-card-test';

    public const LEGACY_TEST_NAME = 'Present Simple Card Test';

    public const SAVED_TEST_UUID = '22222222-2222-4222-8222-222222222222';

    public const QUESTION_UUID = '11111111-1111-4111-8111-111111111111';

    public const SEARCH_TERM = 'present';

    public const WORD_QUERY = 'go';

    public const CATALOG_TAG = 'Present Simple';

    public static function localizedPath(string $locale, string $path = ''): string
    {
        $prefix = $locale === self::DEFAULT_LOCALE ? '' : '/' . $locale;
        $trimmedPath = trim($path, '/');

        if ($trimmedPath === '') {
            return $prefix === '' ? '/' : $prefix;
        }

        return $prefix . '/' . $trimmedPath;
    }

    public static function homePaths(): array
    {
        return [
            'uk' => self::localizedPath('uk'),
            'en' => self::localizedPath('en'),
            'pl' => self::localizedPath('pl'),
        ];
    }

    public static function catalogPaths(): array
    {
        return [
            'uk' => self::localizedPath('uk', 'catalog/tests-cards'),
            'en' => self::localizedPath('en', 'catalog/tests-cards'),
            'pl' => self::localizedPath('pl', 'catalog/tests-cards'),
        ];
    }

    public static function wordsDifficultyPages(): array
    {
        return [
            'easy' => [
                'page' => self::localizedPath('uk', 'words/test'),
                'state' => self::localizedPath('uk', 'words/test/state'),
                'check' => self::localizedPath('uk', 'words/test/check'),
                'reset' => self::localizedPath('uk', 'words/test/reset'),
            ],
            'medium' => [
                'page' => self::localizedPath('uk', 'words/test/medium'),
                'state' => self::localizedPath('uk', 'words/test/medium/state'),
                'check' => self::localizedPath('uk', 'words/test/medium/check'),
                'reset' => self::localizedPath('uk', 'words/test/medium/reset'),
            ],
            'hard' => [
                'page' => self::localizedPath('uk', 'words/test/hard'),
                'state' => self::localizedPath('uk', 'words/test/hard/state'),
                'check' => self::localizedPath('uk', 'words/test/hard/check'),
                'reset' => self::localizedPath('uk', 'words/test/hard/reset'),
            ],
        ];
    }

    public static function publicTestPaths(): array
    {
        return [
            'card' => self::localizedPath('uk', 'test/' . self::LEGACY_TEST_SLUG),
            'step' => self::localizedPath('uk', 'test/' . self::LEGACY_TEST_SLUG . '/step'),
        ];
    }

    public static function theorySearchPath(string $locale = 'uk'): string
    {
        return self::localizedPath($locale, sprintf(
            'theory/%s/%s',
            self::PAGE_CATEGORY_SLUG,
            self::PAGE_SLUG
        ));
    }
}
