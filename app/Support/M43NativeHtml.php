<?php

namespace App\Support;

/** Pure escaping/markup, not a registry or an author-text transformation. */
final class M43NativeHtml
{
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    public static function paragraphs(array $paragraphs): string
    {
        return implode('', array_map(fn (string $text) => '<p lang="uk">'.self::escape($text).'</p>', $paragraphs));
    }

    public static function examples(array $examples): string
    {
        $html = '';
        foreach ($examples as $example) {
            $html .= '<div class="theory-example flex items-start gap-3 rounded-lg bg-white/60 border border-white/80 p-3">'
                .'<span aria-hidden="true" data-theory-ui class="flex-shrink-0 text-lg">💬</span><div class="min-w-0 flex-1">'
                .'<p lang="en" class="font-mono text-xs font-medium text-foreground">'.self::escape($example['en']).'</p>'
                .'<p lang="uk" class="theory-translation text-xs text-muted-foreground mt-0.5 italic">'.self::escape($example['uk']).'</p>';
            if (isset($example['note_uk'])) {
                $html .= '<p lang="uk" class="m43-example-note text-xs text-muted-foreground">'.self::escape($example['note_uk']).'</p>';
            }
            $html .= '</div></div>';
        }
        return $html;
    }

    public static function cell(string|array $cell): string
    {
        if (is_string($cell)) { return '<span>'.self::escape($cell).'</span>'; }
        if (isset($cell['text_uk'])) { return '<span lang="uk">'.self::escape($cell['text_uk']).'</span>'; }
        return self::examples([$cell]);
    }
}
