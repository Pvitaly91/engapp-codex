<?php

namespace App\Support;

/** Pure escaping/markup, not a registry or an author-text transformation. */
final class M43NativeHtml
{
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    public static function paragraphs(array $paragraphs, bool $muted = false): string
    {
        return implode('', array_map(fn (string $text) => '<p lang="uk"'.($muted ? ' class="text-muted-foreground"' : '').'>'.self::escape($text).'</p>', $paragraphs));
    }

    public static function examples(array $examples): string
    {
        $html = '';
        foreach ($examples as $example) {
            $html .= '<div class="theory-example flex items-start gap-3">'
                .'<span aria-hidden="true" data-theory-ui class="flex-shrink-0 text-lg">💬</span><div class="min-w-0 flex-1">'
                .'<p lang="en" class="font-medium text-foreground">'.self::escape($example['en']).'</p>'
                .'<p lang="uk" class="theory-translation">'.self::escape($example['uk']).'</p>';
            if (isset($example['note_uk'])) {
                $html .= '<p lang="uk" class="m43-example-note text-muted-foreground">'.self::escape($example['note_uk']).'</p>';
            }
            $html .= '</div></div>';
        }
        return $html;
    }

    /** Presentation for M43's exact, validated code-generated examples.
     * The immutable stored HTML remains unchanged; every inner byte is retained.
     * This is not a sanitizer and must not be used for arbitrary author HTML.
     */
    public static function decorateStoredExamples(string $html): string
    {
        return preg_replace_callback('/<div class="theory-example">(.*?)<\/div>/s',
            static fn (array $match): string => '<div class="theory-example flex items-start gap-3">'
                .'<span aria-hidden="true" data-theory-ui class="flex-shrink-0 text-lg">💬</span>'
                .'<div class="min-w-0 flex-1">'.$match[1].'</div></div>', $html) ?? $html;
    }

    /** Add reference heading utilities to M43's immutable code-generated prompt. */
    public static function decoratePracticePrompt(string $html, ?int $sourceIndex = null): string
    {
        $badge = $sourceIndex === null ? '' : '<span aria-hidden="true" data-theory-ui class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded bg-blue-500 text-white text-[10px]">'
            .self::escape((string) $sourceIndex).'</span>';
        $html = str_replace('<h4>', '<h4 class="text-sm font-semibold flex items-center gap-2">'.$badge, $html);
        return preg_replace('/<\/h4><p>/', '</h4><p class="text-xs text-muted-foreground mt-1">', $html, 1) ?? $html;
    }

    public static function cell(string|array $cell): string
    {
        if (is_string($cell)) { return '<span>'.self::escape($cell).'</span>'; }
        if (isset($cell['text_uk'])) { return '<span lang="uk">'.self::escape($cell['text_uk']).'</span>'; }
        return self::examples([$cell]);
    }
}
