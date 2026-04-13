<?php

namespace App\Support;

class AiOutputSanitizer
{
    public static function sanitize(?string $value): string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return '';
        }

        $text = preg_replace('#<(script|style|iframe|object|embed|form|input|button|meta|link|base)[^>]*>.*?</\1>#is', ' ', $text) ?? $text;
        $text = preg_replace('#<\s*br\s*/?\s*>#iu', "\n", $text) ?? $text;
        $text = preg_replace('#<\s*/?\s*(p|div|section|article|ul|ol|li|blockquote|h[1-6])\b[^>]*>#iu', "\n", $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/(?i)\b(?:javascript|vbscript):\S+/', '', $text) ?? $text;
        $text = preg_replace('/(?i)\bdata:text\/html\S*/', '', $text) ?? $text;
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/[ \t]+\n/u", "\n", $text) ?? $text;
        $text = preg_replace("/\n[ \t]+/u", "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;
        $text = preg_replace("/[ \t]{2,}/u", ' ', $text) ?? $text;

        return trim($text);
    }
}
