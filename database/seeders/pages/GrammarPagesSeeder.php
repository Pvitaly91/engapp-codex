<?php

namespace Database\Seeders\Pages;

use App\Models\PageBlock;
use App\Support\Database\Seeder;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GrammarPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = $this->pages();

        DB::transaction(function () use ($pages) {
            foreach ($pages as $slug => $blocks) {
                PageBlock::query()
                    ->where('page_slug', $slug)
                    ->where('seeder', static::class)
                    ->delete();

                foreach (array_values($blocks) as $index => $data) {
                    PageBlock::create([
                        'page_slug' => $slug,
                        'locale' => $data['locale'] ?? 'uk',
                        'area' => $data['area'],
                        'type' => $data['type'],
                        'label' => $data['label'] ?? null,
                        'content' => $data['content'] ?? null,
                        'position' => $data['position'] ?? $index + 1,
                        'seeder' => static::class,
                    ]);
                }
            }
        });
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    protected function pages(): array
    {
        $pages = [];

        foreach (config('engram.pages', []) as $slug => $page) {
            $blocks = $this->parseStaticPage($page['legacy_view'] ?? null);

            if (! empty($blocks)) {
                $pages[$slug] = $blocks;
            }
        }

        return $pages;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function parseStaticPage(?string $legacyView): array
    {
        if (! $legacyView) {
            return [];
        }

        $path = resource_path('views/' . str_replace('.', '/', $legacyView) . '.blade.php');

        if (! is_file($path)) {
            return [];
        }

        $contents = file_get_contents($path);
        $contents = preg_replace('/@include[^\n]*\n?/', '', $contents) ?? $contents;
        $contents = trim($contents);

        if ($contents === '') {
            return [];
        }

        $html = '<div>' . $contents . '</div>';

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $blocks = [];
        $position = 1;

        $titleNode = $xpath->query('//h2[contains(concat(" ", normalize-space(@class), " "), " gw-title ")]')->item(0);
        $subtitleNode = $xpath->query('//p[contains(concat(" ", normalize-space(@class), " "), " gw-sub ")]')->item(0);

        if ($titleNode) {
            $blocks[] = [
                'area' => 'header',
                'type' => 'header',
                'content' => [
                    'title' => trim($titleNode->textContent),
                    'subtitle' => $subtitleNode ? $this->nodeInnerHtml($subtitleNode) : null,
                ],
                'position' => $position++,
            ];
        }

        $columns = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " gw-grid ")]//div[contains(concat(" ", normalize-space(@class), " "), " gw-col ")]');

        foreach ($columns as $index => $column) {
            $area = $index === 0 ? 'left' : ($index === 1 ? 'right' : 'full');
            /** @var DOMElement $column */
            foreach ($this->boxNodes($column) as $box) {
                $block = $this->parseBox($box, $area);

                if ($block !== null) {
                    $block['position'] = $position++;
                    $blocks[] = $block;
                }
            }
        }

        $fullWidthBoxes = $xpath->query('//section[contains(concat(" ", normalize-space(@class), " "), " grammar-card ")]//div[contains(concat(" ", normalize-space(@class), " "), " gw-box ") and not(ancestor::div[contains(concat(" ", normalize-space(@class), " "), " gw-col ")])]');

        foreach ($fullWidthBoxes as $box) {
            $block = $this->parseBox($box, 'full');

            if ($block !== null) {
                $block['position'] = $position++;
                $blocks[] = $block;
            }
        }

        return array_values(array_filter($blocks));
    }

    /**
     * @return iterable<int, DOMElement>
     */
    protected function boxNodes(DOMElement $column): iterable
    {
        foreach ($column->childNodes as $child) {
            if ($child instanceof DOMElement && $this->elementHasClass($child, 'gw-box')) {
                yield $child;
            }
        }
    }

    protected function parseBox(DOMElement $box, string $area): ?array
    {
        $labelNode = $this->firstChildWithTag($box, 'h3');
        $label = $labelNode ? trim($labelNode->textContent) : null;
        $content = [];
        $type = 'text';

        if ($this->elementHasClass($box, 'gw-box--scroll')) {
            $content['scroll'] = true;
        }

        if ($hint = $this->firstDescendantWithClass($box, 'div', 'gw-hint')) {
            $type = 'hint';
            $emoji = $this->firstDescendantWithClass($hint, 'div', 'gw-emoji');
            $bodyParagraphs = [];

            foreach ($hint->getElementsByTagName('p') as $paragraph) {
                $bodyParagraphs[] = $this->nodeInnerHtml($paragraph);
            }

            $content['emoji'] = $emoji ? trim($emoji->textContent) : null;
            $content['body'] = array_values(array_filter($bodyParagraphs));
            $content['examples'] = $this->extractExamples($box);
        } elseif ($this->firstDescendantWithClass($box, 'pre', 'gw-formula')) {
            $type = 'formula';
            $variants = [];

            $badges = iterator_to_array($this->descendantsWithClass($box, 'div', 'gw-code-badge'));
            $formulas = iterator_to_array($this->descendantsWithClass($box, 'pre', 'gw-formula'));

            $count = max(count($badges), count($formulas));

            for ($i = 0; $i < $count; $i++) {
                $variants[] = [
                    'label' => isset($badges[$i]) ? trim($badges[$i]->textContent) : null,
                    'text' => isset($formulas[$i]) ? $this->nodeInnerHtml($formulas[$i]) : null,
                ];
            }

            $content['variants'] = array_values(array_filter($variants, fn ($variant) => $variant['text'] ?? null));
        } elseif ($chips = $this->firstDescendantWithClass($box, 'div', 'gw-chips')) {
            $type = $area === 'header' ? 'chips' : 'chips';
            $content['chips'] = [];

            foreach ($this->descendantsWithClass($chips, 'span', 'gw-chip') as $chip) {
                $content['chips'][] = trim($chip->textContent);
            }

            $content['examples'] = $this->extractExamples($box);
        } elseif ($table = $this->firstDescendantWithClass($box, 'table', 'gw-table')) {
            $type = 'table';
            $headings = [];
            $rows = [];

            foreach ($table->getElementsByTagName('th') as $th) {
                $headings[] = $this->nodeInnerHtml($th);
            }

            foreach ($table->getElementsByTagName('tr') as $tr) {
                $cells = [];
                foreach ($tr->getElementsByTagName('td') as $td) {
                    $cells[] = $this->nodeInnerHtml($td);
                }

                if (! empty($cells)) {
                    $rows[] = $cells;
                }
            }

            $content['headings'] = $headings;
            $content['rows'] = $rows;
        } elseif ($this->firstDescendantWithClass($box, 'ul', 'gw-list')) {
            $type = 'list';
            $items = [];
            foreach ($this->descendantsWithClass($box, 'li') as $li) {
                if ($li->parentNode && $li->parentNode instanceof DOMElement && $this->elementHasClass($li->parentNode, 'gw-list')) {
                    $items[] = $this->nodeInnerHtml($li);
                }
            }
            $content['items'] = $items;
            $content['examples'] = $this->extractExamples($box);
        } elseif ($examples = $this->extractExamples($box)) {
            $type = 'examples';
            $content['examples'] = $examples;
        } else {
            $paragraphs = [];
            foreach ($box->getElementsByTagName('p') as $paragraph) {
                $paragraphs[] = $this->nodeInnerHtml($paragraph);
            }
            $content['body'] = array_values(array_filter($paragraphs));
        }

        $content = array_filter($content, fn ($value) => $value !== null && $value !== []);

        if ($label === null && empty($content)) {
            return null;
        }

        return [
            'area' => $area,
            'type' => $type,
            'label' => $label,
            'content' => $content ?: null,
        ];
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    protected function extractExamples(DOMElement $box): array
    {
        $examples = [];

        foreach ($this->descendantsWithClass($box, 'div', 'gw-ex') as $example) {
            $en = $this->firstDescendantWithClass($example, 'div', 'gw-en');
            $ua = $this->firstDescendantWithClass($example, 'div', 'gw-ua');

            $examples[] = [
                'en' => $en ? $this->nodeInnerHtml($en) : null,
                'ua' => $ua ? $this->nodeInnerHtml($ua) : null,
            ];
        }

        return array_values(array_filter($examples, fn ($example) => ($example['en'] ?? null) || ($example['ua'] ?? null)));
    }

    protected function firstChildWithTag(DOMElement $element, string $tag): ?DOMElement
    {
        foreach ($element->childNodes as $child) {
            if ($child instanceof DOMElement && Str::lower($child->tagName) === Str::lower($tag)) {
                return $child;
            }
        }

        return null;
    }

    protected function firstDescendantWithClass(DOMElement $element, string $tag, string $class): ?DOMElement
    {
        foreach ($this->descendantsWithClass($element, $tag, $class) as $node) {
            return $node;
        }

        return null;
    }

    /**
     * @return iterable<int, DOMElement>
     */
    protected function descendantsWithClass(DOMElement $element, string $tag, ?string $class = null): iterable
    {
        foreach ($element->getElementsByTagName($tag) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            if ($class === null || $this->elementHasClass($node, $class)) {
                yield $node;
            }
        }
    }

    protected function elementHasClass(DOMElement $element, string $class): bool
    {
        $classes = preg_split('/\s+/', $element->getAttribute('class') ?? '', -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return in_array($class, $classes, true);
    }

    protected function nodeInnerHtml(DOMNode $node): string
    {
        $innerHTML = '';
        foreach ($node->childNodes as $child) {
            $innerHTML .= $node->ownerDocument?->saveHTML($child) ?? '';
        }

        return trim($innerHTML);
    }
}
