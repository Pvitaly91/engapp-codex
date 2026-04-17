<?php

namespace App\Support;

class TheoryVariantPayloadSanitizer
{
    /**
     * @var array<int, string>
     */
    private const ALLOWED_HTML_TAGS = [
        'p',
        'ul',
        'ol',
        'li',
        'strong',
        'em',
        'table',
        'thead',
        'tbody',
        'tr',
        'th',
        'td',
        'code',
        'pre',
        'br',
        'span',
    ];

    public static function sanitizePayload(array $payload): array
    {
        return [
            'title' => self::sanitizeText($payload['title'] ?? ''),
            'subtitle_html' => self::sanitizeHtml($payload['subtitle_html'] ?? ''),
            'subtitle_text' => self::sanitizeText($payload['subtitle_text'] ?? ''),
            'locale' => self::sanitizeLocale($payload['locale'] ?? null),
            'blocks' => collect($payload['blocks'] ?? [])
                ->filter(fn ($block) => is_array($block))
                ->values()
                ->map(fn (array $block, int $index) => self::sanitizeBlock($block, $index))
                ->all(),
        ];
    }

    public static function sanitizeHtml(?string $value): string
    {
        $html = trim((string) $value);

        if ($html === '') {
            return '';
        }

        $html = preg_replace(
            '#<(script|style|iframe|object|embed|form|input|button|meta|link|base)[^>]*>.*?</\1>#is',
            ' ',
            $html
        ) ?? $html;

        $html = preg_replace('/(?i)\s+on[a-z0-9_-]+\s*=\s*(["\']).*?\1/u', '', $html) ?? $html;
        $html = preg_replace('/(?i)\s+on[a-z0-9_-]+\s*=\s*[^\s>]+/u', '', $html) ?? $html;
        $html = preg_replace('/(?i)\s(href|src)\s*=\s*(["\']?)\s*(javascript:|vbscript:|data:text\/html)/u', ' $1=$2#', $html) ?? $html;

        $html = preg_replace_callback('/<\s*(\/?)\s*([a-z0-9]+)(?:\s+[^>]*)?>/iu', function (array $matches) {
            $isClosing = $matches[1] === '/';
            $tag = strtolower($matches[2]);

            if (! in_array($tag, self::ALLOWED_HTML_TAGS, true)) {
                return '';
            }

            return $isClosing ? "</{$tag}>" : "<{$tag}>";
        }, $html) ?? $html;

        $html = preg_replace('/<\s*br\s*\/?\s*>/iu', '<br>', $html) ?? $html;
        $html = preg_replace("/[ \t]+\n/u", "\n", $html) ?? $html;
        $html = preg_replace("/\n{3,}/u", "\n\n", $html) ?? $html;

        return trim($html);
    }

    public static function sanitizeText(?string $value): string
    {
        $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    public static function sanitizeLocale(?string $locale): string
    {
        $normalized = strtolower(trim((string) $locale));

        return $normalized === 'ua' ? 'uk' : $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    private static function sanitizeBlock(array $block, int $index): array
    {
        return [
            'column' => self::sanitizeText($block['column'] ?? 'left'),
            'type' => self::sanitizeText($block['type'] ?? 'box'),
            'heading' => self::sanitizeText($block['heading'] ?? ''),
            'body' => self::sanitizeBlockBody($block['body'] ?? ''),
            'sort_order' => isset($block['sort_order']) ? (int) $block['sort_order'] : (($index + 1) * 10),
        ];
    }

    private static function sanitizeBlockBody(mixed $body): string
    {
        if (! is_string($body)) {
            return '';
        }

        $trimmed = trim($body);

        if ($trimmed === '') {
            return '';
        }

        if (($trimmed[0] ?? null) === '{' || ($trimmed[0] ?? null) === '[') {
            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode(
                    self::sanitizeRecursive($decoded),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                ) ?: $trimmed;
            }
        }

        return self::sanitizeHtml($trimmed);
    }

    private static function sanitizeRecursive(mixed $value): mixed
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::sanitizeRecursive($item);
            }

            return $value;
        }

        if (is_string($value)) {
            return self::sanitizeHtml($value);
        }

        return $value;
    }
}
