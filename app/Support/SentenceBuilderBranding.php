<?php

namespace App\Support;

use Illuminate\Support\Str;

class SentenceBuilderBranding
{
    public const PUBLIC_COURSE_BASE_SLUG = 'sentence-builder-english';
    public const LEGACY_COURSE_BASE_SLUG = 'polyglot-english';

    public static function legacyCourseSlug(string $slug): string
    {
        $slug = trim($slug);

        if ($slug === self::PUBLIC_COURSE_BASE_SLUG) {
            return self::LEGACY_COURSE_BASE_SLUG;
        }

        if (preg_match('/^sentence-builder-english-(a1|a2|b1|b2|c1|c2)$/i', $slug, $matches) === 1) {
            return 'polyglot-english-'.strtolower($matches[1]);
        }

        return $slug;
    }

    public static function canonicalCourseSlug(string $slug): string
    {
        $slug = trim($slug);

        if ($slug === self::LEGACY_COURSE_BASE_SLUG) {
            return self::PUBLIC_COURSE_BASE_SLUG;
        }

        if (preg_match('/^polyglot-english-(a1|a2|b1|b2|c1|c2)$/i', $slug, $matches) === 1) {
            return 'sentence-builder-english-'.strtolower($matches[1]);
        }

        return $slug;
    }

    public static function legacyLessonSlug(string $slug): string
    {
        $slug = trim($slug);

        if (Str::startsWith($slug, 'sentence-builder-')) {
            return 'polyglot-'.Str::after($slug, 'sentence-builder-');
        }

        return $slug;
    }

    public static function canonicalLessonSlug(string $slug): string
    {
        $slug = trim($slug);

        if (Str::startsWith($slug, 'polyglot-')) {
            return 'sentence-builder-'.Str::after($slug, 'polyglot-');
        }

        return $slug;
    }

    public static function courseTitle(string $slug): string
    {
        $legacySlug = self::legacyCourseSlug($slug);

        if (preg_match('/^polyglot-english-(a1|a2|b1|b2|c1|c2)$/i', $legacySlug, $matches) === 1) {
            return 'English Sentence Builder '.strtoupper($matches[1]);
        }

        if ($legacySlug === self::LEGACY_COURSE_BASE_SLUG || $legacySlug === self::PUBLIC_COURSE_BASE_SLUG) {
            return 'English Sentence Builder';
        }

        return self::publicText(Str::of($slug)->replace('-', ' ')->headline()->toString());
    }

    public static function publicText(?string $value): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return '';
        }

        foreach (['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level) {
            $text = str_replace("Polyglot English {$level}", "English Sentence Builder {$level}", $text);
            $text = str_replace("Poliglot English {$level}", "English Sentence Builder {$level}", $text);
        }

        $replacements = [
            'Polyglot 16' => 'Sentence Builder',
            'Поліглот 16' => 'Sentence Builder',
            'Polyglot English' => 'English Sentence Builder',
            'Poliglot English' => 'English Sentence Builder',
            'Polyglot-style' => 'sentence-building',
            'polyglot-style' => 'sentence-building',
            'Poliglot-style' => 'sentence-building',
            'poliglot-style' => 'sentence-building',
            'Polyglot course' => 'Sentence Builder course',
            'polyglot course' => 'Sentence Builder course',
            'Poliglot course' => 'Sentence Builder course',
            'Polyglot exercises' => 'Sentence Builder exercises',
            'polyglot exercises' => 'Sentence Builder exercises',
            'Poliglot exercises' => 'Sentence Builder exercises',
            'Курс Поліглот' => 'Курс Sentence Builder',
            'курс Поліглот' => 'курс Sentence Builder',
            'Вправи Поліглот' => 'Вправи-конструктор речень',
            'вправи Поліглот' => 'вправи-конструктор речень',
            'у стилі Поліглот' => 'у форматі конструктора речень',
            'у стилі поліглот' => 'у форматі конструктора речень',
            'Polyglot:' => 'Sentence Builder:',
            'Poliglot:' => 'Sentence Builder:',
            'Поліглот:' => 'Sentence Builder:',
            'Полиглот:' => 'Sentence Builder:',
            'Polyglot' => 'Sentence Builder',
            'polyglot' => 'Sentence Builder',
            'Poliglot' => 'Sentence Builder',
            'poliglot' => 'Sentence Builder',
            'Поліглот' => 'Sentence Builder',
            'поліглот' => 'Sentence Builder',
            'Полиглот' => 'Sentence Builder',
            'полиглот' => 'Sentence Builder',
            'polihlot-style' => 'sentence-building',
            'polihlot' => 'Sentence Builder',
        ];

        return strtr($text, $replacements);
    }
}
