<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Render-only normalization after the caller's immutable package/owner guards.
 * This never participates in a frozen projection or selects a payload-supplied view.
 * Package plans may supply content grouping; only semantic nodes reach the views.
 */
final class TheoryAuthoredAdapter
{
    public static function section(object $block, array $data, ?array $pointSections = null, ?array $compact = null, bool $guarded = true): array
    {
        $source = $data['author_section'];
        $kind = $source['native_kind'] ?? $source['kind'] ?? 'usage';
        $design = $data['m41_existing_design'] ?? $data['m43_native_design'] ?? $data['m44_native_design'] ?? [];
        $node = ['kind' => 'section', 'id' => 'block-'.$block->id, 'aliases' => [$source['id']],
            'variant' => match ($kind) { 'summary', 'summary-list' => 'summary', 'mistakes', 'mistakes-grid' => 'mistakes', default => 'plain' },
            'title' => $data['title'] ?? $source['title'], 'level' => $block->level ?? null,
            'layout' => 'stack', 'items' => [], 'notes' => [], 'footer' => true];
        $provenance = self::provenance($data);
        if ($provenance !== null) { $node['attrs'] = ['data-'.$provenance.'-author-section' => $source['id']]; }
        if (isset($source['intro_uk'])) { $node['intro_html'] = self::paragraphs([$source['intro_uk']]); }

        // The two-card model is a general form-card capability, independent of owner.
        if (isset($source['cards'])) {
            $node['layout'] = 'grid2';
            foreach ($source['cards'] as $index => $card) {
                $item = ['kind' => 'form', 'id' => $card['id'], 'title' => $card['title'],
                    'attrs' => $provenance ? ['data-'.$provenance.'-form-card' => ''] : [],
                    'rows' => array_map(static fn ($row) => [
                        'label' => $row['label_uk'], 'formula' => $row['formula'], 'en' => $row['en'], 'uk' => $row['uk'],
                        'attrs' => $provenance ? ['data-'.$provenance.'-form-row' => ''] : [],
                    ], $card['rows']), 'tail' => []];
                if (isset($card['note_uk'])) { $item['tail'][] = self::note($card['note_uk']); }
                self::ownDetail($item, $card['detail'] ?? null, $card['title'], $index, $guarded);
                $node['items'][] = $item;
            }
        } elseif ($compact !== null && $guarded) {
            $node['layout'] = $kind === 'forms-grid' && ($compact[0]['layout'] ?? null) !== 'form-comparison' ? 'grid2' : 'stack';
            foreach ($compact as $index => $group) {
                $item = self::compactGroup($block, $source, $group, $index, $design, $pointSections ?? []);
                if ($provenance !== null) {
                    $item['attrs'] = ['data-'.$provenance.'-compact-group' => $group['id']];
                    if (!$group['disclosure']) { $item['attrs']['data-'.$provenance.'-basic-point'] = $group['sources'][0]['id']; }
                }
                $node['items'][] = $item;
            }
        } else {
            if (isset($design['forms'])) {
                $forms = $design['forms'];
                // Preserve all three original column labels and source row labels.
                $node['items'][] = ['kind' => 'fragment', 'body_html' => new HtmlString(implode('', array_map(
                    static fn ($column) => '<span>'.e($column).'</span> ', $forms['columns'])))];
                foreach ($forms['rows'] as $row) {
                    $cards = [];
                    foreach ($row['cells'] as $cell) {
                        $cards[] = ['kind' => 'form', 'title' => $cell['en'], 'subtitle_html' => new HtmlString(e($cell['uk']))];
                    }
                    $node['items'][] = ['kind' => 'fragment', 'id' => 'block-'.$block->id.'-form-row-'.$row['row_index'],
                        'title' => $row['label'], 'items' => [['kind' => 'group', 'layout' => 'grid2', 'items' => $cards]]];
                }
            }
            $pointNodes = [];
            foreach ($source['points'] ?? [] as $index => $point) {
                $config = $design['points'][$index] ?? [];
                $pointKind = $config['component'] ?? $kind;
                $item = self::point($point, $pointKind, $index, $config, $data['sections'][$index] ?? null);
                if ($provenance !== null) { $item['attrs'] = ['data-'.$provenance.'-basic-point' => $point['id']]; }
                if (isset($source['native_kind']) || isset($design['points'])) {
                    $item['aliases'][] = 'block-'.$block->id.'-point-'.$point['id'];
                }
                if (isset($pointSections[$index])) {
                    $item['detail'] = ['kind' => 'disclosure', 'section' => $pointSections[$index], 'index' => $index];
                } elseif (isset($point['basic_uk'])) {
                    self::ownDetail($item, $point['detail'] ?? null, $point['title'], $index, $guarded);
                } elseif (!$guarded && isset($point['detail'])) {
                    // Older accepted details have no authored ID. The rejected-
                    // mapping fallback must still expose every complete detail.
                    $detailId = $point['detail']['id'] ?? ($source['id'].'-'.$point['id'].'-detail');
                    $item['tail'][] = self::detailFragment($point['detail'], 'block-'.$detailId);
                }
                $pointNodes[] = $item;
            }
            if ($kind === 'forms-grid' && !isset($design['forms'])) { $node['layout'] = 'grid2'; }
            array_push($node['items'], ...$pointNodes);
        }
        if (isset($source['table']) && !isset($design['forms'])) {
            $node['tail'][] = self::table($source['table'], $source['title'], 'block-'.$block->id.'-table');
        }
        foreach ($source['notes_uk'] ?? [] as $note) { $node['tail'][] = self::note($note); }
        if (($compact[0]['layout'] ?? null) === 'form-comparison') {
            $node['tail'][] = ['kind' => 'group', 'layout' => 'inline', 'items' => array_map(
                static fn ($example) => self::example($example, 'short-answer'), $source['note_examples'] ?? [])];
        } else {
            foreach ($source['note_examples'] ?? [] as $example) { $node['tail'][] = self::example($example); }
        }
        return $node;
    }

