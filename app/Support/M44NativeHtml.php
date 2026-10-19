<?php

namespace App\Support;

/** Pure post-M43.1 presentation reuse; this class grants no package/owner authority. */
final class M44NativeHtml
{
    public static function escape(string $value): string
    {
        return M43NativeHtml::escape($value);
    }

    public static function paragraphs(array $paragraphs, bool $muted = false): string
    {
        return M43NativeHtml::paragraphs($paragraphs, $muted);
    }

    public static function examples(array $examples): string
    {
        return str_replace('class="m43-example-note ', 'class="theory-example-note ', M43NativeHtml::examples($examples));
    }

    /** Only exact, code-generated author HTML supplied by the guarded M44 package. */
    public static function decorateStoredExamples(string $html): string
    {
        return str_replace('class="theory-example-note"', 'class="theory-example-note text-muted-foreground"',
            M43NativeHtml::decorateStoredExamples($html));
    }

    public static function decoratePracticePrompt(string $html, ?int $sourceIndex = null): string
    {
        return M43NativeHtml::decoratePracticePrompt($html, $sourceIndex);
    }

    public static function cell(string|array $cell): string
    {
        if (is_string($cell)) { return '<span>'.self::escape($cell).'</span>'; }
        if (isset($cell['formula'])) { return '<span>'.self::escape($cell['formula']).'</span>'; }
        if (isset($cell['text_uk'])) { return '<span lang="uk">'.self::escape($cell['text_uk']).'</span>'; }
        return self::examples([$cell]);
    }
}
