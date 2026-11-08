<?php

namespace App\Support;

use RuntimeException;

/** Three frozen M19 owners only. Unknown data retains the complete native basic. */
final class M35PassiveReportingPackage
{
    public const BEFORE = 'database/content-patches/m35-m19-passive-reporting-before.json';
    public const BEFORE_SHA = '71610d3bbbd41b9d5fee14e633c4adc5dee0480910609dd9c4086bed982b68f6';
    public const SOURCE = 'database/content-patches/m35-m19-passive-reporting.v1.json';
    public const SOURCE_SHA = '000b06498bea93d9240b346db100b5578a9522b2ef082fc0b7f482d2367595dc';

    public static function load(?string $root = null): array
    {
        $root ??= base_path(); $out = [];
        foreach ([self::BEFORE => self::BEFORE_SHA, self::SOURCE => self::SOURCE_SHA] as $path => $sha) {
            $bytes = file_get_contents($root.'/'.$path);
            if (!hash_equals($sha, hash('sha256', $bytes))) { throw new RuntimeException('M35 immutable source differs: '.$path); }
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
            throw new RuntimeException('M35 fidelity/point ownership differs from the accepted finite projection.');
        }
        $detailOwners = [];
        foreach ($package['targets'] as $i => $target) {
            $original = $before['targets'][$i]['before']; $after = $target['after'];
            $restored = $after; $restored['page']['blocks'] = $original['page']['blocks'];
            if ($restored !== $original || $after['page']['blocks'][0] !== $original['page']['blocks'][0]) {
                throw new RuntimeException('M35 protected metadata/hero differs.');
            }
            $keys = [];
            foreach ($target['plans'] as $j => $plan) {
                $data = json_decode($after['page']['blocks'][$j + 1]['body'], true, flags: JSON_THROW_ON_ERROR);
                if (isset($keys[$plan['key']]) || $data['m35_v1']['key'] !== $plan['key']
                    || $plan['source_section'] !== $j + 1) { throw new RuntimeException('M35 duplicate/moved section anchor.'); }
                $keys[$plan['key']] = true;
                foreach ($plan['points'] as $k => $p) {
                    if ($p['basic'] === '' || $data['sections'][$k]['description'] !== $p['basic'].($p['detail'] !== '' ? '<br><br>'.$p['detail'] : '')) {
                        throw new RuntimeException('M35 missing basic or author fragment.');
                    }
                    if ($p['detail'] !== '') { $detailOwners[] = [$i, $plan['key'], $plan['source_section'], $k]; }
                }
            }
        }
        // Explicit editorial decisions, never a runtime word-count heuristic.
        if ($detailOwners !== []) {
            throw new RuntimeException('M35 finite meaningful detail ownership differs.');
        }
    }

    /** Returns only server-side, hash-bound fragments. DB metadata cannot select a view. */
    public static function presentation(object $block, array $data): ?array
    {
        if (!isset($data['m35_v1']) || ($block->locale ?? null) !== 'uk') { return null; }
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
                            ['id' => 'block-'.$key.'-detail', 'type' => 'm35-author-html', 'value' => $p['detail']],
                        ]];
                    }
                    return ['data' => $basic, 'points' => $points, 'legacy_section' => $plan['source_section'],
                        'legacy_practice_id' => $data['m35_v1']['legacy_practice_id'] ?? null];
                }
            }
        } catch (\Throwable) { /* Full author text remains in the stored native data. */ }
        return null;
    }
}