    private static function point(array $point, string $kind, int $index, array $config = [], ?array $native = null): array
    {
        $paragraphs = $point['basic_uk'] ?? $point['paragraphs_uk'] ?? [];
        $body = self::paragraphs($paragraphs);
        $common = ['id' => $point['id'], 'aliases' => [], 'body_html' => $body,
            'examples' => array_map(self::example(...), $point['examples'] ?? []), 'tail' => []];
        if ($kind === 'forms-grid' || $kind === 'forms-note') {
            return $common + ['kind' => 'form', 'label' => $point['title'], 'title' => $point['formula'] ?? ''];
        }
        if ($kind === 'summary-list' || $kind === 'summary') {
            return ['kind' => 'fragment', 'id' => $point['id'], 'aliases' => [], 'title' => $point['title'],
                'items' => [['kind' => 'summary', 'items' => array_map(static fn ($p) => ['html' => new HtmlString('<p lang="uk">'.e($p).'</p>'), 'block_html' => true], $paragraphs)]],
                'tail' => array_map(self::example(...), $point['examples'] ?? [])];
        }
        if ($kind === 'mistakes-grid' && isset($config['error_paragraphs'])) {
            // Accepted exact byte ranges preserve surrounding prose and the pair's order.
            $parts = [];
            foreach ($paragraphs as $paragraphIndex => $paragraph) {
                $plan = collect($config['error_paragraphs'])->firstWhere('paragraph_index', $paragraphIndex);
                if (!$plan) { $parts[] = ['kind' => 'fragment', 'body_html' => self::paragraphs([$paragraph])]; continue; }
                $html = '';
                foreach (M41ExistingDesignPackage::paragraphFragments($paragraph, $plan) as $part) {
                    $html .= $part['role'] === 'plain' ? e($part['text']) : self::render([
                        'kind' => 'correction', 'variant' => $part['role'], 'text' => $part['text'], 'inline' => true, 'lang' => 'en',
                    ]);
                }
                $parts[] = ['kind' => 'fragment', 'body_html' => new HtmlString($html)];
            }
            return ['kind' => 'usage', 'id' => $point['id'], 'aliases' => [], 'label' => $point['title'], 'number' => $index + 1,
                'accent' => 'rose', 'items' => $parts, 'tail' => array_map(self::example(...), $point['examples'] ?? [])];
        }
        if ($kind === 'mistakes-grid' && isset($point['wrong_en'])) {
            return $common + ['kind' => 'mistake', 'label' => $point['title'], 'number' => $index + 1,
                'accent' => 'rose', 'wrong_en' => $point['wrong_en'], 'wrong_uk' => $point['wrong_uk'] ?? null,
                'right_en' => $point['right_en'], 'right_uk' => $point['right_uk'] ?? null];
        }
        if ($kind === 'comparison-table' && $native !== null) {
            return ['kind' => 'usage', 'id' => $point['id'], 'aliases' => [], 'label' => $point['title'], 'number' => $index + 1,
                'accent' => 'sky', 'body_html' => $body, 'items' => [[
                    'kind' => 'table', 'caption' => $point['title'],
                    'headers' => [__('theory_blocks.comparison_table.english_sentence'), __('theory_blocks.comparison_table.translation')],
                    'rows' => array_map(static fn ($example) => ['cells' => [new HtmlString(e($example['en'])), new HtmlString(e($example['ua']))]], $native['examples']),
                ]]];
        }
        if (isset($point['formula'])) {
            $common['body_html'] = new HtmlString('<p class="font-semibold">'.e($point['formula']).'</p>'.$body);
        }
        return $common + ['kind' => 'usage', 'label' => $point['title'], 'number' => $index + 1,
            'accent' => self::accent($kind, $index)];
    }

