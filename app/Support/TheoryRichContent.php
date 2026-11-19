<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\HtmlString;

/** Presentation adapter for trusted, structured lesson HTML; not a sanitizer. */
final class TheoryRichContent
{
    private const PRESENTATION_STYLES = [
        'h4' => ['margin:1.5rem 0 0.75rem;font-size:1.125rem;line-height:1.5;font-weight:700;'],
        'li' => ['margin:0.65rem 0;padding-left:0.25rem;'],
        'ol' => ['list-style-type:decimal;padding-left:1.75rem;margin:0.75rem 0;'],
        'summary' => ['display:list-item;cursor:pointer;font-weight:700;margin:1rem 0 0.75rem;'],
        'th' => [
            'padding:0.75rem;border:1px solid var(--line);text-align:left;vertical-align:top;font-weight:700;',
            'padding:0.75rem;border:1px solid currentColor;text-align:left;',
        ],
        'td' => [
            'padding:0.75rem;border:1px solid var(--line);vertical-align:top;',
            'padding:0.75rem;border:1px solid currentColor;vertical-align:top;',
        ],
    ];

    /** Null means the caller must retain its existing rendering verbatim. */
    public static function render(?string $html): ?HtmlString
    {
        if (! $html || ! str_contains($html, 'self-check-')) {
            return null;
        }

        // Do not restyle a fragment the HTML parser would have to repair.
        foreach (['h4', 'section', 'details', 'summary', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'ul', 'ol', 'li', 'p', 'em', 'strong', 'blockquote', 'div'] as $tag) {
            if (preg_match_all('/<'.$tag.'\b[^>]*>/i', $html) !== preg_match_all('/<\/'.$tag.'\s*>/i', $html)) {
                return null;
            }
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
            $errors = libxml_get_errors();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        // libxml's HTML4 parser reports valid HTML5 section/details/summary as 801.
        foreach ($errors as $error) {
            if ($error->code !== 801) {
                return null;
            }
        }
        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $loaded || ! $body) {
            return null;
        }

        $xpath = new DOMXPath($dom);
        $headings = $xpath->query('./h4', $body);
        $numbered = 0;
        foreach ($headings as $heading) {
            $numbered += preg_match('/^\s*\d+[.)]\s+/u', $heading->textContent) === 1 ? 1 : 0;
        }
        $practice = $xpath->query('./section[starts-with(@id,"self-check-")][h4][ol][details[summary][ol]]', $body);
        if ($numbered < 2 || $practice->length !== 1) {
            return null;
        }

        $practiceNode = $practice->item(0);
        foreach ($xpath->query('.//*', $body) as $element) {
            if (in_array($element->getAttribute('style'), self::PRESENTATION_STYLES[$element->tagName] ?? [], true)) {
                $element->removeAttribute('style');
            }
            if ($element->tagName === 'h4') {
                self::addClass($element, 'theory-rich-heading');
                self::wrapNumber($element, $dom);
            }
            if ($element->tagName === 'em' && self::isEnglishSentence($element)) {
                self::addClass($element, 'theory-rich-example');
                if (! $element->hasAttribute('lang')) {
                    $element->setAttribute('lang', 'en');
                }
            }
        }

        // Reuse existing scroll wrappers; otherwise add one without touching the table.
        foreach (iterator_to_array($body->getElementsByTagName('table')) as $table) {
            $parent = $table->parentNode;
            if ($parent instanceof DOMElement && $parent->tagName === 'div' && self::onlyElementChild($parent, $table)) {
                $wrapper = $parent;
            } else {
                $wrapper = $dom->createElement('div');
                $parent->insertBefore($wrapper, $table);
                $wrapper->appendChild($table);
            }
            self::addClass($wrapper, 'theory-rich-table');
            self::addClass($table, 'theory-rich-table-element');
            if (! $wrapper->hasAttribute('tabindex')) {
                $wrapper->setAttribute('tabindex', '0');
            }
        }

        $output = $dom->createElement('div');
        $current = null;
        $afterPractice = false;
        foreach (iterator_to_array($body->childNodes) as $node) {
            if ($node === $practiceNode) {
                self::addClass($node, 'theory-rich-section theory-rich-practice');
                $output->appendChild($node);
                $current = null;
                $afterPractice = true;
            } elseif ($node instanceof DOMElement && $node->tagName === 'h4') {
                $section = $dom->createElement('section');
                self::addClass($section, 'theory-rich-section');
                if (preg_match('/помилк/iu', $node->textContent)) {
                    self::addClass($section, 'theory-rich-section--warning');
                }
                if ($afterPractice && ! preg_match('/^\s*\d+[.)]\s+/u', $node->textContent)) {
                    self::addClass($section, 'theory-rich-next');
                }
                $output->appendChild($section);
                $section->appendChild($node);
                $current = $section->appendChild($dom->createElement('div'));
                self::addClass($current, 'theory-rich-section-body');
            } else {
                ($current ?? $output)->appendChild($node);
            }
        }

        $result = '';
        foreach ($output->childNodes as $node) {
            $result .= $dom->saveHTML($node);
        }

        return new HtmlString($result);
    }

    /** Keep the established inline sanitizer, splitting only plain bilingual examples. */
    public static function example(?string $value): HtmlString
    {
        $value ??= '';
        if (strip_tags($value) !== $value || str_contains($value, '&') || ! str_contains($value, ' — ')) {
            return TheoryInlineHtml::render($value);
        }
        [$english, $translation] = explode(' — ', $value, 2);
        if (trim($english) === '' || trim($translation) === '' || preg_match('/\p{Cyrillic}/u', $english)) {
            return TheoryInlineHtml::render($value);
        }

        return new HtmlString('<span class="theory-rich-example-en" lang="en">'.e($english).'</span>'
            .'<span class="theory-rich-example-translation">'.e(' — '.$translation).'</span>');
    }

    private static function wrapNumber(DOMElement $heading, DOMDocument $dom): void
    {
        $first = $heading->firstChild;
        if (! $first || $first->nodeType !== XML_TEXT_NODE || ! preg_match('/^(\s*\d+[.)]\s+)(.*)$/us', $first->nodeValue, $parts)) {
            return;
        }
        $badge = $dom->createElement('span');
        $badge->setAttribute('class', 'theory-rich-number');
        $badge->appendChild($dom->createTextNode($parts[1]));
        $heading->insertBefore($badge, $first);
        $first->nodeValue = $parts[2];
    }

    private static function isEnglishSentence(DOMElement $element): bool
    {
        $text = trim($element->textContent);

        return (! $element->hasAttribute('lang') || $element->getAttribute('lang') === 'en')
            && ! preg_match('/[^\p{Latin}\p{N}\p{P}\p{Z}\s_+=$<>]/u', $text)
            && preg_match('/[.!?][”"\x{2019}]?$/u', $text)
            && preg_match_all('/\p{Latin}+/u', $text) >= 3;
    }

    private static function onlyElementChild(DOMElement $parent, DOMNode $expected): bool
    {
        foreach ($parent->childNodes as $child) {
            if ($child !== $expected && ($child instanceof DOMElement || trim($child->textContent) !== '')) {
                return false;
            }
        }

        return true;
    }

    private static function addClass(DOMElement $element, string $class): void
    {
        $element->setAttribute('class', trim($element->getAttribute('class').' '.$class));
    }
}
