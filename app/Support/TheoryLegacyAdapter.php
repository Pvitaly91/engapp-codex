<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/** Render-only normalization of trusted native lesson fields. No database or frozen projection writes. */
final class TheoryLegacyAdapter
{
    public static function section(object $block, array $data, array $pointSections = [], array $lessonLinks = [], bool $embedded = false, ?array $design = null): array
    {
        $type = $block->type ?? '';
        $nativeProse = is_array($design)
            && ($design['uuid'] ?? null) === ($block->uuid ?? null)
            && ($design['component'] ?? null) === $type
            && ($block->locale ?? null) === 'uk'
            && TheoryHtmlAdapter::verifiedNativeDesign($design);
        $node = ['kind' => 'section', 'id' => 'block-'.$block->id, 'title' => $data['title'] ?? '',
            'variant' => match ($type) { 'summary-list' => 'summary', 'mistakes-grid' => 'mistakes', default => 'plain' },
            'level' => $block->level ?? null, 'embedded' => $embedded, 'footer' => true,
            'layout' => in_array($type, ['usage-panels', 'mistakes-grid'], true) ? 'stack' : 'plain', 'items' => [], 'tail' => []];
        $accent = $nativeProse ? TheoryHtmlAdapter::nativeAccent($design) : null;
        if ($accent !== null) {
            $node['attrs'] = ['data-theory-palette' => 'canonical', 'data-theory-accent' => $accent];
        }
        $reference = $nativeProse ? TheoryHtmlAdapter::nativePointPanels($design) : null;
        if ($reference !== null) {
            $node['reference'] = true;
            $node['attrs']['data-theory-reference'] = 'native';
            $node['attrs']['data-theory-section-variant'] = $node['variant'];
        }
        if (!empty($data['intro'])) { $node['intro_html'] = self::rich($data['intro'], $design, '/intro'); }
        if (in_array($type, ['forms-grid', 'lesson-rule-cards'], true)) {
            $node['layout'] = 'grid2';
            foreach ($data['items'] ?? [] as $index => $item) {
                $title = (string) ($item['title'] ?? '');
                $normalizedTitle = Str::lower(trim(preg_replace('/^\d+[.\d\s]*\s*/u', '', $title) ?? $title));
                $card = ['kind' => 'form', 'label' => $item['label'] ?? '', 'title' => $title, 'empty_title' => true,
                    'url' => $item['url'] ?? $lessonLinks[$title] ?? $lessonLinks[$normalizedTitle] ?? null,
                    'subtitle_html' => self::rich((string) TheoryInlineHtml::render($item['subtitle'] ?? ''), $design, '/items/'.$index.'/subtitle'),
                    'rules' => []];
                foreach ($item['rules'] ?? [] as $ruleIndex => $rule) {
                    $card['rules'][] = ['label' => $rule['label'] ?? '', 'formula' => new HtmlString($rule['formula'] ?? ''),
                        'text_html' => self::rich($rule['text'] ?? '', $design, '/items/'.$index.'/rules/'.$ruleIndex.'/text'),
                        'example_html' => new HtmlString($rule['example'] ?? '')];
                }
                $mixed = self::mixedExample($item['subtitle'] ?? '', $design, '/items/'.$index.'/subtitle');
                if ($mixed !== null) {
                    $card['subtitle_html'] = $mixed['context'];
                    $card['examples'] = [['kind' => 'example', 'variant' => 'inline', 'en' => $mixed['en'], 'uk' => $mixed['uk']]];
                }
                self::detail($card, $pointSections, $index);
                $node['items'][] = $card;
            }
        } elseif ($type === 'usage-panels') {
            foreach ($data['sections'] ?? [] as $index => $section) {
                $description = (string) ($section['description'] ?? '');
                $descriptionHtml = self::rich($description, $design, '/sections/'.$index.'/description');
                $item = ['kind' => 'usage', 'label' => $section['label'] ?? '',
                    'number' => !empty($section['label']) && is_numeric($index) ? $index + 1 : null,
                    'accent' => $section['color'] ?? $design['section_colors'][$index] ?? 'slate',
                    'body_html' => $descriptionHtml,
                    'body_inline' => !self::blockHtml((string) $descriptionHtml),
                    'examples' => array_map(self::example(...), $section['examples'] ?? []), 'tail' => []];
                $panel = $reference['points_by_index'][$index] ?? null;
                if ($panel !== null && in_array(hash('sha256', $description), $panel['description_sha256'], true)) {
                    // A reviewed whole point, never one box per paragraph.
                    $item['label'] = $panel['label'];
                    $item['number'] = $index + 1;
                    $item['accent'] = $panel['accent'];
                    $item['attrs'] = ['data-theory-point-panel' => '', 'data-theory-accent' => $panel['accent']];
                } elseif ($nativeProse && empty($section['label'])) {
                    // Unannotated paragraphs keep their complete plain flow.
                    $item = ['kind' => 'fragment', 'body_html' => $descriptionHtml,
                        'body_role' => self::blockHtml((string) $descriptionHtml) ? null : 'paragraph',
                        'examples' => $item['examples'], 'tail' => []];
                }
                if (!empty($section['note'])) { $item['tail'][] = ['kind' => 'note', 'html' => self::rich($section['note'], $design, '/sections/'.$index.'/note')]; }
                self::detail($item, $pointSections, $index);
                $node['items'][] = $item;
            }
        } elseif ($type === 'comparison-table') {
            if ($nativeProse && !empty($data['sections'])) { $node['layout'] = 'stack'; }
            foreach ($data['sections'] ?? [] as $index => $section) {
                $item = ['kind' => 'usage', 'body_html' => self::rich($section['description'] ?? '', $design, '/sections/'.$index.'/description'), 'body_inline' => false];
                if ($nativeProse && empty($section['label'])) {
                    $item['kind'] = 'fragment';
                    $item['body_role'] = self::blockHtml((string) $item['body_html']) ? null : 'paragraph';
                }
                self::detail($item, $pointSections, $index);
                $node['items'][] = $item;
            }
            $table = ['kind' => 'table', 'caption' => $data['title'] ?? '', 'headers' => $data['headers'] ?? [
                __('theory_blocks.comparison_table.english_sentence'), __('theory_blocks.comparison_table.translation'), __('theory_blocks.comparison_table.forms_notes'),
            ], 'header_case' => isset($data['headers']) ? 'sentence' : 'upper',
                'min_width' => $data['table_min_width'] ?? null, 'column_min_widths' => $data['column_min_widths'] ?? [], 'rows' => []];
            foreach ($data['rows'] ?? [] as $index => $row) {
                $cells = [];
                if (isset($row['cells'])) {
                    foreach ($row['cells'] as $cellIndex => $cell) { $cells[] = self::rich($cell, $design, '/rows/'.$index.'/cells/'.$cellIndex); }
                } else {
                    $note = ['role' => 'note', 'html' => self::rich($row['note'] ?? '', $design, '/rows/'.$index.'/note')];
                    self::detail($note, $pointSections, $index);
                    $cells = [
                        ['role' => 'english', 'html' => new HtmlString(TheoryComponents::html(['kind' => 'example', 'variant' => 'code', 'en' => TheoryInlineHtml::render($row['en'] ?? '')]))],
                        ['role' => 'translation', 'html' => TheoryInlineHtml::render($row['ua'] ?? '')],
                        $note,
                    ];
                }
                $table['rows'][] = ['cells' => $cells];
            }
            $node['items'][] = $table;
            if (!empty($data['outro'])) { $node['tail'][] = ['kind' => 'fragment', 'body_html' => self::rich($data['outro'], $design, '/outro')]; }
            if (!empty($data['warning'])) { $node['tail'][] = ['kind' => 'note', 'variant' => 'warning', 'html' => self::rich($data['warning'], $design, '/warning')]; }
        } elseif ($type === 'mistakes-grid') {
            $node['fallback'] = '⚠';
            foreach ($data['items'] ?? [] as $index => $item) {
                $point = ['kind' => 'mistake', 'label' => $item['label'] ?? '',
                    'number' => !empty($item['label']) && is_numeric($index) ? $index + 1 : null,
                    'accent' => $item['color'] ?? 'rose', 'title' => new HtmlString($item['title'] ?? ''),
                    'wrong_en' => TheoryInlineHtml::render($item['wrong'] ?? ''),
                    'right_en' => self::rich($item['right'] ?? '', $design, '/items/'.$index.'/right'),
                    'hint_html' => self::rich($item['hint'] ?? '', $design, '/items/'.$index.'/hint')];
                $mixed = self::mixedExample($item['right'] ?? '', $design, '/items/'.$index.'/right');
                if ($mixed !== null && !TheoryComponents::present($mixed['context'])) {
                    $point['right_en'] = $mixed['en'];
                    $point['right_uk'] = $mixed['uk'];
                }
                self::detail($point, $pointSections, $index);
                $node['items'][] = $point;
            }
        } elseif ($type === 'summary-list') {
            $node['fallback'] = '✓'; $items = [];
            foreach ($data['items'] ?? [] as $index => $text) {
                $html = self::rich($text, $design, '/items/'.$index);
                $item = ['html' => $html, 'block_html' => self::blockHtml((string) $html)];
                self::detail($item, $pointSections, $index); $items[] = $item;
            }
            $node['items'][] = ['kind' => 'summary', 'items' => $items];
        } elseif ($type === 'tense-forms-table') {
            $headers = array_map(static fn ($header) => $header + [], $data['headers'] ?? []);
            foreach ($headers as &$header) {
                $header['title'] = new HtmlString($header['title'] ?? '');
                if (isset($header['subtitle'])) { $header['subtitle'] = new HtmlString($header['subtitle']); }
            }
            unset($header);
            $rows = $data['rows'] ?? [];
            foreach ($rows as &$row) {
                foreach ($row['cells'] as &$cell) {
                    if (isset($cell['formula'])) { $cell['formula'] = new HtmlString($cell['formula']); }
                    $cell['lines'] = array_map(static fn ($line) => new HtmlString($line), $cell['lines'] ?? []);
                    if (isset($cell['note'])) { $cell['note'] = new HtmlString($cell['note']); }
                }
                unset($cell);
            }
            unset($row);
            $node['items'][] = ['kind' => 'table', 'variant' => 'tense-matrix', 'caption' => $data['title'] ?? '',
                'corner' => $data['corner'] ?? [], 'headers' => $headers, 'rows' => $rows];
            $node['footer'] = false;
        }
        if ($type !== 'comparison-table' && !empty($data['outro'])) { $node['tail'][] = ['kind' => 'fragment', 'body_html' => self::rich($data['outro'], $design, '/outro')]; }
        return $node;
    }

