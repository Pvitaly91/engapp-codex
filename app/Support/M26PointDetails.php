<?php

namespace App\Support;

use RuntimeException;

/** Finite presentation-only routing of immutable M26 text to individual points. */
final class M26PointDetails
{
    public const SOURCE = 'database/content-patches/m26-ppc-point-details.v1.json';
    public const SOURCE_SHA = '210dec0e198857c4f67b085ebb38b505b0d5b9d729f3978fbfeb278ede5ba28e';

    public static function load(?string $root = null): array
    {
        $bytes = file_get_contents(($root ?? base_path()).'/'.self::SOURCE);
        if (!hash_equals(self::SOURCE_SHA, hash('sha256', $bytes))) {
            throw new RuntimeException('M26 point presentation source differs.');
        }
        return json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
    }

    /** Validate complete one-time author coverage, not a guessed positional zip. */
    public static function validate(array $map, array $master, array $manifest): array
    {
        if (array_keys($map) !== ['version', 'blocks'] || $map['version'] !== 1 || !is_array($map['blocks'])) {
            throw new RuntimeException('Invalid M26 point map.');
        }
        $sources = []; $expected = [];
        foreach ($master['targets'] as $target) {
            foreach ($target['blocks'] as $item) {
                if (!isset($item['progressive_v1'])) { continue; }
                $key = $item['progressive_v1']['key'];
                $field = match ($item['type']) {
                    'forms-grid', 'summary-list' => 'items',
                    'usage-panels' => 'sections', 'comparison-table' => 'rows',
                    default => throw new RuntimeException('Unknown M26 point type.'),
                };
                $basic = json_decode($manifest['definitions'][$target['identity']][$target['source_content_root']]['blocks'][$item['source_index']]['body'], true, flags: JSON_THROW_ON_ERROR);
                $sources[$key] = ['owner' => $target['identity'], 'type' => $item['type'],
                    'field' => $field, 'basic' => $basic, 'detail' => $item['native_data']];
                foreach ($item['native_data'][$field] as $index => $_) { $expected[$key.':'.$field.':'.$index] = true; }
                foreach (['intro', 'warning'] as $name) {
                    if (isset($item['native_data'][$name])) { $expected[$key.':'.$name] = true; }
                }
            }
        }
        if (array_keys($map['blocks']) !== array_keys($sources)) { throw new RuntimeException('M26 point targets differ.'); }
        $seen = []; $seenSources = []; $plans = [];
        foreach ($sources as $key => $source) {
            $points = $map['blocks'][$key]; $basicPoints = $source['basic'][$source['field']];
            if (!is_array($points) || !array_is_list($points) || count($points) !== count($basicPoints)) {
                throw new RuntimeException('M26 basic point coverage differs.');
            }
            foreach ($points as $index => $point) {
                if (!is_array($point) || !isset($point['fragments']) || !is_array($point['fragments'])
                    || !array_is_list($point['fragments']) || array_diff(array_keys($point), ['fragments', 'supplement'])) {
                    throw new RuntimeException('Invalid M26 point shape.');
                }
                $pointKey = $key.'-point-'.($index + 1);
                $basic = $basicPoints[$index];
                $title = is_array($basic) ? ($basic['label'] ?? $basic['title'] ?? $basic['en'] ?? '') : strip_tags($basic);
                $fragments = []; $educational = false;
                foreach ($point['fragments'] as $ref) {
                    if (!is_array($ref) || !is_string($ref['source'] ?? null) || !is_string($ref['field'] ?? null)) {
                        throw new RuntimeException('Invalid M26 author reference.');
                    }
                    $s = $sources[$ref['source'] ?? ''] ?? null; $field = $ref['field'] ?? '';
                    if (!$s || $s['owner'] !== $source['owner']) { throw new RuntimeException('Foreign M26 detail source.'); }
                    if ($field === $s['field']) {
                        if (array_keys($ref) !== ['source', 'field', 'index'] || !is_int($ref['index'])
                            || !array_key_exists($ref['index'], $s['detail'][$field])) { throw new RuntimeException('Invalid M26 author item.'); }
                        $reference = $ref['source'].':'.$field.':'.$ref['index'];
                        $type = $s['type']; $value = $s['detail'][$field][$ref['index']]; $educational = true;
                        $suffix = $field.'-'.($ref['index'] + 1);
                    } elseif (in_array($field, ['intro', 'warning'], true)) {
                        if (array_keys($ref) !== ['source', 'field'] || !isset($s['detail'][$field])) { throw new RuntimeException('Invalid M26 author context.'); }
                        $reference = $ref['source'].':'.$field; $type = $field; $value = $s['detail'][$field]; $suffix = $field;
                    } else { throw new RuntimeException('Unknown M26 author field.'); }
                    if (isset($seen[$reference])) { throw new RuntimeException('Duplicate M26 author fragment.'); }
                    $seen[$reference] = true;
                    // Keep each former block-level deep link on its first fragment.
                    $id = 'block-'.$ref['source'].(isset($seenSources[$ref['source']]) ? '-'.$suffix : '');
                    $seenSources[$ref['source']] = true;
                    $fragments[] = ['id' => $id, 'type' => $type, 'value' => $value];
                }
                if (isset($point['supplement'])) {
                    $extra = $point['supplement'];
                    if (!is_array($extra) || array_keys($extra) !== ['description', 'examples'] || !self::plain($extra['description'])
                        || !is_array($extra['examples']) || !array_is_list($extra['examples']) || $extra['examples'] === []) {
                        throw new RuntimeException('Invalid M26 point supplement.');
                    }
                    foreach ($extra['examples'] as $example) {
                        if (!is_array($example) || array_keys($example) !== ['en', 'ua'] || !self::plain($example['en']) || !self::plain($example['ua'])) {
                            throw new RuntimeException('Invalid M26 supplement example.');
                        }
                    }
                    $educational = true;
                    $fragments[] = ['id' => 'block-'.$pointKey.'-supplement', 'type' => 'supplement', 'value' => $extra];
                }
                if (!$educational || trim(strip_tags($title)) === '') { throw new RuntimeException('Missing M26 point explanation.'); }
                $plans[$key][$index] = ['key' => $pointKey, 'title' => $title, 'fragments' => $fragments];
            }
        }
        if (array_diff_key($expected, $seen) || array_diff_key($seen, $expected)) { throw new RuntimeException('Incomplete M26 author coverage.'); }
        return $plans;
    }

    private static function plain(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '' && !preg_match('/[<>]|\{a\d+\}|&(?:#\d+|\w+);/u', $value);
    }

    /** Invalid or foreign metadata preserves the full basic view without controls. */
    public static function fragmentsFor(object $block, array $data): ?array
    {
        $extension = M26DetailPackage::detailFor($block, $data);
        if ($extension === null) { return null; }
        try {
            [$master, $manifest] = M26DetailPackage::load();
            return self::validate(self::load(), $master, $manifest)[$extension['key']] ?? null;
        } catch (\Throwable) { return null; }
    }
}
