<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\HtmlString;

/**
 * Render-only bridge for trusted legacy HTML. Not a sanitizer or a frozen source
 * generator. Unknown or malformed fragments remain complete and unchanged.
 */
final class TheoryHtmlAdapter
{
    public static function fragment(string $html): HtmlString
    {
        $parsed = self::parse($html);
        if ($parsed === null) { return new HtmlString($html); }
        [$dom, $root, $xpath] = $parsed;
        $changed = false;
        // Work from the inside out. A full bilingual pair uses the same component
        // as a structured source; inline emphasis is deliberately not guessed.
        foreach (array_reverse(iterator_to_array($xpath->query('.//div[contains(concat(" ",normalize-space(@class)," ")," theory-example ")]', $root))) as $example) {
            $english = $xpath->query('.//p[@lang="en"]', $example)->item(0);
            $translation = $xpath->query('.//p[@lang="uk"]', $example)->item(0);
            if (!$english || !$translation || str_contains($example->getAttribute('class'), 'theory-example--')) { continue; }
            if (!self::supportedAttributes($example, ['id', 'class'], true)) { return new HtmlString($html); }
            $known = trim($english->textContent.$translation->textContent);
            $notes = [];
            $paragraphs = [$english, $translation];
            foreach ($xpath->query('.//p[@lang="uk"]', $example) as $candidate) {
                if ($candidate !== $translation) { $notes[] = new HtmlString(self::inner($candidate)); $known .= trim($candidate->textContent); $paragraphs[] = $candidate; }
            }
            // Paragraph contents survive intact; the surrounding legacy wrappers do
            // not. Never discard attributes, anchors or unknown structural nodes.
            foreach ($xpath->query('.//*', $example) as $child) {
                if (in_array($child, $paragraphs, true)) {
                    if (!self::supportedAttributes($child, ['class', 'lang'])) { return new HtmlString($html); }
                } elseif (!self::insideAny($child, $paragraphs)
                    && (!in_array($child->tagName, ['div', 'span'], true) || !self::supportedAttributes($child, ['class']))) {
                    return new HtmlString($html);
                }
            }
            foreach ($xpath->query('.//comment()', $example) as $comment) {
                if (!self::insideAny($comment, $paragraphs)) { return new HtmlString($html); }
            }
            // Do not throw away an extra author field or a source anchor.
            if ($xpath->query('.//*[@id]', $example)->length > 0 || count($notes) > 1) { continue; }
            $remainder = preg_replace('/\s+/u', '', $example->textContent) ?? '';
            $expected = preg_replace('/\s+/u', '', $known) ?? '';
            if (!in_array($remainder, [$expected, '💬'.$expected], true)) { continue; }
            $node = ['kind' => 'example', 'en' => new HtmlString(self::inner($english)), 'uk' => new HtmlString(self::inner($translation)),
                'note_uk' => $notes[0] ?? null, 'variant' => 'box', 'attrs' => self::technicalAttributes($example)];
            if ($example->hasAttribute('id')) { $node['id'] = $example->getAttribute('id'); }
            $changed = self::replace($example, TheoryAuthoredAdapter::render($node)) || $changed;
        }
        foreach (array_reverse(iterator_to_array($xpath->query('.//table', $root))) as $table) {
            if (str_contains($table->getAttribute('class'), 'tense-forms-table')) { continue; }
            // These three fields have an explicit canonical equivalent. Other
            // table semantics must not be moved onto the scroll-region wrapper.
            if (!self::supportedAttributes($table, ['id', 'class', 'aria-label'])) { return new HtmlString($html); }
            $node = ['kind' => 'table', 'content_html' => new HtmlString(self::inner($table)), 'caption' => $table->getAttribute('aria-label')];
            if ($table->hasAttribute('id')) { $node['id'] = $table->getAttribute('id'); }
            $parent = $table->parentNode;
            if ($parent instanceof DOMElement && $parent->tagName === 'div' && self::onlyElement($parent, $table)) {
                if (!self::supportedAttributes($parent, ['id', 'class'])) { return new HtmlString($html); }
                if ($parent->hasAttribute('id')) { $node['aliases'] = [$parent->getAttribute('id')]; }
                $changed = self::replace($parent, TheoryAuthoredAdapter::render($node)) || $changed;
            } else { $changed = self::replace($table, TheoryAuthoredAdapter::render($node)) || $changed; }
        }
        return new HtmlString($changed ? self::inner($root) : $html);
    }

