<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * Render-only section state. Plain strings are escaped; Htmlable must come from
 * an already permitted renderer. This class is not a sanitizer or a content store.
 */
final readonly class TheorySection
{
    private function __construct(
        public string $key,
        public string $title,
        public ?string $number,
        public HtmlString $main,
        public ?HtmlString $detail,
        public array $detailReferences,
        public ?string $diagnosticCode,
    ) {}

    /**
     * An explicit author-approved pair is optional. No pair means full content.
     * Any invalid pair fails closed to the caller's original full content.
     */
    public static function resolve(
        string $key,
        string $title,
        string|Htmlable $fullContent,
        ?string $number = null,
        mixed $pair = null,
    ): self {
        $full = self::safeContent($fullContent);
        $fallback = static fn (?string $reason): self => new self($key, $title, $number, $full, null, [], $reason);

        if ($pair === null) {
            return $fallback(null);
        }
        if (! is_array($pair)) {
            return $fallback('invalid-pair-shape');
        }
        if (trim(strip_tags($title)) === '') {
            return $fallback('missing-title-context');
        }
        if (($pair['source_key'] ?? null) !== $key) {
            return $fallback('source-key-mismatch');
        }
        $revision = $pair['source_revision'] ?? null;
        if (! is_string($revision) || ! preg_match('/^[a-f0-9]{64}$/D', $revision)
            || ! hash_equals(hash('sha256', $full->toHtml()), $revision)) {
            return $fallback('source-revision-mismatch');
        }
        foreach (['main', 'detail'] as $field) {
            if (! isset($pair[$field]) || (! is_string($pair[$field]) && ! $pair[$field] instanceof Htmlable)) {
                return $fallback('missing-or-invalid-'.$field);
            }
        }
        $references = $pair['detail_references'] ?? null;
        if (! is_array($references) || $references === [] || ! array_is_list($references)) {
            return $fallback('missing-detail-references');
        }
        foreach ($references as $reference) {
            if (! is_string($reference) || ! preg_match('/^[A-Za-z][A-Za-z0-9_.:-]*$/D', $reference)) {
                return $fallback('invalid-detail-reference');
            }
        }
        if (count(array_unique($references)) !== count($references)) {
            return $fallback('duplicate-detail-reference');
        }
        $main = self::safeContent($pair['main']);
        $detail = self::safeContent($pair['detail']);
        if (trim(strip_tags($main->toHtml())) === '') {
            return $fallback('empty-main');
        }
        if (trim(strip_tags($detail->toHtml())) === '') {
            return $fallback('empty-detail');
        }
        $referenceProblem = self::validateReferences($full, $main, $detail, $references, self::controlId($key));
        if ($referenceProblem !== null) {
            return $fallback($referenceProblem);
        }

        return new self($key, $title, $number, $main, $detail, $references, null);
    }

    public function detailsId(): string
    {
        return self::controlId($this->key);
    }

    private static function controlId(string $key): string
    {
        return 'theory-details-'.substr(hash('sha256', $key), 0, 20);
    }

    public function contextTitle(): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags($this->title)) ?? $this->title);
    }

    public function visibleNumber(): ?string
    {
        if ($this->number === null || $this->number === '') {
            return null;
        }
        // Author numbering already in a title is not repeated by decoration.
        if (preg_match('/^\s*'.preg_quote($this->number, '/').'[.)]?\s+/u', $this->title)) {
            return null;
        }

        return $this->number;
    }

    private static function safeContent(string|Htmlable $content): HtmlString
    {
        return new HtmlString($content instanceof Htmlable ? $content->toHtml() : e($content));
    }

    private static function validateReferences(HtmlString $full, HtmlString $main, HtmlString $detail, array $references, string $controlId): ?string
    {
        $documents = [];
        foreach (['full' => $full, 'main' => $main, 'detail' => $detail] as $name => $html) {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $previous = libxml_use_internal_errors(true);
            try {
                $loaded = $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'.$html->toHtml().'</body></html>', LIBXML_NONET);
                $errors = libxml_get_errors();
            } finally {
                libxml_clear_errors();
                libxml_use_internal_errors($previous);
            }
            if (! $loaded || array_filter($errors, static fn ($error): bool => $error->code !== 801)) {
                return 'unparseable-'.$name;
            }
            $ids = [];
            foreach ($dom->getElementsByTagName('*') as $node) {
                if ($node instanceof DOMElement && $node->hasAttribute('id')) {
                    $id = $node->getAttribute('id');
                    if (isset($ids[$id])) {
                        return 'duplicate-'.$name.'-anchor';
                    }
                    $ids[$id] = $node;
                }
            }
            $documents[$name] = ['dom' => $dom, 'ids' => $ids];
        }
        $normalizeSpaces = static fn (string $text): string => trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        foreach (['main', 'detail'] as $name) {
            if ($normalizeSpaces($documents[$name]['dom']->getElementsByTagName('body')->item(0)->textContent) === '') {
                return 'empty-'.$name;
            }
        }
        if (array_intersect_key($documents['main']['ids'], $documents['detail']['ids']) !== []) {
            return 'main-detail-anchor-collision';
        }
        if (isset($documents['main']['ids'][$controlId]) || isset($documents['detail']['ids'][$controlId])) {
            return 'generated-control-anchor-collision';
        }
        $referenceText = '';
        foreach ($references as $reference) {
            $source = $documents['full']['ids'][$reference] ?? null;
            $rendered = $documents['detail']['ids'][$reference] ?? null;
            if (! $source || ! $rendered) {
                return 'detail-reference-not-found';
            }
            if ($documents['full']['dom']->saveHTML($source) !== $documents['detail']['dom']->saveHTML($rendered)) {
                return 'detail-reference-changed';
            }
            $referenceText .= $source->textContent;
        }
        // References preserve their authored order and text. Wrappers may differ.
        $detailText = $documents['detail']['dom']->getElementsByTagName('body')->item(0)->textContent;
        if ($normalizeSpaces($referenceText) !== $normalizeSpaces($detailText)) {
            return 'detail-reference-order-or-content-mismatch';
        }

        return null;
    }
}
