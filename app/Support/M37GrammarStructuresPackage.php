<?php

namespace App\Support;

use RuntimeException;

/** Three frozen M21 owners only. Unknown data retains the complete native basic. */
final class M37GrammarStructuresPackage
{
    public const BEFORE = 'database/content-patches/m37-m21-grammar-structures-before.json';
    public const BEFORE_SHA = '3c978f6b91dfea0ce988d9ed3f7fb34ef1189ba4e485ca1d86b2f7449ba7fc52';
    public const SOURCE = 'database/content-patches/m37-m21-grammar-structures.v1.json';
    public const SOURCE_SHA = '0006e0ecbb6d29aee047527bb1739884ad29930ec74eb4e499e5010e3764dbc5';

    public static function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA] as $path => $sha) {
            $bytes = file_get_contents($root.'/'.$path);
            if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M37 immutable source differs: '.$path); }
            $out[] = json_decode($bytes, true, flags: JSON_THROW_ON_ERROR);
        }
        return $out;
    }

    public static function json(array $value): string { return M26DetailPackage::json($value); }

    /** Byte-level package identity also protects punctuation, translations and point ownership. */
    public static function validate(array $before, array $package): void
    {
        [$expectedBefore, $expectedPackage] = self::load();
        if ($before !== $expectedBefore || $package !== $expectedPackage) {
            throw new RuntimeException('M37 fidelity/point ownership differs from the accepted finite projection.');
        }
        $detailOwners = [];
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after'];
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks'];
            if ($restored !== $original || $after['page']['blocks'][0] !== $original['page']['blocks'][0]) {
                throw new RuntimeException('M37 protected metadata/hero differs.');
            }
            $keys = [];
            foreach ($target['plans'] as $j => $plan) {
                $data = json_decode($after['page']['blocks'][$j + 1]['body'], true, flags: JSON_THROW_ON_ERROR);
                if (isset($keys[$plan['key']]) || $data['m37_v1']['key'] !== $plan['key']
                    || $plan['source_section'] !== $j + 1) { throw new RuntimeException('M37 duplicate/moved section anchor.'); }
                $keys[$plan['key']] = true;
                foreach ($plan['points'] as $k => $p) {
                    if ($p['basic'] === '' || $data['sections'][$k]['description'] !== $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')) {
                        throw new RuntimeException('M37 missing basic or author fragment.');
                    }
                    if ($p['detail'] !== '') { $detailOwners[] = [$i, $plan['key'], $plan['source_section'], $k]; }
                }
            }
        }
        // Explicit editorial decisions, never a runtime word-count heuristic.
        if ($detailOwners !== []) {
            throw new RuntimeException('M37 finite meaningful detail ownership differs.');
        }
    }

    /** Returns only server-side, hash-bound fragments. DB metadata cannot select a view. */
    public static function presentation(object $block, array $data): ?array
    {
        if (!isset($data['m37_v1']) || ($block->locale ?? null) !== 'uk') { return null; }
        try {
            [, $package] = self::load();
            foreach ($package['targets'] as $target) {
                if (($block->seeder ?? null) !== $target['identity']) { continue; }
                foreach ($target['plans'] as $j => $plan) {
                    $source = $target['after']['page']['blocks'][$j + 1]; $position = $j + 2;
                    if (($block->uuid ?? null) !== M26DetailPackage::uuid($target['identity'], $source, $position)) { continue; }
                    if (($block->type ?? null) !== $source['type'] || (int) ($block->sort_order ?? -1) !== $position
                        || $data !== json_decode($source['body'], true, flags: JSON_THROW_ON_ERROR)) { return null; }
                    $basic = $data; $points = [];
                    foreach ($plan['points'] as $k => $p) {
                        if ($p['detail'] === '') { continue; }
                        $basic['sections'][$k]['description'] = $p['basic'];
                        $key = $plan['key'].'-point-'.($k + 1);
                        $points[$k] = ['key' => $key, 'title' => $data['title'], 'fragments' => [
                            ['id' => 'block-'.$key.'-detail', 'type' => 'm37-author-html', 'value' => $p['detail']],
                        ]];
                    }
                    return ['data' => $basic, 'points' => $points, 'legacy_section' => $plan['source_section'],
                        'legacy_practice_id' => $data['m37_v1']['legacy_practice_id'] ?? null];
                }
            }
        } catch (\Throwable) { /* Full author text remains in the stored native data. */ }
        return null;
    }
}
