<?php

namespace App\Support;

/**
 * Finite, reviewed grouping of accepted prose into existing native usage panels.
 * Only wrappers/labels are added: original normalized children and detail owners
 * remain intact. Content never chooses a view, CSS class, or stylesheet.
 */
final class TheoryContentGroups
{
    private const MAPS = [
        'docs/content/theory-content-groups/emphasis-conditionals.v1.json' => 'ea9cb463a1daa1195e87185490a7f668fc77fade1cfcd7e02a269e1be8d2b77a',
        'docs/content/theory-content-groups/formal-academic-passive.v1.json' => '4fe5ac866351be7d91084d9d2fcbdee99114cc821fac7df78948b1044ed4b728',
        'docs/content/theory-content-groups/modals-revision.v1.json' => 'cf3ae48bd75ce28dac7b9cf710039ab84f4635a4f9e44a2e17fd6cca78092a36',
    ];

    private static function record(object $block, array $design): ?array
    {
        static $cache = [];
        foreach (self::MAPS as $file => $sha) {
            $path = base_path($file);
            if (!is_file($path)) { continue; }
            $bytes = file_get_contents($path);
            if (!is_string($bytes) || !hash_equals($sha, hash('sha256', $bytes))) { continue; }
            $map = $cache[$file] ??= json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
            if (($map['schema_version'] ?? null) !== 1) { continue; }
            foreach ($map['targets'] ?? [] as $target) {
                if (($target['identity'] ?? null) !== ($block->seeder ?? null)) { continue; }
                foreach ($target['blocks'] ?? [] as $record) {
                    if (($record['uuid'] ?? null) === ($design['uuid'] ?? null)
                        && ($record['source_index'] ?? null) === ($design['source_index'] ?? null)
                        && ($record['body_sha256'] ?? null) === ($design['body_sha256'] ?? null)
                        && ($record['component'] ?? null) === ($design['component'] ?? null)) { return $record; }
                }
            }
        }
        return null;
    }

    public static function apply(array $node, object $block, array $data, ?array $design): array
    {
        if (!in_array($block->type ?? null, ['usage-panels', 'comparison-table'], true) || ($block->locale ?? null) !== 'uk'
            || $design === null || !TheoryHtmlAdapter::verifiedNativeDesign($design)) { return $node; }
        try {
            $record = self::record($block, $design);
            if ($record === null) { return $node; }
            // Rebind both the stored owner and the complete approved basic projection.
            // A valid design record alone must not style unrelated or modified prose.
            $resolved = TheoryContentSource::resolve($block, ['m42StyleContext' => true]);
            if ($resolved['design'] !== $design || $resolved['data'] !== $data) { return $node; }
            $sections = $data['sections'] ?? [];
            $items = $node['items'] ?? [];
            $after = [];
            if (($block->type ?? null) === 'comparison-table' && is_array($sections) && is_array($items)) {
                $after = array_slice($items, count($sections));
                $items = array_slice($items, 0, count($sections));
                if (count($after) !== 1 || ($after[0]['kind'] ?? null) !== 'table') { return $node; }
            }
            if (!is_array($sections) || !array_is_list($sections) || !array_is_list($items)
                || count($sections) !== count($items)) { return $node; }
            if (isset($record['intro_group'])) {
                $intro = $record['intro_group'];
                if (!is_array($intro) || !self::validLabel($intro) || $items !== []
                    || !TheoryComponents::present($node['intro_html'] ?? null)) { return $node; }
                $node['items'] = [[
                    'kind' => 'usage', 'label' => $intro['label'], 'number' => 1,
                    'accent' => $intro['accent'], 'attrs' => self::attrs($intro['accent']),
                    'body_html' => $node['intro_html'], 'body_inline' => false,
                ]];
                unset($node['intro_html']);
                $node['attrs']['data-theory-reference'] = 'native';
                return $node;
            }
            $groups = $record['groups'] ?? null;
            if (!is_array($groups) || $groups === []) { return $node; }
            $seen = []; $panels = [];
            foreach ($groups as $position => $group) {
                if (!self::validLabel($group) || !is_array($group['indices'] ?? null) || $group['indices'] === []) { return $node; }
                $children = [];
                foreach ($group['indices'] as $index) {
                    if (!is_int($index) || $index !== count($seen) || !isset($items[$index])
                        || !in_array($items[$index]['kind'] ?? null, ['fragment', 'usage'], true)) { return $node; }
                    $seen[] = $index;
                    $children[] = $items[$index];
                }
                $named = array_filter($children, static fn ($item) => $item['kind'] === 'usage');
                if ($named !== []) {
                    // An already labelled point keeps its original component, not a nested panel.
                    if (count($children) !== 1 || ($children[0]['label'] ?? null) !== $group['label']) { return $node; }
                    $panel = $children[0];
                    $panel['accent'] = $group['accent'];
                    $panel['attrs'] = ($panel['attrs'] ?? []) + self::attrs($group['accent']);
                    $panels[] = $panel;
                    continue;
                }
                $panels[] = [
                    'kind' => 'usage', 'label' => $group['label'], 'number' => $position + 1,
                    'accent' => $group['accent'], 'attrs' => self::attrs($group['accent']),
                    'items' => [['kind' => 'group', 'layout' => 'stack', 'items' => $children]],
                ];
            }
            if (count($seen) !== count($items)) { return $node; }
            $node['items'] = array_merge($panels, $after);
            $node['attrs']['data-theory-reference'] = 'native';
            return $node;
        } catch (\Throwable) {
            // Fail closed to the complete existing presentation; never hide content.
            return $node;
        }
    }

    private static function validLabel(array $group): bool
    {
        return is_string($group['label'] ?? null) && trim($group['label']) !== ''
            && in_array($group['accent'] ?? null, ['emerald', 'blue', 'amber', 'slate', 'sky', 'rose'], true);
    }

    private static function attrs(string $accent): array
    {
        return ['data-theory-content-group' => '', 'data-theory-point-panel' => '', 'data-theory-accent' => $accent];
    }
}