    public static function example(mixed $example): array
    {
        // Legacy Blade used offset coalescing: scalar entries kept an empty
        // example box. Preserve that display instead of rejecting the page or
        // reinterpreting previously undisplayed source text as new content.
        $example = is_array($example) ? $example : [];
        return ['kind' => 'example', 'en' => TheoryInlineHtml::render($example['en'] ?? ''),
            'uk' => TheoryInlineHtml::render($example['uk'] ?? $example['ua'] ?? '')];
    }

    private static function detail(array &$node, array $pointSections, int|string $index): void
    {
        if (isset($pointSections[$index])) { $node['detail'] = ['kind' => 'disclosure', 'section' => $pointSections[$index], 'index' => $index]; }
    }

    private static function rich(string $html, ?array $design, string $pointer): HtmlString
    {
        return TheoryHtmlAdapter::nativeFragment($html, $design, $pointer);
    }

    /** Use only the existing hash-bound language ranges, never infer a pair from prose. */
    private static function mixedExample(string $html, ?array $design, string $pointer): ?array
    {
        if (!isset($design['rich_mixed_fields'][$pointer])) { return null; }
        $annotated = M42NativeDesignPackage::richFragment($html, $design, $pointer);
        // richFragment verifies the complete finite plan and the field hash.
        // Its exact generated spans are the boundary between metadata and views.
        $pattern = '~\A(?:<span class="m42-form-context" lang="uk" data-m42-mixed-role="form-context">(?<context>[^<]*)</span>)?'
            .'<span class="m42-english" lang="en" data-m42-mixed-role="en">(?<en>[^<]*)</span>'
            .'<span class="m42-paired-translation" lang="uk" data-m42-mixed-role="translation">(?<uk>[^<]*)</span>\z~u';
        if ($annotated === $html || preg_match($pattern, $annotated, $parts) !== 1) { return null; }
        return ['context' => new HtmlString($parts['context'] ?? ''),
            'en' => new HtmlString($parts['en']), 'uk' => new HtmlString($parts['uk'])];
    }

    private static function blockHtml(string $html): bool
    {
        return preg_match('/<(?:p|div|ul|ol|table|h[1-6]|section|article)\b/i', $html) === 1;
    }
}
