<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\HtmlString;

/** Render-only adaptation of already trusted lesson HTML. This is not a sanitizer. */
final class TheoryPresentation
{
    public const NATIVE_TYPES = ['forms-grid', 'lesson-rule-cards', 'usage-panels', 'comparison-table', 'mistakes-grid', 'summary-list', 'practice-set', 'tense-forms-table'];

    public static function data(?string $body): array
    {
        $data = json_decode($body ?? '', true);

        return is_array($data) ? $data : [];
    }

    /** Keep unknown formats separate from the finite, code-owned view registry. */
    public static function nativeView(?string $type): ?string
    {
        return in_array($type, self::NATIVE_TYPES, true) ? 'engram.theory.blocks-v3.'.$type : null;
    }

    /** All original text/attributes/anchors remain; only presentation and new anchors are added. */
    public static function html(object $block): array
    {
        $body = (string) ($block->body ?? '');
        $rich = TheoryRichContent::render($body);
        $source = $rich?->toHtml() ?? $body;
        $fallback = ['html' => new HtmlString($source), 'toc' => [], 'structured' => $rich !== null];
        if ($source === '') {
            return $fallback;
        }
        foreach (['h2', 'h3', 'h4', 'section', 'details', 'summary', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'ul', 'ol', 'li', 'p', 'div'] as $tag) {
            if (preg_match_all('/<'.$tag.'\b[^>]*>/i', $source) !== preg_match_all('/<\/'.$tag.'\s*>/i', $source)) {
                return $fallback;
            }
        }
        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$source.'</body></html>', LIBXML_NONET);
            $errors = libxml_get_errors();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $root = $dom->getElementsByTagName('body')->item(0);
        if (!$loaded || !$root || collect($errors)->contains(fn ($error) => $error->code !== 801)) {
            return $fallback;
        }
        $xpath = new DOMXPath($dom);
        $headings = iterator_to_array($xpath->query('./h2|./h3|./h4|./section/h4[contains(@class,"theory-rich-heading")]|./section/h2[contains(@class,"theory-rich-heading")]', $root));
        $toc = [];
        $existingIds = [];
        foreach ($xpath->query('//*[@id]') as $element) {
            $existingIds[$element->getAttribute('id')] = true;
        }
        foreach ($headings as $index => $heading) {
            $title = trim($heading->textContent);
            if ($title === '') {
                continue;
            }
            $id = $heading->getAttribute('id');
            if ($id === '') {
                $base = 'lesson-block-'.preg_replace('/[^a-zA-Z0-9_-]/', '-', (string) ($block->id ?? $block->uuid ?? 'content')).'-section-'.($index + 1);
                $id = $base;
                for ($suffix = 2; isset($existingIds[$id]); $suffix++) {
                    $id = $base.'-'.$suffix;
                }
                $heading->setAttribute('id', $id);
                $existingIds[$id] = true;
            }
            $toc[] = ['id' => $id, 'title' => $title];
            self::addClass($heading, 'theory-section-title theory-html-heading');
            // The page's existing H1 is followed by these top-level sections.
            if ($heading->tagName !== 'h2') {
                $replacement = $dom->createElement('h2');
                foreach ($heading->attributes as $attribute) {
                    $replacement->setAttribute($attribute->nodeName, $attribute->nodeValue);
                }
                while ($heading->firstChild) {
                    $replacement->appendChild($heading->firstChild);
                }
                $heading->parentNode->replaceChild($replacement, $heading);
                $heading = $replacement;
            }
            if (!str_contains($heading->getAttribute('class'), 'theory-rich-heading')) {
                $first = $heading->firstChild;
                if ($first && $first->nodeType === XML_TEXT_NODE && preg_match('/^(\s*\d+[.)]\s+)(.*)$/us', $first->nodeValue, $parts)) {
                    $badge = $dom->createElement('span');
                    $badge->setAttribute('class', 'theory-section-number');
                    $badge->appendChild($dom->createTextNode($parts[1]));
                    $heading->insertBefore($badge, $first);
                    $first->nodeValue = $parts[2];
                }
            }
        }
        // Structural grouping only: an existing top-level heading starts a card.
        $current = null;
        foreach (iterator_to_array($root->childNodes) as $node) {
            if ($node instanceof DOMElement && $node->tagName === 'h2' && str_contains($node->getAttribute('class'), 'theory-html-heading')) {
                $section = $dom->createElement('section');
                $section->setAttribute('class', 'theory-html-section');
                $root->insertBefore($section, $node);
                $section->appendChild($node);
                $current = $section->appendChild($dom->createElement('div'));
                $current->setAttribute('class', 'theory-section-body');
            } elseif ($node instanceof DOMElement && $node->tagName === 'section') {
                $current = null;
            } elseif ($current) {
                $current->appendChild($node);
            }
        }
        foreach (iterator_to_array($root->getElementsByTagName('table')) as $table) {
            $parent = $table->parentNode;
            if (!($parent instanceof DOMElement && $parent->tagName === 'div' && $parent->getElementsByTagName('table')->length === 1 && trim($parent->textContent) === trim($table->textContent))) {
                $wrapper = $dom->createElement('div');
                $parent->insertBefore($wrapper, $table);
                $wrapper->appendChild($table);
                $parent = $wrapper;
            }
            self::addClass($parent, 'theory-table-scroll');
            if (!$parent->hasAttribute('tabindex')) {
                $parent->setAttribute('tabindex', '0');
            }
        }
        $html = '';
        foreach ($root->childNodes as $node) {
            $html .= $dom->saveHTML($node);
        }

        return ['html' => new HtmlString($html), 'toc' => $toc, 'structured' => $rich !== null || $toc !== []];
    }

    private static function addClass(DOMElement $element, string $class): void
    {
        $element->setAttribute('class', trim($element->getAttribute('class').' '.$class));
    }
}