    /** Semantic defaults: neither package number nor source identity affects a component. */
    private static function accent(string $kind, int $index): string
    {
        $palette = match ($kind) {
            'contrast', 'comparison-table' => ['blue', 'emerald', 'sky', 'slate'],
            'limitations', 'warning' => ['amber', 'rose', 'slate'],
            default => ['emerald', 'blue', 'amber', 'slate'],
        };
        return $palette[$index % count($palette)];
    }

    private static function compactGroup(object $block, array $section, array $group, int $index, array $design, array $details): array
    {
        $source = $group['sources'][0]; $sourceIndex = $group['source_index'];
        $config = $design['points'][$sourceIndex] ?? [];
        $disclosed = $group['disclosure'];
        $point = $source;
        if ($disclosed) {
            $point['title'] = $group['title']; $point['paragraphs_uk'] = $group['basic_uk'];
            $point['examples'] = $group['examples'];
            unset($point['formula'], $point['detail']);
            if ($group['formula'] !== null) { $point['formula'] = $group['formula']; }
        }
        $item = self::point($point, $section['native_kind'], $index, $config);
        $item['aliases'][] = 'block-'.$block->id.'-point-'.$group['id'];
        if (isset($group['form_rows'])) {
            $rows = array_map(static fn ($row) => [
                'label' => $row['label'], 'formula' => $row['formula'], 'en' => $row['example']['en'],
                'uk' => $row['example']['uk'], 'note_uk' => $row['example']['note_uk'] ?? null,
            ], $group['form_rows']);
            if (($group['layout'] ?? null) === 'form-comparison') {
                $cards = array_map(static fn ($row, $rowIndex) => ['kind' => 'form', 'label' => $row['label'], 'title' => $row['formula'],
                    'id' => 'block-'.$block->id.'-point-'.$group['id'].'-row-'.$rowIndex,
                    'examples' => [self::example(['en' => $row['en'], 'uk' => $row['uk'], 'note_uk' => $row['note_uk']], 'inline')]], $rows, array_keys($rows));
                $item = ['kind' => 'fragment', 'id' => $group['id'], 'aliases' => $item['aliases'],
                    'title' => $group['title'], 'title_hidden' => true,
                    'items' => [['kind' => 'group', 'layout' => 'grid3', 'items' => $cards]],
                    'tail' => [['kind' => 'fragment', 'body_html' => self::paragraphs($point['paragraphs_uk'])]]];
            } else {
                $item['label'] = ''; $item['title'] = $point['title']; $item['rows'] = $rows;
                $item['examples'] = [];
            }
        } elseif (($group['layout'] ?? null) === 'inline-note') {
            $item = ['kind' => 'fragment', 'id' => $group['id'], 'aliases' => $item['aliases'], 'title' => $point['title'],
                'body_html' => new HtmlString((isset($point['formula']) ? '<p class="font-semibold">'.e($point['formula']).'</p>' : '').self::paragraphs($point['paragraphs_uk'])),
                'examples' => array_map(static fn ($e) => self::example($e, 'inline'), $point['examples'])];
        }
        if ($disclosed) {
            $fragments = [];
            foreach ($group['sources'] as $original) {
                $fragment = ['kind' => 'fragment', 'title' => $original['title'], 'body_html' => self::paragraphs($original['paragraphs_uk']), 'items' => []];
                if ($original['id'] !== $group['id']) {
                    $fragment['id'] = $original['id']; $fragment['aliases'] = ['block-'.$block->id.'-point-'.$original['id']];
                }
                if (isset($original['formula']) && !in_array($original['id'], $group['visible_formulas'], true)) {
                    $fragment['body_html'] = new HtmlString('<p class="font-semibold">'.e($original['formula']).'</p>'.$fragment['body_html']);
                }
                if (isset($original['wrong_en'])) {
                    $fragment['items'][] = ['kind' => 'correction', 'variant' => 'wrong', 'text' => $original['wrong_en'], 'lang' => 'en'];
                    if (isset($original['wrong_uk'])) { $fragment['items'][] = ['kind' => 'fragment', 'body_html' => self::paragraphs([$original['wrong_uk']])]; }
                    $fragment['items'][] = ['kind' => 'correction', 'variant' => 'right', 'text' => $original['right_en'], 'lang' => 'en'];
                    $fragment['items'][] = ['kind' => 'fragment', 'body_html' => self::paragraphs([$original['right_uk']])];
                }
                foreach ($original['examples'] as $exampleIndex => $example) {
                    if (!isset($group['selected'][$original['id'].':'.$exampleIndex])) { $fragment['items'][] = self::example($example); }
                }
                if (isset($original['detail'])) {
                    $detailFragment = self::detailFragment($original['detail'], 'block-'.$original['detail']['id']);
                    $originalIndex = array_search($original['id'], array_column($section['points'], 'id'), true);
                    if (isset($details[$originalIndex])) { $detailFragment['aliases'] = [$details[$originalIndex]->detailsId()]; }
                    $fragment['items'][] = $detailFragment;
                }
                $fragments[] = $fragment;
            }
            $content = ['kind' => 'fragment', 'id' => $group['id'].'-expanded-content', 'aliases' => $group['legacy_toggle_ids'] ?? [], 'items' => $fragments];
            // The grouped detail preserves original point-control IDs as aliases.
            // Its validation identity must therefore differ from those points.
            $item['detail'] = self::disclosure($content, $content['id'], $group['title'], $index, $group['id'].'-more');
        } elseif (isset($details[$sourceIndex])) {
            $item['detail'] = ['kind' => 'disclosure', 'section' => $details[$sourceIndex], 'index' => $index];
        }
        return $item;
    }