    /** Normalize already recognized rich sections, retaining their exact anchors. */
    public static function sections(string $html): HtmlString
    {
        $parsed = self::parse($html);
        if ($parsed === null) { return new HtmlString($html); }
        [$dom, $root, $xpath] = $parsed;
        $changed = false;
        foreach (iterator_to_array($xpath->query('./section', $root)) as $section) {
            $class = $section->getAttribute('class');
            if (!str_contains($class, 'theory-rich-section') && !str_contains($class, 'theory-html-section')) { continue; }
            $heading = $xpath->query('./h2|./h3|./h4', $section)->item(0);
            if (!$heading) { continue; }
            // The shared native header accepts a plain title. Keep an authored
            // rich heading verbatim instead of flattening emphasis or anchors.
            if (!self::supportedAttributes($section, ['id', 'class'], true)
                || !self::supportedAttributes($heading, ['id', 'class'])
                || $xpath->query('./*|./comment()', $heading)->length > 0) { return new HtmlString($html); }
            $node = ['kind' => 'section', 'title' => trim($heading->textContent), 'layout' => 'stack', 'items' => [], 'footer' => false,
                'attrs' => self::technicalAttributes($section)];
            if ($section->hasAttribute('id')) { $node['id'] = $section->getAttribute('id'); }
            $aliases = [];
            if ($heading->hasAttribute('id')) { $aliases[] = $heading->getAttribute('id'); }
            $body = '';
            foreach (iterator_to_array($section->childNodes) as $child) {
                if ($child === $heading) { continue; }
                if ($child instanceof DOMElement && in_array($child->getAttribute('class'), ['theory-section-body', 'theory-rich-section-body'], true)) {
                    if (!self::supportedAttributes($child, ['id', 'class'])) { return new HtmlString($html); }
                    if ($child->hasAttribute('id')) { $aliases[] = $child->getAttribute('id'); }
                    $body .= self::inner($child);
                } else { $body .= $dom->saveHTML($child); }
            }
            $node['aliases'] = array_values(array_unique($aliases));
            // An empty anchor alias is fine for navigation, but not as the source
            // of an accessible name/description or an ARIA-controlled region.
            foreach ($xpath->query('.//*[@aria-labelledby or @aria-describedby or @aria-details or @aria-controls or @aria-owns]', $root) as $reference) {
                foreach (['aria-labelledby', 'aria-describedby', 'aria-details', 'aria-controls', 'aria-owns'] as $attribute) {
                    $ids = preg_split('/\s+/', trim($reference->getAttribute($attribute)), -1, PREG_SPLIT_NO_EMPTY);
                    if (array_intersect($ids, $node['aliases']) !== []) { return new HtmlString($html); }
                }
            }
            $node['items'][] = ['kind' => 'fragment', 'body_html' => self::fragment($body)];
            $changed = self::replace($section, TheoryAuthoredAdapter::render($node)) || $changed;
        }
        return new HtmlString($changed ? self::inner($root) : $html);
    }

    private static function supportedAttributes(DOMElement $element, array $names, bool $technical = false): bool
    {
        foreach ($element->attributes as $attribute) {
            if (in_array($attribute->name, $names, true)) { continue; }
            if ($technical && $attribute->name !== 'data-theory-component'
                && preg_match('/^(?:data-|aria-)[a-z0-9_.:-]+$/D', $attribute->name)) { continue; }
            return false;
        }
        return true;
    }

    private static function technicalAttributes(DOMElement $element): array
    {
        $attributes = [];
        foreach ($element->attributes as $attribute) {
            if (preg_match('/^(?:data-|aria-)[a-z0-9_.:-]+$/D', $attribute->name)) {
                $attributes[$attribute->name] = $attribute->value;
            }
        }
        return $attributes;
    }

    private static function insideAny(DOMNode $node, array $ancestors): bool
    {
        for ($parent = $node->parentNode; $parent !== null; $parent = $parent->parentNode) {
            if (in_array($parent, $ancestors, true)) { return true; }
        }
        return false;
    }

    private static function parse(string $html): ?array
    {
        if ($html === '') { return null; }
        foreach (['p','div','section','details','summary','table','thead','tbody','tfoot','tr','th','td','ul','ol','li',
            'h1','h2','h3','h4','h5','h6','blockquote','em','strong','span','a','code'] as $tag) {
            if (preg_match_all('/<'.$tag.'\b[^>]*>/i', $html) !== preg_match_all('/<\/'.$tag.'\s*>/i', $html)) { return null; }
        }
        $dom = new DOMDocument('1.0', 'UTF-8'); $previous = libxml_use_internal_errors(true);
        try {
            $loaded = $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html.'</body></html>', LIBXML_NONET);
            $errors = libxml_get_errors();
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$loaded || !$body || array_filter($errors, static fn ($e) => $e->code !== 801)) { return null; }
        return [$dom, $body, new DOMXPath($dom)];
    }

    private static function inner(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) { $html .= $node->ownerDocument->saveHTML($child); }
        return $html;
    }

    private static function onlyElement(DOMElement $parent, DOMNode $expected): bool
    {
        foreach ($parent->childNodes as $child) {
            if ($child !== $expected && ($child->nodeType !== XML_TEXT_NODE || trim($child->textContent) !== '')) { return false; }
        }
        return true;
    }

    private static function replace(DOMNode $node, string $html): bool
    {
        $parsed = self::parse($html);
        if ($parsed === null || !$node->parentNode) { return false; }
        foreach (iterator_to_array($parsed[1]->childNodes) as $child) {
            $node->parentNode->insertBefore($node->ownerDocument->importNode($child, true), $node);
        }
        $node->parentNode->removeChild($node);
        return true;
    }
}
