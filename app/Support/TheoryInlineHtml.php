<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\HtmlString;

/**
 * Inline formatting for the explicitly rich-text fields in theory blocks.
 *
 * Rebuild a parsed fragment from an allowlist rather than trusting stored HTML.
 * Links, URLs, event handlers, styles and arbitrary attributes are not supported.
 * Titles, labels and other plain-text fields must continue to use Blade escaping.
 */
final class TheoryInlineHtml
{
    private const TAGS = ['strong', 'b', 'em', 'i', 'u', 's', 'del', 'code', 'small', 'sub', 'sup', 'br', 'span'];

    private const DROP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template',
        'noscript', 'textarea', 'select', 'button', 'form', 'input', 'link', 'meta',
        'base', 'head', 'title', 'xmp', 'plaintext',
    ];

    private const SPAN_CLASSES = ['text-slate-500', 'text-muted-foreground', 'font-mono'];

    public static function render(?string $value): HtmlString
    {
        if ($value === null || $value === '') {
            return new HtmlString('');
        }

        $source = new DOMDocument('1.0', 'UTF-8');
        $previousErrors = libxml_use_internal_errors(true);

        try {
            $loaded = $source->loadHTML(
                '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$value.'</body></html>',
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrors);
        }

        $body = $source->getElementsByTagName('body')->item(0);

        if (! $loaded || ! $body) {
            return new HtmlString(htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        }

        $output = new DOMDocument('1.0', 'UTF-8');
        $container = $output->appendChild($output->createElement('div'));

        foreach ($body->childNodes as $child) {
            self::appendSafeNode($child, $container, $output);
        }

        $html = '';
        foreach ($container->childNodes as $child) {
            $html .= $output->saveHTML($child);
        }

        return new HtmlString($html);
    }

    private static function appendSafeNode(DOMNode $node, DOMNode $parent, DOMDocument $output): void
    {
        if (in_array($node->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE], true)) {
            $parent->appendChild($output->createTextNode($node->nodeValue ?? ''));

            return;
        }

        if (! $node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->tagName);
        if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
            return;
        }

        $target = $parent;
        if (in_array($tag, self::TAGS, true)) {
            $target = $parent->appendChild($output->createElement($tag));

            if ($tag === 'span') {
                $tokens = explode(' ', str_replace(["\t", "\n", "\r", "\f"], ' ', $node->getAttribute('class')));
                $classes = array_values(array_unique(array_intersect($tokens, self::SPAN_CLASSES)));
                if ($classes !== []) {
                    $target->setAttribute('class', implode(' ', $classes));
                }
            }
        }

        foreach ($node->childNodes as $child) {
            self::appendSafeNode($child, $target, $output);
        }
    }
}