    private static function ownDetail(array &$item, ?array $detail, string $title, int $index, bool $guarded): void
    {
        if ($detail === null) { return; }
        $fragment = self::detailFragment($detail, $detail['id']);
        if (!$guarded) { $item['tail'][] = $fragment; return; }
        $item['detail'] = self::disclosure($fragment, $detail['id'], $detail['title'], $index, $detail['id'].'-toggle');
        // Provenance supports fidelity checks; it never selects classes or markup.
        foreach (array_keys($item['attrs'] ?? []) as $attribute) {
            if (preg_match('/^data-(m\d+)-(?:form-card|basic-point)$/', $attribute, $matches)) {
                $item['detail']['attrs'] = ['data-'.$matches[1].'-detail' => $detail['id']];
            }
        }
    }

    private static function provenance(array $data): ?string
    {
        foreach (['m41_v1' => 'm41', 'm43_v1' => 'm43', 'm44_v1' => 'm44', 'm45_v1' => 'm45'] as $key => $value) {
            if (isset($data[$key])) { return $value; }
        }
        return null;
    }

    private static function disclosure(array $fragment, string $key, string $title, int $index, string $toggle): array
    {
        $detail = self::render($fragment);
        // The complete finite fragment is verified before it can be collapsed.
        $basic = '<p>'.e($title).'</p>'; $full = $basic.$detail;
        $section = TheorySection::resolve($key, $title, new HtmlString($full), null, [
            'source_key' => $key, 'source_revision' => hash('sha256', $full), 'main' => new HtmlString($basic),
            'detail' => new HtmlString($detail), 'detail_references' => [$fragment['id']],
        ], nativeHtml5: true);
        if ($section->detail === null) { return $fragment; }
        return ['kind' => 'disclosure', 'section' => $section, 'index' => $index, 'toggle_id' => $toggle];
    }

    public static function detailFragment(array $detail, string $id): array
    {
        return ['kind' => 'fragment', 'id' => $id, 'title' => $detail['title'],
            'body_html' => self::paragraphs($detail['paragraphs_uk'] ?? []),
            'examples' => array_map(self::example(...), $detail['examples'] ?? [])];
    }

    public static function paragraphs(array $paragraphs): HtmlString
    {
        return new HtmlString(implode('', array_map(static fn ($text) => '<p lang="uk">'.e($text).'</p>', $paragraphs)));
    }

    public static function example(array $example, string $variant = 'box'): array
    {
        return ['kind' => 'example', 'en' => $example['en'], 'uk' => $example['uk'] ?? $example['ua'] ?? null,
            'note_uk' => $example['note_uk'] ?? null, 'variant' => $variant];
    }

    private static function note(string $text): array
    {
        return ['kind' => 'note', 'html' => new HtmlString(e($text)), 'variant' => 'plain'];
    }

    public static function table(array $table, string $title, ?string $id = null): array
    {
        return ['kind' => 'table', 'id' => $id, 'caption' => $title, 'headers' => $table['columns'],
            'rows' => array_map(static fn ($row) => ['cells' => array_map(static function ($cell) {
                if (is_string($cell)) { return ['role' => 'text', 'html' => new HtmlString('<span>'.e($cell).'</span>')]; }
                if (isset($cell['formula'])) { return ['role' => 'text', 'html' => new HtmlString('<span>'.e($cell['formula']).'</span>')]; }
                if (isset($cell['text_uk'])) { return ['role' => 'text', 'html' => new HtmlString('<span lang="uk">'.e($cell['text_uk']).'</span>')]; }
                return ['role' => 'example', 'node' => self::example($cell, 'table-cell')];
            }, $row)], $table['rows'])];
    }

    public static function render(array $node): string
    {
        return view('theory.components.node', ['node' => $node])->render();
    }
}
