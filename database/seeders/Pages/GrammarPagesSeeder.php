<?php

namespace Database\Seeders\Pages;

use App\Models\Page;
use App\Models\TextBlock;
use App\Support\Database\Seeder;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GrammarPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = config('engram.pages', []);

        foreach ($pages as $slug => $config) {
            $view = $config['view'] ?? null;
            $path = $view ? $this->resolveViewPath($view) : null;

            if (! $path || ! File::exists($path)) {
                continue;
            }

            $parsed = $this->parseBlade($path);

            if (! $parsed['title']) {
                continue;
            }

            $page = Page::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $parsed['title'],
                    'text' => $parsed['subtitle_text'],
                ]
            );

            TextBlock::where('page_id', $page->id)
                ->where('seeder', static::class)
                ->delete();

            if ($parsed['subtitle_html']) {
                TextBlock::create([
                    'page_id' => $page->id,
                    'locale' => $parsed['locale'],
                    'type' => 'subtitle',
                    'column' => 'header',
                    'heading' => null,
                    'css_class' => null,
                    'sort_order' => 0,
                    'body' => $parsed['subtitle_html'],
                    'seeder' => static::class,
                ]);
            }

            foreach ($parsed['blocks'] as $index => $block) {
                TextBlock::create([
                    'page_id' => $page->id,
                    'locale' => $parsed['locale'],
                    'type' => 'box',
                    'column' => $block['column'],
                    'heading' => $block['heading'],
                    'css_class' => $block['css_class'],
                    'sort_order' => $index + 1,
                    'body' => $block['body'],
                    'seeder' => static::class,
                ]);
            }
        }
    }

    protected function resolveViewPath(string $view): ?string
    {
        $relative = Str::replace('.', DIRECTORY_SEPARATOR, $view) . '.blade.php';

        return resource_path('views/' . $relative);
    }

    protected function parseBlade(string $path): array
    {
        $content = File::get($path);
        $content = preg_replace('/@include\s*\(.*?\)\s*/', '', $content);
        $content = preg_replace('/{{--.*?--}}/s', '', $content);
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        $content = trim($content);

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $section = $xpath->query('//section[contains(@class, "grammar-card")]')->item(0);

        if (! $section instanceof DOMElement) {
            return [
                'title' => null,
                'subtitle_html' => null,
                'subtitle_text' => null,
                'locale' => 'uk',
                'blocks' => [],
            ];
        }

        $locale = $section->getAttribute('lang') ?: 'uk';
        $header = $xpath->query('./header', $section)->item(0);

        $titleNode = $header ? $xpath->query('.//h2', $header)->item(0) : null;
        $subtitleNode = $header ? $xpath->query('.//p[contains(@class, "gw-sub")]', $header)->item(0) : null;

        $title = $titleNode ? trim($titleNode->textContent) : null;
        $subtitleHtml = $subtitleNode ? $this->innerHtml($subtitleNode) : null;
        $subtitleText = $subtitleNode ? trim($subtitleNode->textContent) : null;

        $grid = $xpath->query('./div[contains(@class, "gw-grid")]', $section)->item(0);

        $blocks = [];

        if ($grid instanceof DOMElement) {
            $columns = $xpath->query('./div[contains(@class, "gw-col")]', $grid);

            foreach ($columns as $columnIndex => $columnNode) {
                if (! $columnNode instanceof DOMElement) {
                    continue;
                }

                $columnKey = $columnIndex === 0 ? 'left' : 'right';

                $boxes = $xpath->query('./div[contains(@class, "gw-box")]', $columnNode);

                foreach ($boxes as $boxNode) {
                    if (! $boxNode instanceof DOMElement) {
                        continue;
                    }

                    $headingNode = $xpath->query('./h3', $boxNode)->item(0);
                    $heading = $headingNode ? trim($headingNode->textContent) : null;

                    $body = $this->boxBody($boxNode);

                    $classAttr = $boxNode->getAttribute('class');
                    $cssClass = collect(explode(' ', $classAttr))
                        ->map(fn ($class) => trim($class))
                        ->reject(fn ($class) => $class === '' || $class === 'gw-box')
                        ->implode(' ');

                    $blocks[] = [
                        'column' => $columnKey,
                        'heading' => $heading,
                        'css_class' => $cssClass ?: null,
                        'body' => $body,
                    ];
                }
            }
        }

        return [
            'title' => $title,
            'subtitle_html' => $subtitleHtml,
            'subtitle_text' => $subtitleText,
            'locale' => $locale,
            'blocks' => $blocks,
        ];
    }

    protected function boxBody(DOMElement $box): string
    {
        $html = '';

        foreach ($box->childNodes as $child) {
            if ($child instanceof DOMElement && strtolower($child->tagName) === 'h3') {
                continue;
            }

            $html .= $box->ownerDocument->saveHTML($child);
        }

        return $this->cleanHtml($html);
    }

    protected function innerHtml(DOMElement $element): string
    {
        $html = '';

        foreach ($element->childNodes as $child) {
            $html .= $element->ownerDocument->saveHTML($child);
        }

        return $this->cleanHtml($html);
    }

    protected function cleanHtml(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim($html);
        $html = preg_replace("/\n\s+/", "\n", $html);
        $html = preg_replace("/\s+\n/", "\n", $html);

        return trim($html);
    }
}
